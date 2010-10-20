<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

include_once(PHPWG_ROOT_PATH.'admin/include/functions_upload.inc.php');

$video_types = array('google', 'youtube', 'dailymotion', 'wideo', 'vimeo', 'wat');

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
      if (preg_match('#meta property="og:image" content="(http://.*?)"#', $source, $matches))
      {
        $video['thumb_url'] = str_replace('160.jpg', '200.jpg', $matches[1]);
      }
    }
    return $video;

  case "wat":
    if (fetchRemote($url, $source))
    {
      @preg_match('#link rel="video_src" href="http://www.wat.tv/swf2/(.*?)"#i', $source, $id);
      if (empty($id[1])) return false;
      $video['id'] = $id[1];
      $video['ext'] = 'wat';
      if ($_POST['thumbnail'] == 'thumb_from_server')
      {
        @preg_match('#link rel="image_src" href="(.*?)"#', $source, $matches);
        $video['thumb_url'] = @str_replace('120x90', '320x240', $matches[1]);
      }
      return $video;
    }

  default:
    return false;
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
      // current date
      list($dbnow) = pwg_db_fetch_row(pwg_query('SELECT NOW();'));

      $file_name = str2url($_POST['pywaie_add_name']).'.'.$video['ext'];

      // prepare database registration
      $insert = array(
        'name' => $_POST['pywaie_add_name'],
        'file' => $file_name,
        'date_available' => $dbnow,
        );
      
      $optional_fields = array('author', 'comment');
      foreach ($optional_fields as $field)
      {
        if (isset($_POST[$field]) and !empty($_POST[$field]))
        {
          $insert[$field] = $_POST[$field];
        }
      }
      
      check_input_parameter('parent', $_POST, false, PATTERN_ID);
      $category_id = $_POST['parent'];
      
      $query = '
SELECT
    c.id,
    c.name,
    c.permalink,
    c.dir,
    c.site_id,
    s.galleries_url
  FROM '.CATEGORIES_TABLE.' AS c
    LEFT JOIN '.SITES_TABLE.' AS s ON s.id = c.site_id
  WHERE c.id = '.$category_id.'
;';
      $result = pwg_query($query);
      $category = pwg_db_fetch_assoc($result);

      // is the category virtual or is the category on a remote site?
      $use_galleries_directory = true;
      if (empty($category['dir']))
      {
        $use_galleries_directory = false;
      }
      if (!empty($category['galleries_url']) and url_is_remote($category['galleries_url']))
      {
        $use_galleries_directory = false;
      }
      
      if (!$use_galleries_directory)
      {
        list($year, $month, $day) = preg_split('/[^\d]/', $dbnow, 4);
  
        // upload directory hierarchy
        $upload_dir = sprintf(
          PHPWG_ROOT_PATH.$conf['upload_dir'].'/%s/%s/%s',
          $year,
          $month,
          $day
          );
        prepare_directory($upload_dir);

        $file_path = $upload_dir.'/'.$file_name;
        $thumb_path = file_path_for_type($file_path, 'thumb');
        $thumb_dir = dirname($thumb_path);
        prepare_directory($thumb_dir);
      }
      // or physical and local
      else
      {
        $catpath = get_fulldirs(array($category_id));
        $file_path = $catpath[$category_id].'/'.$file_name;

        $insert['storage_category_id'] = $category_id;
      }

      $insert['path'] = $file_path;
      
      if (file_exists($file_path))
      {
        array_push($page['errors'], sprintf(l10n('py_error6'), $file_path));
      }
      else
      {
        // Write fake file with video settings inside
        //
        // TODO: store these information in a specific database table instead
        $file_content = stripslashes($video['id']);
        $file_content.= '/'.$_POST['pywaie_add_h'];
        $file_content.= '/'.$_POST['pywaie_add_w'];
        $file_content.= '/'.$_POST['pywaie_add_start'];

        $bytes_written = file_put_contents($file_path, $file_content);        
        if (false === $bytes_written)
        {
          array_push(
            $page['errors'],
            
            sprintf(
              l10n('py_error7'),
              $file_path
              ),
            
            sprintf(
              l10n('py_error8'),
              dirname($file_path)
              )
            );

        }
        else
        {
          // thumbnail
          $thumb_extension = null;
          if ($_POST['thumbnail'] == 'thumb_from_server' or $_POST['thumbnail'] == 'thumb_from_user')
          {
            include_once ('thumbnails.php');
            $insert['tn_ext'] = $thumb_extension;
          }

          // database registration
          mass_inserts(
            IMAGES_TABLE,
            array_keys($insert),
            array($insert)
            );
          
          $image_id = pwg_db_insert_id(IMAGES_TABLE);
          associate_images_to_categories(array($image_id), array($category_id));
          invalidate_user_cache();

          // success information to display
          array_unshift($page['infos'], sprintf(l10n('py_info3'), $file_path));
          
          array_push(
            $page['infos'],
            sprintf(
              l10n('py_show_file'),
              make_picture_url(
                array(
                  'image_id' => $image_id,
                  'section' => 'categories',
                  'category' => $category,
                  )
                )
              )
            );
        }
      }
    }
  }
}


// display list of all categories
$query = '
SELECT
    id,
    name,
    uppercats,
    global_rank
  FROM '.CATEGORIES_TABLE.'
;';

if (isset($_POST['parent']))
{
  $selected = array($_POST['parent']);
}
else
{
  $selected = array();
}

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
    'AUTHOR' => $_POST['author'],
    'COMMENT' => $_POST['comment']));
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