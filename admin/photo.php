<?php
defined('GVIDEO_PATH') or die ("Hacking attempt!");

include_once(GVIDEO_PATH.'include/functions.inc.php');
include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');


// +-----------------------------------------------------------------------+
// | Basic checks                                                          |
// +-----------------------------------------------------------------------+
check_status(ACCESS_ADMINISTRATOR);

check_input_parameter('image_id', $_GET, false, PATTERN_ID);

$admin_photo_base_url = get_root_url().'admin.php?page=photo-'.$_GET['image_id'];
$self_url = GVIDEO_ADMIN.'-photo&amp;image_id='.$_GET['image_id'];

// +-----------------------------------------------------------------------+
// | Tabs                                                                  |
// +-----------------------------------------------------------------------+
include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$tabsheet = new tabsheet();
$tabsheet->set_id('photo');
$tabsheet->select('gvideo');
$tabsheet->assign();

$page['active_menu'] = get_active_menu('photo');

// +-----------------------------------------------------------------------+
// | Picture infos                                                         |
// +-----------------------------------------------------------------------+
global $gvideo; // request from GVIDEO_TABLE done when building tabsheet

$query = '
SELECT *
  FROM '.IMAGES_TABLE.'
  WHERE id = '.$_GET['image_id'].'
;';
$picture = pwg_db_fetch_assoc(pwg_query($query));


// +-----------------------------------------------------------------------+
// | Update properties                                                     |
// +-----------------------------------------------------------------------+
if (isset($_POST['save_properties']))
{
  $_POST['url'] = trim($_POST['url']);
  
  if ($gvideo['type'] != 'embed')
  {
    // check inputs
    if (empty($_POST['url']))
    {
      $page['errors'][] = l10n('Please fill the video URL');
    }
    else if ($gvideo['url']!=$_POST['url'])
    {
      if ( ($video = parse_video_url($_POST['url'], $safe_mode)) === false )
      {
        $page['errors'][] = l10n('an error happened');
      }
    }
    else
    {
      $safe_mode = false;
      $video = $gvideo;
    }
    
    if (count($page['errors']) == 0)
    {
      if ($safe_mode === true)
      {
        $page['warnings'][] = l10n('Unable to contact host server');
        $page['warnings'][] = l10n('Video data like description and thumbnail might be missing');
      }
      
      if ($gvideo['url'] != $video['url'])
      {
        // download thumbnail
        $thumb_ext = empty($video['thumbnail']) ? 'jpg' : get_extension($video['thumbnail']);
        $thumb_name = $video['type'].'-'.$video['video_id'].'-'.uniqid().'.'.$thumb_ext;
        $thumb_source = $conf['data_location'].$thumb_name;
        
        if (empty($video['thumbnail']) or gvideo_download_remote_file($video['thumbnail'], $thumb_source) !== true)
        {
          $thumb_source = $conf['data_location'].get_filename_wo_extension($thumb_name).'.jpg';
          copy(GVIDEO_PATH.'mimetypes/'.$video['type'].'.jpg', $thumb_source);
        }
        
        // add image and update infos
        $image_id = add_uploaded_file($thumb_source, $thumb_name, null, null, $_GET['image_id']);
        
        $updates = array(
          'name' => pwg_db_real_escape_string($video['title']),
          'author' => pwg_db_real_escape_string($video['author']),
          'is_gvideo' => 1,
          );
          
        if ($_POST['sync_description'] and !empty($video['description']))
        {
          $updates['comment'] = pwg_db_real_escape_string($video['description']);
        }
        else
        {
          $updates['comment'] = null;
        }
        if ($_POST['sync_tags'] and !empty($video['tags']))
        {
          set_tags(get_tag_ids(implode(',', $video['tags'])), $image_id);
        }
        
        single_update(
          IMAGES_TABLE,
          $updates,
          array('id' => $_GET['image_id']),
          true
          );
      }
      
      // register video
      if ($_POST['size_common'] == 'true')
      {
        $_POST['width'] = $_POST['height'] = '';
      }
      else if (!preg_match('#^([0-9]+)$#', $_POST['width']) or !preg_match('#^([0-9]+)$#', $_POST['height']))
      {
        $page['errors'][] = l10n('Width and height must be integers');
        $_POST['width'] = $_POST['height'] = '';
      }
      if ($_POST['autoplay_common'] == 'true')
      {
        $_POST['autoplay'] = '';
      }
      
      $updates = array(
        'url' => $video['url'],
        'type' => $video['type'],
        'video_id' => $video['video_id'],
        'width' => $_POST['width'],
        'height' => $_POST['height'],
        'autoplay' => $_POST['autoplay'],
        );
    }
  }
  else
  {
    $_POST['embed_code'] = trim($_POST['embed_code']);
    
    if (empty($_POST['embed_code']))
    {
      $page['errors'][] = l10n('Please fill the embed code');
    }
    else
    {
      $updates = array(
        'url' => $_POST['url'],
        'embed' => $_POST['embed_code'],
        );
    }
  }
  
  if (isset($updates))
  {
    single_update(
      GVIDEO_TABLE,
      $updates,
      array('picture_id' => $_GET['image_id']),
      true
      );
      
    $page['infos'][] = l10n('Video successfully updated');
    $gvideo = array_merge($gvideo, $updates);
  }
}

// +-----------------------------------------------------------------------+
// | Update thumbnail (from Photo Update)                                  |
// +-----------------------------------------------------------------------+
if (isset($_FILES['photo_update']))
{
  if ($_FILES['photo_update']['error'] !== UPLOAD_ERR_OK)
  {
    $page['errors'][] = file_upload_error_message($_FILES['photo_update']['error']);
  }
  else
  {
    add_uploaded_file(
      $_FILES['photo_update']['tmp_name'],
      $_FILES['photo_update']['name'],
      null,
      null,
      $_GET['image_id']
      );

    $page['infos'][] = l10n('The thumbnail was updated');
  }
}

// +-----------------------------------------------------------------------+
// | Add film frame                                                        |
// +-----------------------------------------------------------------------+
if (function_exists('imagecreatetruecolor') and isset($_GET['add_film_frame']))
{
  $thumb_source = $conf['data_location'].$picture['file'];
  
  add_film_frame($picture['path'], $thumb_source);
  add_uploaded_file($thumb_source, $picture['file'], null, null, $_GET['image_id']);
  
  redirect($self_url);
}

// +-----------------------------------------------------------------------+
// | Reset thumbnail                                                       |
// +-----------------------------------------------------------------------+
if (isset($_GET['reset_thumbnail']))
{
  $video = parse_video_url($gvideo['url']);
  $thumb = gvideo_get_thumbnail($video);
  add_uploaded_file($thumb['source'], $picture['file'], null, null, $_GET['image_id']);

  redirect($self_url);
}

// +-----------------------------------------------------------------------+
// | Template                                                              |
// +-----------------------------------------------------------------------+
if (empty($gvideo['height']))
{
  $gvideo['size_common'] = 'true';
}
if (empty($gvideo['autoplay']))
{
  $gvideo['autoplay_common'] = 'true';
}
$gvideo['sync_description'] = $conf['gvideo']['sync_description'];
$gvideo['sync_tags'] = $conf['gvideo']['sync_tags'];

if (function_exists('imagecreatetruecolor'))
{
  $template->assign('U_ADD_FILM_FRAME', $self_url.'&amp;add_film_frame=1');
}

$template->assign(array(
  'F_ACTION' => $self_url,
  'GVIDEO' => array_map('stripslashes', $gvideo),
  'TN_SRC' => DerivativeImage::thumb_url($picture).'?'.time(),
  'TITLE' => render_element_name($picture),
  'RESET_THUMBNAIL' => $self_url.'&amp;reset_thumbnail=1',
));

$template->set_filename('gvideo_content', realpath(GVIDEO_PATH . 'admin/template/photo.tpl'));
