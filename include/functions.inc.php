<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function parse_video_url($source_url)
{
  $source_url = 'http://'.preg_replace('#^http(s?)://#', null, $source_url);
  
  $url = parse_url($source_url);
  $url['host'] = str_replace('www.', null, $url['host']);
  $url['host'] = explode('.', $url['host']);
  
  $video = array();
  switch ($url['host'][0])
  {
    /* youtube */
    case 'youtube':
    {
      $video['type'] = 'youtube';

      parse_str($url['query'], $url['query']);
      if (empty($url['query']['v'])) return false;
      
      $video['video_id'] = $url['query']['v'];
    }
    
    case 'youtu': // youtu.be (short-url service)
    {
      if (empty($video))
      {
        $video['type'] = 'youtube';
        
        $url['path'] = explode('/', $url['path']);
        $video['video_id'] = $url['path'][1];
      }
      
      $api_url = 'http://gdata.youtube.com/feeds/api/videos/'.$video['video_id'].'?v=2&alt=json';
      $json = download_remote_file($api_url, true);
      if ($json === false or $json == 'file_error') return false;
      
      $json = json_decode($json, true);
      $video = array_merge($video, array(
        'url' => 'http://youtube.com/watch?v='.$video['video_id'],
        'title' => $json['entry']['title']['$t'],
        'description' => $json['entry']['media$group']['media$description']['$t'],
        'thumbnail' => $json['entry']['media$group']['media$thumbnail'][2]['url'],
        'author' => $json['entry']['author'][0]['name']['$t'],
        'tags' => null,
        ));
      break;
    }
      
    /* vimeo */
    case 'vimeo':
    {
      $video['type'] = 'vimeo';
      
      $url['path'] = explode('/', $url['path']);
      $video['video_id'] = $url['path'][1];
      
      $api_url = 'http://vimeo.com/api/v2/video/'.$video['video_id'].'.json';
      $json = download_remote_file($api_url, true);
      if ($json === false or $json == 'file_error') return false;
      
      $json = json_decode($json, true);
      $video = array_merge($video, array(
        'url' => 'http://vimeo.com/'.$video['video_id'],
        'title' => $json[0]['title'],
        'description' => $json[0]['description'],
        'thumbnail' => $json[0]['thumbnail_large'],
        'author' => $json[0]['user_name'],
        'tags' => array_map('trim', explode(',', $json[0]['tags'])),
        ));
      break;
    }
      
    /* dailymotion */
    case 'dailymotion':
    {
      $video['type'] = 'dailymotion';
      
      $url['path'] = explode('/', $url['path']);
      if ($url['path'][1] != 'video') return false;
      $video['video_id'] = $url['path'][2];
      
      $api_url = 'https://api.dailymotion.com/video/'.$video['video_id'].'?fields=description,thumbnail_large_url,title,owner.username,tags'; // DM doesn't accept non secure connection
      $json = download_remote_file($api_url, true);
      if ($json === false or $json == 'file_error') return false;
      
      $json = json_decode($json, true);
      $json['thumbnail_large_url'] = preg_replace('#\?([0-9]+)$#', null, $json['thumbnail_large_url']);
      
      $video = array_merge($video, array(
        'url' => 'http://dailymotion.com/video/'.$video['video_id'],
        'title' => $json['title'],
        'description' => $json['description'],
        'thumbnail' => $json['thumbnail_large_url'],
        'author' => $json['owner.username'],
        'tags' => $json['tags'],
        ));
      break;
    }
      
    /* wat */
    case 'wat':
    {
      $video['type'] = 'wat';
      
      $html = download_remote_file($source_url, true);
      if ($html === false or $html == 'file_error') return false;
      
      preg_match('#<meta property="og:video" content="http://www.wat.tv/swf2/([^"/>]+)" />#', $html, $matches);
      if (empty($matches[1])) return false;
      $video['video_id'] = $matches[1];
      
      $video['url'] = $source_url;
      
      preg_match('#<meta name="name" content="([^">]*)" />#', $html, $matches);
      $video['title'] = $matches[1];
      
      preg_match('#<p class="description"(?:[^>]*)>(.*?)</p>#s', $html, $matches);
      $video['description'] = $matches[1];
      
      preg_match('#<meta property="og:image" content="([^">]+)" />#', $html, $matches);
      $video['thumbnail'] = $matches[1];
      
      $video['author'] = null;
      
      preg_match_all('#<meta property="video:tag" content="([^">]+)" />#', $html, $matches);
      $video['tags'] = $matches[1];
      break;
    }
      
    /* wideo */
    case 'wideo':
    {
      $video['type'] = 'wideo';
      
      $url['path'] = explode('/', $url['path']);
      $video['video_id'] = rtrim($url['path'][2], '.html');
      
      $html = download_remote_file($source_url, true);
      if ($html === false or $html == 'file_error') return false;
      
      $video['url'] = 'http://wideo.fr/video/'.$video['video_id'].'.html';
      
      preg_match('#<meta property="og:title" content="([^">]*)" />#', $html, $matches);
      $video['title'] = $matches[1];
      
      preg_match('#<meta property="og:description" content="([^">]*)" />#', $html, $matches);
      $video['description'] = $matches[1];
      
      preg_match('#<meta property="og:image" content="([^">]+)" />#', $html, $matches);
      $video['thumbnail'] = $matches[1];
      
      preg_match('#<li id="li_author">Auteur :  <a href=(?:[^>]*)><span>(.*?)</span></a>#', $html, $matches);
      $video['author'] = $matches[1];
      
      preg_match('#<meta name="keywords" content="([^">]+)" />#', $html, $matches);
      $video['tags'] = array_map('trim', explode(',', $matches[1]));
      break;
    }
      
    default:
      return false;   
  }
  
  return $video;
}

/**
 * test if a download method is available
 * @return: bool
 */
if (!function_exists('test_remote_download'))
{
  function test_remote_download()
  {
    return function_exists('curl_init') || ini_get('allow_url_fopen');
  }
}

/**
 * download a remote file
 *  - needs cURL or allow_url_fopen
 *  - take care of SSL urls
 *
 * @param: string source url
 * @param: mixed destination file (if true, file content is returned)
 */
if (!function_exists('download_remote_file'))
{
  function download_remote_file($src, $dest)
  {
    if (empty($src))
    {
      return false;
    }
    
    $return = ($dest === true) ? true : false;
    
    /* curl */
    if (function_exists('curl_init'))
    {
      if (!$return)
      {
        $newf = fopen($dest, "wb");
      }
      $ch = curl_init();
      
      curl_setopt($ch, CURLOPT_URL, $src);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-language: en"));
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
      if (!ini_get('safe_mode'))
      {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
      }
      if (strpos($src, 'https://') !== false)
      {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      }
      if (!$return)
      {
        curl_setopt($ch, CURLOPT_FILE, $newf);
      }
      else
      {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      }
      
      $out = curl_exec($ch);
      curl_close($ch);
      
      if ($out === false)
      {
        return 'file_error';
      }
      else if (!$return)
      {
        fclose($newf);
        return true;
      }
      else
      {
        return $out;
      }
    }
    /* file get content */
    else if (ini_get('allow_url_fopen'))
    {
      if (strpos($src, 'https://') !== false and !extension_loaded('openssl'))
      {
        return false;
      }
      
      $opts = array(
        'http' => array(
          'method' => "GET",
          'user_agent' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
          'header' => "Accept-language: en",
        )
      );

      $context = stream_context_create($opts);
      
      if (($file = file_get_contents($src, false, $context)) === false)
      {
        return 'file_error';
      }
      
      if (!$return)
      {
        file_put_contents($dest, $file);
        return true;
      }
      else
      {
        return $file;
      }
    }
    
    return false;
  }
}

/**
 * and film frame to an image (need GD library)
 * @param: string source
 * @param: string destination (if null, the source si modified)
 * @return: void
 */
function add_film_frame($src, $dest=null)
{
  if (empty($dest))
  {
    $dest = $src;
  }
  
  // we need gd library
  if (!function_exists('imagecreatetruecolor'))
  {
    if ($dest != $src) copy($src, $dest);
    return;
  }
  
  // open source image
  switch (strtolower(get_extension($src)))
  {
    case 'jpg':
    case 'jpeg':
      $srcImage = imagecreatefromjpeg($src);
      break;
    case 'png':
      $srcImage = imagecreatefrompng($src);
      break;
    case 'gif':
      $srcImage = imagecreatefromgif($src);
      break;
    default:
      if ($dest != $src) copy($src, $dest);
      return;
  }
  
  // source properties
  $srcWidth = imagesx($srcImage);
  $srcHeight = imagesy($srcImage);
  $const = intval($srcWidth * 0.04);
  $bandRadius = floor($const/8);

  // band properties
  $imgBand = imagecreatetruecolor($srcWidth + 6*$const, $srcHeight + 3*$const);
  
  $black = imagecolorallocate($imgBand, 0, 0, 0);
  $white = imagecolorallocate($imgBand, 245, 245, 245);
  
  // and dots
  $y_start = intval(($srcHeight + 3*$const) / 2);
  $aug = intval($y_start / 5) + 1;
  $i = 0;

  while ($y_start + $i*$aug < $srcHeight + 3*$const)
  {
    imagefilledroundrectangle($imgBand, (3/4)*$const, $y_start + $i*$aug - $const/2, (9/4)*$const - 1, $y_start + $i*$aug + $const/2 - 1, $white, $bandRadius);
    imagefilledroundrectangle($imgBand, (3/4)*$const, $y_start - $i*$aug - $const/2, (9/4)*$const - 1, $y_start - $i*$aug + $const/2 - 1, $white, $bandRadius);

    imagefilledroundrectangle($imgBand, $srcWidth + (15/4)*$const, $y_start + $i*$aug - $const/2, $srcWidth + (21/4)*$const - 1, $y_start + $i*$aug + $const/2 - 1, $white, $bandRadius);
    imagefilledroundrectangle($imgBand, $srcWidth + (15/4)*$const, $y_start - $i*$aug - $const/2, $srcWidth + (21/4)*$const - 1, $y_start - $i*$aug + $const/2 - 1, $white, $bandRadius);

    ++$i;
  }

  // add source to band
  imagecopy($imgBand, $srcImage, 3*$const, (3/2)*$const, 0, 0, $srcWidth, $srcHeight);
  
  // save image
  switch (strtolower(get_extension($dest)))
  {
    case 'jpg':
    case 'jpeg':
      imagejpeg($imgBand, $dest, 85);
      break;
    case 'png':
      imagepng($imgBand, $dest);
      break;
    case 'gif':
      imagegif($imgBand, $dest);
      break;
  }
}

/**
 * create a rectangle with round corners
 * http://www.php.net/manual/fr/function.imagefilledrectangle.php#42815
 */
function imagefilledroundrectangle(&$img, $x1, $y1, $x2, $y2, $color, $radius)
{
  imagefilledrectangle($img, $x1+$radius, $y1, $x2-$radius, $y2, $color);
  
  if ($radius > 0)
  {
    imagefilledrectangle($img, $x1, $y1+$radius, $x2, $y2-$radius, $color);
    imagefilledellipse($img, $x1+$radius, $y1+$radius, $radius*2, $radius*2, $color);
    imagefilledellipse($img, $x2-$radius, $y1+$radius, $radius*2, $radius*2, $color);
    imagefilledellipse($img, $x1+$radius, $y2-$radius, $radius*2, $radius*2, $color);
    imagefilledellipse($img, $x2-$radius, $y2-$radius, $radius*2, $radius*2, $color);
  }
}

?>