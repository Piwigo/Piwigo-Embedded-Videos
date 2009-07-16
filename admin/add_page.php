<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

$video_types = array('google', 'youtube', 'dailymotion', 'wideo', 'vimeo');

function get_video_infos($url, $type)
{
  switch ($type)
  {
  case "google":
    @preg_match('#\=([\-\+0-9]*)#', $url, $id);
    if (empty($id[1])) return false;
    $video['id'] = $id[1];
    $video['ext'] = 'gvideo';
    if ($_POST['thumbnail'] == 'thumb_from_server' and fetchRemote($url, $source))
    {
      @preg_match("#thumbnailUrl\\\\x3d(http://.*/ThumbnailServer.*)\\\\x26#", $source, $thumb_url);
      $video['thumb_url'] = @urldecode($thumb_url[1]);
      $video['thumb_url'] = @str_replace(array('\x3d', '\x26'), array('=', '&'), $video['thumb_url']);
    }
    return $video;

  case "youtube":
    @preg_match('#\=([\-_a-z0-9]*)#i', $url, $id);
    if (empty($id[1])) return false;
    $video['id'] = $id[1];
    $video['ext'] = 'ytube';
    if ($_POST['thumbnail'] == 'thumb_from_server')
    {
      $video['thumb_url'] = 'http://img.youtube.com/vi/' . $video['id'] . '/default.jpg';
    }
    return $video;

  case "dailymotion":
    @preg_match('#video/([_a-z0-9]*)#i', $url, $id);
    if (empty($id[1])) return false;
    $video['id'] = $id[1];
    $video['ext'] = 'dm';
    if ($_POST['thumbnail'] == 'thumb_from_server')
    {
      $video['thumb_url'] = 'http://www.dailymotion.com/thumbnail/160x120/video/' . $video['id'];
    }
    return $video;
    
  case "wideo":
    @preg_match('#video/([_a-z0-9]*)#i', $url, $id);
    if (empty($id[1])) return false;
    $video['id'] = $id[1];
    $video['ext'] = 'wideo';
    if ($_POST['thumbnail'] == 'thumb_from_server' and fetchRemote($url, $source))
    {
      @preg_match('#link rel\="thumbnail" href\="(.*)"#', $source, $matches);
      $video['thumb_url'] = @str_replace('74x54', '154x114', $matches[1]);
    }
    return $video;

  case "vimeo":
    @preg_match('#vimeo.com/([0-9]*)#i', $url, $id);
    if (empty($id[1])) return false;
    $video['id'] = $id[1];
    $video['ext'] = 'vimeo';
    if ($_POST['thumbnail'] == 'thumb_from_server' and fetchRemote($url, $source))
    {
      @preg_match('#link rel="image_src" href="(http://.*?)"#', $source, $matches);
      $video['thumb_url'] = @str_replace('160.jpg', '200.jpg', $matches[1]);
    }
    return $video;

  default:
    return false;
  }
}

/* MUST BE REMOVED WITH PIWIGO 2.0.0 */
if (!function_exists('fetchRemote'))
{
  function fetchRemote($src, &$dest, $user_agent='Piwigo', $step=0)
  {
    // After 3 redirections, return false
    if ($step > 3) return false;

    // Initialize $dest
    is_resource($dest) or $dest = '';

    // Try curl to read remote file
    if (function_exists('curl_init'))
    {
      $ch = @curl_init();
      @curl_setopt($ch, CURLOPT_URL, $src);
      @curl_setopt($ch, CURLOPT_HEADER, 1);
      @curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $content = @curl_exec($ch);
      $header_length = @curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $status = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
      @curl_close($ch);
      if ($content !== false and $status >= 200 and $status < 400)
      {
        if (preg_match('/Location:\s+?(.+)/', substr($content, 0, $header_length), $m))
        {
          return fetchRemote($m[1], $dest, $user_agent, $step+1);
        }
        $content = substr($content, $header_length);
        is_resource($dest) ? @fwrite($dest, $content) : $dest = $content;
        return true;
      }
    }

    // Try file_get_contents to read remote file
    if (ini_get('allow_url_fopen'))
    {
      $content = @file_get_contents($src);
      if ($content !== false)
      {
        is_resource($dest) ? @fwrite($dest, $content) : $dest = $content;
        return true;
      }
    }

    // Try fsockopen to read remote file
    $src = parse_url($src);
    $host = $src['host'];
    $path = isset($src['path']) ? $src['path'] : '/';
    $path .= isset($src['query']) ? '?'.$src['query'] : '';
    
    if (($s = @fsockopen($host,80,$errno,$errstr,5)) === false)
    {
      return false;
    }

    fwrite($s,
      "GET ".$path." HTTP/1.0\r\n"
      ."Host: ".$host."\r\n"
      ."User-Agent: ".$user_agent."\r\n"
      ."Accept: */*\r\n"
      ."\r\n"
    );

    $i = 0;
    $in_content = false;
    while (!feof($s))
    {
      $line = fgets($s);

      if (rtrim($line,"\r\n") == '' && !$in_content)
      {
        $in_content = true;
        $i++;
        continue;
      }
      if ($i == 0)
      {
        if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/',rtrim($line,"\r\n"), $m))
        {
          fclose($s);
          return false;
        }
        $status = (integer) $m[2];
        if ($status < 200 || $status >= 400)
        {
          fclose($s);
          return false;
        }
      }
      if (!$in_content)
      {
        if (preg_match('/Location:\s+?(.+)$/',rtrim($line,"\r\n"),$m))
        {
          fclose($s);
          return fetchRemote(trim($m[1]),$dest,$user_agent,$step+1);
        }
        $i++;
        continue;
      }
      is_resource($dest) ? @fwrite($dest, $line) : $dest .= $line;
      $i++;
    }
    fclose($s);
    return true;
  }
}

// Creation du nouveau fichier
if (isset($_POST['submit_add']) and !is_adviser())
{
  if (empty($_POST['pywaie_add_name']) or empty($_POST['pywaie_add_url']) or ($_POST['parent'] == 0))
  {
    array_push($page['errors'], l10n('py_error2'), l10n('py_error3'));
  }
  else
  {
    $py_url = $_POST['pywaie_add_url'];
    if (!substr_count($py_url, "http://")) $py_url = "http://" . $py_url;

    // Extraction de l'id et du type
    foreach ($video_types as $type)
    {
      if (substr_count($py_url, $type))
      {
        $video = get_video_infos($py_url, $type);
        break;
      }
    }

    if (!isset($video) or !$video)
    {
      array_unshift($page['errors'], l10n('py_error5'));
    }
    else
    {
      $cat = $_POST['parent'];
      $video['name'] = str_replace(" ", "_", $_POST['pywaie_add_name']);
      $catpath = get_fulldirs(array($cat));
      $path_file = $catpath[$cat] . '/' . $video['name'] . '.' . $video['ext'];
      $thefile = substr($path_file, 2);
      if (file_exists($path_file))
      {
        array_push($page['errors'], sprintf(l10n('py_error6'), $thefile));
      }
      else
      {
        $file = @fopen($thefile , 'w');

        // Ecriture du fichier et attribution des messages
        if (@!fwrite ($file, stripslashes($video['id']) . '/' . $_POST['pywaie_add_h'] . '/' . $_POST['pywaie_add_w'] . '/' . $_POST['pywaie_add_start']))
        {
          array_push($page['errors'], sprintf(l10n('py_error7'), $thefile), sprintf(l10n('py_error8'), $catpath[$cat]));
        }
        else
        {
          // Miniatures
          $thumb_extension = 'NULL';
          if ($_POST['thumbnail'] == 'thumb_from_server' or $_POST['thumbnail'] == 'thumb_from_user')
          {
					  include_once ('thumbnails.php');
          }
          
          // Synchronisation avec la base de donnees
          $infos['name'] = (!empty($_POST['name']) ? '"' . $_POST['name'] . '"' : 'NULL');
          $infos['description'] = (!empty($_POST['description']) ? '"' . $_POST['description'] . '"' : 'NULL');
          $infos['author'] = (!empty($_POST['author']) ? '"' . $_POST['author'] . '"' : 'NULL');

          $query = 'SELECT IF(MAX(id)+1 IS NULL, 1, MAX(id)+1) AS next_element_id  FROM ' . IMAGES_TABLE . ' ;';
          list($next_element_id) = mysql_fetch_array(pwg_query($query));

          pwg_query('INSERT INTO ' . IMAGES_TABLE . ' ( id , file , date_available , date_creation , tn_ext , name , comment , author , hit , filesize , width , height , representative_ext , date_metadata_update , average_rate , has_high , path , storage_category_id , high_filesize )
					  VALUES ( ' . $next_element_id . ', "' . $video['name'] . '.' . $video['ext'] . '", NOW() , NULL , ' . $thumb_extension . ' ,  ' . $infos['name'] . ' , ' . $infos['description'] . ' , ' . $infos['author'] . ' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , "' . $path_file . '", ' . $cat . ', NULL);');
          pwg_query('INSERT INTO ' . IMAGE_CATEGORY_TABLE . ' ( image_id , category_id )
					  VALUES ( ' . $next_element_id . ', ' . $cat . ');');

          $query = 'SELECT representative_picture_id FROM ' . CATEGORIES_TABLE . ' WHERE id =' .  $cat . ';';
          list($result) = mysql_fetch_array(pwg_query($query));
          if ($result === NULL)
          {
            pwg_query('UPDATE ' . CATEGORIES_TABLE . ' SET representative_picture_id=' . $next_element_id . ' WHERE id = ' . $cat . ' LIMIT 1');
          }

          invalidate_user_cache();
          array_unshift($page['infos'], sprintf(l10n('py_info3'), $thefile));
          array_push($page['infos'], sprintf(l10n('py_show_file'), PHPWG_ROOT_PATH . 'picture.php?/' . $next_element_id . '/category/' . $cat));
          @fclose($file);
        }
      }
    }
  }
}


// Affichage de la liste des categories
$site_locaux = array();
$query = '
SELECT id , galleries_url
FROM ' . SITES_TABLE . '
ORDER by id';
$result = pwg_query($query);

if (mysql_num_rows($result) > 0)
{
  while (list($id , $galleries_url) = mysql_fetch_row($result))
  {
    if (!url_is_remote($galleries_url)) array_push($site_locaux , $id);
  }
}
if (empty($site_locaux))
{
  array_push($page['errors'], l10n('py_error1'));
  $site_locaux = array(0);
}

$query = '
SELECT id,name,uppercats,global_rank
  FROM ' . CATEGORIES_TABLE . '
  WHERE site_id IN (' . implode("," , $site_locaux) . ');';

if (isset($_POST['parent'])) $selected = array($_POST['parent']);
else $selected = array();

display_select_cat_wrapper($query, $selected , 'category_option_parent', false);


// Parametrage du template
if (isset($_POST['submit_add']))
{
  $template->assign(array(
    'PYWAIE_ADD_NAME' => $_POST['pywaie_add_name'],
    'PYWAIE_ADD_URL' => $_POST['pywaie_add_url'],
		$_POST['thumbnail'] . '_CHECKED' => 'checked="checked"',
    'ADD_BAND' => isset($_POST['add_band']) ? 'checked="checked"' : '',
    'THUMB_RESIZE' => isset($_POST['thumb_resize']) ? 'checked="checked"' : '',
    'PYWAIE_ADD_START' => $_POST['pywaie_add_start'],
    'PYWAIE_ADD_W' => $_POST['pywaie_add_w'],
    'PYWAIE_ADD_H' => $_POST['pywaie_add_h'],
    'NAME' => $_POST['name'],
    'AUTHOR' => $_POST['author'],
    'DESCRIPTION' => $_POST['description']));
}
else
{
  $template->assign(array('no_thumb_CHECKED' => 'checked="checked"'));
}

$template->assign(array(
  'INFOBULLES_JS' => GVIDEO_PATH . 'admin/infobulles.js',
  'ICON_INFOBULLE' => GVIDEO_PATH . 'admin/infobulle.png',
  'DEFAULT_THUMB_W' => $conf['tn_width'],
  'DEFAULT_THUMB_H' => $conf['tn_height']));

$template->set_filenames(array('plugin_admin_content' => dirname(__FILE__) . '/add_page.tpl'));
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>