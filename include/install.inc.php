<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable;

// defined here only because it's more convenient
define('gvideo_path', PHPWG_PLUGINS_PATH . 'gvideo/');
define('gvideo_table', $prefixeTable.'image_video');

/**
 * installation
 */
function gvideo_install() 
{
  global $conf;
  
  // configuration
  if (empty($conf['gvideo']))
  {
    $gvideo_default_config = serialize(array(
      'autoplay' => 0,
      'width' => 640,
      'height' => 360,
      'sync_description' => 1,
      'sync_tags' => 1,
      'vimeo' => array(
        'title' => 1,
        'portrait' => 1,
        'byline' => 1,
        'color' => '00adef',
        ),
      'dailymotion' => array(
        'logo' => 1,
        'title' => 1,
        'color' => 'F7FFFD',
        ),
      'youtube' => array(),
      'wat' => array(),
      'wideo' => array(),
      ));
    
    conf_update_param('gvideo', $gvideo_default_config);
    $conf['gvideo'] = $gvideo_default_config;
  }
  else
  {
    if (is_string($conf['gvideo']))
    {
      $conf['gvideo'] = unserialize($conf['gvideo']);
    }
    
    if (!isset($conf['gvideo']['sync_description']))
    {
      $conf['gvideo']['sync_description'] = 1;
      $conf['gvideo']['sync_tags'] = 1;
      
      conf_update_param('gvideo', serialize($conf['gvideo']));
    }
  }
  
  // create table
  $query = '
CREATE TABLE IF NOT EXISTS `'.gvideo_table.'` (
  `picture_id` mediumint(8) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `type` varchar(64) NOT NULL,
  `video_id` varchar(128) NOT NULL,
  `width` smallint(9) DEFAULT NULL,
  `height` smallint(9) DEFAULT NULL,
  `autoplay` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;';
  pwg_query($query);
  
  // update video_id lenght
  $query = 'ALTER TABLE `'.gvideo_table.'` CHANGE `video_id` `video_id` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;';
  pwg_query($query);
  
  // new collumn in images table
  $result = pwg_query('SHOW COLUMNS FROM '.IMAGES_TABLE.' LIKE "is_gvideo";');
  if (!pwg_db_num_rows($result))
  {
    pwg_query('ALTER TABLE `' . IMAGES_TABLE . '` ADD `is_gvideo` TINYINT(1) NOT NULL DEFAULT 0;');
    
    $query = '
UPDATE '.IMAGES_TABLE.'
  SET is_gvideo = 1
  WHERE id IN(
    SELECT picture_id FROM '.gvideo_table.'
    )
;';
    pwg_query($query);
  }
  
  // remove old configuration and upgrade video files
  if (isset($conf['PY_GVideo']))
  {
    pwg_query('DELETE FROM `'. CONFIG_TABLE .'` WHERE param = "PY_GVideo" LIMIT 1;');
    unset($conf['PY_GVideo']);
  
    gvideo_update_24();
  }
}


/**
 * update from 2.3 to 2.4
 */
function gvideo_update_24()
{
  global $conf;
  
  // search existing videos
  $query = '
SELECT *
  FROM '.IMAGES_TABLE.'
  WHERE
    file LIKE "%.gvideo"
    OR file LIKE "%.dm"
    OR file LIKE "%.ytube"
    OR file LIKE "%.wideo"
    OR file LIKE "%.vimeo"
    OR file LIKE "%.wat"
;';
  $result = pwg_query($query);
  
  if (!pwg_db_num_rows($result))
  {
    return;
  }
  
  if (!isset($conf['prefix_thumbnail']))
  {
    $conf['prefix_thumbnail'] = 'TN-';
  }

  if (!isset($conf['dir_thumbnail']))
  {
    $conf['dir_thumbnail'] = 'thumbnail';
  }
  
  set_time_limit(600);
  include_once(gvideo_path . 'include/functions.inc.php');
  include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');
  
  $videos_inserts = array();
  $images_updates = array();
  $images_delete = array();
  
  while ($img = pwg_db_fetch_assoc($result))
  {
    $file_content = file_get_contents($img['path']);
    list($file['id'], $file['height'], $file['width'], ) = explode('/', $file_content);
    $file['type'] = get_extension($img['path']);
    
    switch ($file['type'])
    {
      case 'vimeo':
        $video = array(
          'type' => 'vimeo',
          'url' => 'http://vimeo.com/'.$file['id'],
          );
        break;
      case 'dm':
        $video = array(
          'type' => 'dailymotion',
          'url' => 'http://dailymotion.com/video/'.$file['id'],
          );
        break;
      case 'ytube':
        $video = array(
          'type' => 'youtube',
          'url' => 'http://youtube.com/watch?v='.$file['id'],
          );
        break;
      case 'wideo':
        $video = array(
          'type' => 'wideo',
          'url' => 'http://wideo.fr/video/'.$file['id'].'.html',
          );
        break;
      case 'wat':
        $video = array(
          'type' => 'wat',
          'url' => null,
          );
        break;
      case 'gvideo': // closed
      default:
        array_push($images_delete, $img['id']);
        continue;
    }
    
    $real_path = str_replace($img['file'], null, str_replace('././', './', $img['path']));
    
    // get existing thumbnail
    $thumb = $real_path.$conf['dir_thumbnail'].'/'.$conf['prefix_thumbnail'].get_filename_wo_extension($img['file']).'.*';
    $thumb = glob($thumb);
    if (!empty($thumb))
    {
      $thumb_name = $video['type'].'-'.$file['id'].'-'.uniqid().'.'.get_extension($thumb[0]);
      $thumb_source = $conf['data_location'].$thumb_name;
      copy($thumb[0], $thumb_source);
    }
    else
    {
      $thumb_name = $video['type'].'-'.$file['id'].'-'.uniqid().'.jpg';
      $thumb_source = $conf['data_location'].$thumb_name;
      copy(gvideo_path.'mimetypes/'.$video['type'].'.jpg', $thumb_source);
      add_film_frame($thumb_source);
    }
    
    // update element
    $image_id = add_uploaded_file($thumb_source, $thumb_name, null, null, $img['id']);
    
    // update path and rename the file
    $img['new_path'] = $real_path.$thumb_name;
    rename($img['path'], $img['new_path']);
    array_push($images_updates, array(
      'id' => $img['id'],
      'path' => $img['new_path'],
      'is_gvideo' => 1,
      ));
    
    if (empty($file['width'])) $file['width'] = '';
    if (empty($file['height'])) $file['height'] = '';
    
    // register video    
    array_push($videos_inserts, array(
      'picture_id' => $image_id,
      'url' => $video['url'],
      'type' => $video['type'],
      'video_id' => $file['id'],
      'width' => $file['width'],
      'height' => $file['height'],
      'autoplay' => '',
      ));
      
    unset($thumb_source, $thumb_name, $file, $video, $url);
  }
  
  // delete obsolete elements
  delete_elements($images_delete);
  
  // registers videos
  mass_inserts(
    gvideo_table,
    array('picture_id', 'url', 'type', 'video_id', 'width', 'height', 'autoplay'),
    $videos_inserts
    );
    
  // update images
  mass_updates(
    IMAGES_TABLE,
    array('primary'=>array('id'), 'update'=>array('path', 'is_gvideo')),
    $images_updates
    );
}