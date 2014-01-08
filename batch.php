<?php
/*
Plugin Name: EVideo Batch Import
Version: 1.0
Author: Mistic100
*/

global $gvideo_links, $gvideo_safe, $gvideo_film_frame, $gvideo_category;

$gvideo_safe = false;
$gvideo_film_frame = true;
$gvideo_category = 0;
$gvideo_links = '';


add_event_handler('loc_begin_admin', 'gvideo_batch');

function gvideo_batch()
{
  global $conf, $page, $gvideo_links, $gvideo_safe, $gvideo_film_frame, $gvideo_category;
  
  if (!defined('GVIDEO_PATH')) return;
  if (@$_GET['page'] != 'plugin-gvideo') return;
  if ($gvideo_category == 0 or empty($gvideo_links)) return;
  
  set_time_limit(600);
  
  $links = str_replace("\r\n", "\n", $gvideo_links);
  $links = explode("\n", $links);
  $links = array_map('trim', $links);
  
  include_once(GVIDEO_PATH.'include/functions.inc.php');
  include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');

  foreach ($links as $url)
  {
    if (empty($url)) continue;
    $error = false;
    
    
    // check url
    if ( ($video = parse_video_url($url, $gvideo_safe)) === false )
    {
      if ($gvideo_safe)
      {
        array_push($page['errors'], $url.' : '.l10n('an error happened'));
        $error = true;
      }
      else
      {
        array_push($page['errors'], $url.' : '.l10n('Unable to contact host server'));
        array_push($page['errors'], $url.' : '.l10n('Try in safe-mode'));
        $error = true;
      }
    }
    
    if ($error) continue;
    
    
    // download thumbnail
    $thumb_ext = empty($video['thumbnail']) ? 'jpg' : get_extension($video['thumbnail']);
    $thumb_name = $video['type'].'-'.$video['video_id'].'-'.uniqid().'.'.$thumb_ext;
    $thumb_source = $conf['data_location'].$thumb_name;
    if ( empty($video['thumbnail']) or gvideo_download_remote_file($video['thumbnail'], $thumb_source) !== true )
    {
      $thumb_source = $conf['data_location'].get_filename_wo_extension($thumb_name).'.jpg';
      copy(GVIDEO_PATH.'mimetypes/'.$video['type'].'.jpg', $thumb_source);
    }
    
    if ($gvideo_film_frame)
    {
      add_film_frame($thumb_source);
    }
    
    
    // add image and update infos
    $image_id = add_uploaded_file($thumb_source, $thumb_name, array($gvideo_category));
    
    $updates = array(
      'name' => pwg_db_real_escape_string($video['title']),
      'author' => pwg_db_real_escape_string($video['author']),
      'is_gvideo' => 1,
      );
    
    if ( !empty($video['description']) )
    {
      $updates['comment'] = pwg_db_real_escape_string($video['description']);
    }
    if ( !empty($video['tags']) )
    {
      set_tags(get_tag_ids(str_replace("'", "\'", implode(',', $video['tags']))), $image_id);
    }
    
    single_update(
      IMAGES_TABLE,
      $updates,
      array('id' => $image_id),
      true
      );
    
    // register video
    $insert = array(
      'picture_id' => $image_id,
      'url' => $video['url'],
      'type' => $video['type'],
      'video_id' => $video['video_id'],
      'width' => '',
      'height' => '',
      'autoplay' => '',
      );
      
    single_insert(
      GVIDEO_TABLE,
      $insert
      );
    
    array_push($page['infos'], $url.' : '.l10n('Video successfully added'));
  }
}
  
?>