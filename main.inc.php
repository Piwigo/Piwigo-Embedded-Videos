<?php 
/*
Plugin Name: Embedded Videos
Version: auto
Description: Add videos from Dailymotion, Youtube, Vimeo, Wideo, videobb and Wat.
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=136
Author: Mistic & P@t
Author URI: http://www.strangeplanet.fr
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable;

define('GVIDEO_PATH',    PHPWG_PLUGINS_PATH . 'gvideo/');
define('GVIDEO_ADMIN',   get_root_url() . 'admin.php?page=plugin-gvideo');
define('GVIDEO_TABLE',   $prefixeTable.'image_video');
define('GVIDEO_VERSION', '2.4.d');


add_event_handler('init', 'gvideo_init');
add_event_handler('picture_pictures_data', 'gvideo_prepare_picture');
add_event_handler('render_element_content', 'gvideo_element_content', EVENT_HANDLER_PRIORITY_NEUTRAL-10, 2);

if (defined('IN_ADMIN'))
{
  add_event_handler('delete_elements', 'gvideo_delete_elements');
  add_event_handler('get_admin_plugin_menu_links', 'gvideo_admin_menu');
  add_event_handler('tabsheet_before_select','gvideo_tab', EVENT_HANDLER_PRIORITY_NEUTRAL+10, 2); 
}

include_once(GVIDEO_PATH . 'include/gvideo.inc.php');


/**
 * update & load language
 */
function gvideo_init()
{
  global $pwg_loaded_plugins;
  
  if (
    $pwg_loaded_plugins['gvideo']['version'] == 'auto' or
    version_compare($pwg_loaded_plugins['gvideo']['version'], GVIDEO_VERSION, '<')
  )
  {
    include_once(GVIDEO_PATH . 'include/install.inc.php');
    gvideo_install();
    
    if ($pwg_loaded_plugins['gvideo']['version'] != 'auto')
    {
      $query = '
UPDATE '. PLUGINS_TABLE .'
SET version = "'. GVIDEO_VERSION .'"
WHERE id = "gvideo"';
      pwg_query($query);
      
      $pwg_loaded_plugins['gvideo']['version'] = GVIDEO_VERSION;
      
      if (defined('IN_ADMIN'))
      {
        $_SESSION['page_infos'][] = 'Embedded Videos updated to version '. GVIDEO_VERSION;
      }
    }
  }
  
  load_language('plugin.lang', GVIDEO_PATH);
}

/**
 * admin plugins menu
 */
function gvideo_admin_menu($menu) 
{
  array_push($menu, array(
    'NAME' => 'Embedded Videos',
    'URL' => GVIDEO_ADMIN,
  ));
  return $menu;
}

/**
 * special tabs
 */
function gvideo_tab($sheets, $id)
{
  if ($id != 'photo') return $sheets;
  
  $query = '
SELECT *
  FROM '.GVIDEO_TABLE.'
  WHERE picture_id = '.$_GET['image_id'].'
;';
  $result = pwg_query($query);

  if (!pwg_db_num_rows($result)) return $sheets;
  
  global $gvideo;
  $gvideo = pwg_db_fetch_assoc($result);
  
  $sheets['gvideo'] = array(
    'caption' => l10n('Video properties'),
    'url' => GVIDEO_ADMIN.'-photo&amp;image_id='.$_GET['image_id'],
    );
  unset($sheets['coi'], $sheets['update']);
  
  return $sheets;
}

?>