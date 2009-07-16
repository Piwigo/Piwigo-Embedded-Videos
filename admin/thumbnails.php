<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function get_image_with_band($srcImage, $gd_ver)
{
    $srcWidth = imagesx($srcImage);
    $srcHeight = imagesy($srcImage);
    $const = intval($srcWidth * 0.04);

    if ($gd_ver >= 2)
    {
      $imgBand = imagecreatetruecolor($srcWidth + 6 * $const, $srcHeight);
    }
    else
    {
      $imgBand = imagecreate($srcWidth + 6 * $const, $srcHeight);
    }

    $white = imagecolorallocate($imgBand, 255, 255, 255);
    $y_start = intval($srcHeight / 2);
    $aug = intval($y_start / 4) + 1;
    $i = 0;

    while ($y_start + $i * $aug < $srcHeight - $const)
    {
      imagefilledrectangle($imgBand, $const, $y_start + $i * $aug - ($const / 2), 2 * $const - 1, $y_start + $i * $aug + ($const / 2) - 1, $white);
      imagefilledrectangle($imgBand, $const, $y_start - $i * $aug - ($const / 2), 2 * $const - 1, $y_start - $i * $aug + ($const / 2) - 1, $white);

      imagefilledrectangle($imgBand, $srcWidth + (4 * $const), $y_start + $i * $aug - ($const / 2), $srcWidth + (5 * $const) - 1, $y_start + $i * $aug + ($const / 2) - 1, $white);
      imagefilledrectangle($imgBand, $srcWidth + (4 * $const), $y_start - $i * $aug - ($const / 2), $srcWidth + (5 * $const) - 1, $y_start - $i * $aug + ($const / 2) - 1, $white);

      ++$i;
    }

    imagecopy($imgBand, $srcImage, 3 * $const, 0, 0, 0, $srcWidth, $srcHeight);
    return $imgBand;
}

function Py_RatioResizeImg($url, $path, $newWidth, $newHeight)
{
    global $conf, $lang, $page;

    if (!function_exists('gd_info'))
    {
      return false;
    }

    $ver_info = gd_info();
    preg_match('/\d/', $ver_info['GD Version'], $gd_ver);

    if (!($tempfile = @tempnam( $path, 'jpg')
      and $handle = @fopen($tempfile, 'wb')
      and fetchRemote($url, $handle)
      and @fclose($handle)
      and $srcImage = @imagecreatefromjpeg($tempfile)))
    {
      return false;
    }
    @unlink($tempfile);

    if (isset($_POST['add_band']))
    {
      $srcImage = get_image_with_band($srcImage, $gd_ver[0]);
    }

    $srcWidth = imagesx($srcImage);
    $srcHeight = imagesy($srcImage);

    if (isset($_POST['thumb_resize']))
    {
      $ratioWidth = $srcWidth / $newWidth;
      $ratioHeight = $srcHeight / $newHeight;
      if ($ratioWidth < $ratioHeight)
      {
        $destWidth = $srcWidth / $ratioHeight;
        $destHigh = $newHeight;
      }
      else
      {
        $destWidth = $newWidth;
        $destHigh = $srcHeight / $ratioWidth;
      }
    }
    else
    {
      $destWidth = $srcWidth;
      $destHigh = $srcHeight;
    }

    if ($gd_ver[0] >= 2)
    {
        $destImage = imagecreatetruecolor($destWidth, $destHigh);
        imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0,
            $destWidth, $destHigh, $srcWidth, $srcHeight);
    }
    else
    {
        $destImage = imagecreate($destWidth, $destHigh);
        imagecopyresized($destImage, $srcImage, 0, 0, 0, 0,
            $destWidth, $destHigh, $srcWidth, $srcHeight);
    }

    imagejpeg($destImage, $path);
    imagedestroy($srcImage);
    imagedestroy($destImage);
    return true;
}

// Récupération de la miniature depuis le serveur
if ($_POST['thumbnail'] == 'thumb_from_server')
{
  $video['thumb_url'] = str_replace('&amp;', '&', $video['thumb_url']);
  if (($path = mkget_thumbnail_dir($catpath[$cat], $error)) == false)
  {
    array_push($page['errors'], l10n('py_error9'));
  }
  else
  {
    $path .= '/' . $conf['prefix_thumbnail'] . $video['name'] . '.jpg';
    if (file_exists($path))
    {
      array_push($page['errors'], sprintf(l10n('py_error11'), $path), l10n('py_error9'));
    }
    else
    {
      if (Py_RatioResizeImg($video['thumb_url'], $path, $_POST['thumb_width'], $_POST['thumb_hight']))
      {
        array_push($page['infos'], l10n('py_info2'));
        $thumb_extension = '"jpg"';
      }
      else
      {
        array_push($page['errors'], l10n('py_error9'));
      }
    }
  }
}

// Upload de la miniature par l'utilisateur
if ($_POST['thumbnail'] == 'thumb_from_user')
{
  if (empty($_FILES['picture']['size']))
  {
    array_push($page['infos'], l10n('py_error12'));
  }
  else
  {
    $ext = get_extension($_FILES['picture']['name']);
    if (($path = mkget_thumbnail_dir($catpath[$cat], $error)) == false)
    {
      array_push($page['errors'], l10n('py_error9'));
    }
    else
    {
      $path .= '/' . $conf['prefix_thumbnail'] . $video['name'] . '.' . $ext;
      if (file_exists($path))
      {
        array_push($page['errors'], sprintf(l10n('py_error11'), $path), l10n('py_error9'));
      }
      else
      {
        if (Py_RatioResizeImg($_FILES['picture']['tmp_name'], $path, $_POST['thumb_width'], $_POST['thumb_hight']))
        {
          $thumb_extension = "\"$ext\"";
          array_push($page['infos'], l10n('py_info2'));
        }
        else
        {
          array_push($page['errors'], l10n('py_error9'));
        }
      }
    }
  }
}

?>