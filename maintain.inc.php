<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable;

define('gvideo_path', PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)));
define('gvideo_table', $prefixeTable.'image_video');

define(
  'gvideo_default_config', 
  serialize(array(
    'autoplay' => 0,
    'width' => 640,
    'height' => 360,
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
    'videobb' => array(),
    ))
  );


function plugin_install() 
{
  global $conf;
  
  conf_update_param('gvideo', gvideo_default_config);
  
  $query = '
CREATE TABLE IF NOT EXISTS `'.gvideo_table.'` (
  `picture_id` mediumint(8) NOT NULL,
  `type` varchar(64) NOT NULL,
  `video_id` varchar(64) NOT NULL,
  `width` smallint(9) DEFAULT NULL,
  `height` smallint(9) DEFAULT NULL,
  `autoplay` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;';
  pwg_query($query);
  
  if (isset($conf['PY_GVideo']))
  {
    pwg_query('DELETE FROM `'. CONFIG_TABLE .'` WHERE param = "PY_GVideo" LIMIT 1;');
    unset($conf['PY_GVideo']);
  
    gvideo_update_24();
  }
}

function plugin_activate()
{
  global $conf;
    
  if (isset($conf['PY_GVideo']))
  {
    plugin_install();
  }
  else if (!isset($conf['gvideo']))
  {
    conf_update_param('gvideo', gvideo_default_config);
  }
}

function plugin_uninstall() 
{  
  pwg_query('DELETE FROM `'. CONFIG_TABLE .'` WHERE param = "gvideo" LIMIT 1;');
  pwg_query('DROP TABLE `'.gvideo_table.'`;');
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
  
  set_time_limit(600);
  include_once(gvideo_path . '/include/functions.inc.php');
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
        $url = 'http://vimeo.com/'.$file['id'];
        break;
      case 'dm':
        $url = 'http://dailymotion.com/video/'.$file['id'];
        break;
      case 'ytube':
        $url = 'http://youtu.be/'.$file['id'];
        break;
      case 'wideo':
        $url = 'http://wideo.fr/video/'.$file['id'].'.html';
        break;
        
      case 'wat': // can't get original page from id !
        $thumb = str_replace($img['file'], null, $img['path']).'thumbnail/TN-'.get_filename_wo_extension($img['file']).'.*';
        $thumb = glob($thumb);
        if (!empty($thumb))
        {
          $thumb_name = 'wat-'.$file['id'].'-'.uniqid().'.'.get_extension($thumb[0]);
          $thumb_source = $conf['data_location'].$thumb_name;
          copy($thumb[0], $thumb_source);
        }
        
        $video = array(
          'type' => 'wat',
          'id' => $file['id'],
          'title' => null,
          'description' => null,
          'thumbnail' => null,
          );
        break;
        
      case 'gvideo': // closed
      default:
        array_push($images_delete, $img['id']);
        continue;
    }
    
    // get video infos
    if (!isset($video))
    {
      if ( ($video = parse_video_url($url)) === false )
      {
        array_push($images_delete, $img['id']);
        continue;
      }
    }
    
    // download thumbnail
    if (!isset($thumb_source))
    {
      $thumb_name = $video['type'].'-'.$video['id'].'-'.uniqid().'.'.get_extension($video['thumbnail']);
      $thumb_source = $conf['data_location'].$thumb_name;
      if (download_remote_file($video['thumbnail'], $thumb_source) !== true)
      {
        $thumb_source = $conf['data_location'].get_filename_wo_extension($thumb_name).'.jpg';
        copy(gvideo_path.'mimetypes/'.$video['type'].'.jpg', $thumb_source);
      }
    }
    
    // update element
    $image_id = add_uploaded_file($thumb_source, $thumb_name, null, null, $img['id']);
    
    // update path and rename the file
    $img['new_path'] = str_replace($img['file'], null, $img['path']).$thumb_name;
    rename($img['path'], $img['new_path']);
    array_push($images_updates, array(
      'id' => $img['id'],
      'path' => str_replace('././', './', $img['new_path']),
      ));
    
    if (empty($file['width'])) $file['width'] = '';
    if (empty($file['height'])) $file['height'] = '';
    
    // register video    
    array_push($videos_inserts, array(
      'picture_id' => $image_id,
      'type' => $video['type'],
      'video_id' => $video['id'],
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
    array('picture_id', 'type', 'video_id', 'width', 'height', 'autoplay'),
    $videos_inserts
    );
    
  // update images
  mass_updates(
    IMAGES_TABLE,
    array('primary'=>array('id'), 'update'=>array('path')),
    $images_updates
    );
}

?>