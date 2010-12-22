<?php
/*
Plugin Name: PY GVideo
Version: auto
Description: Adds some videos from Google Video, Dailymotion, Youtube, Wideo, Vimeo or Wat.
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=136
Author: PYwaie & P@t
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
define('GVIDEO_DIR' , basename(dirname(__FILE__)));
define('GVIDEO_PATH' , PHPWG_PLUGINS_PATH . GVIDEO_DIR . '/');

global $conf, $py_addext;
$py_addext = array("gvideo", "dm", "ytube", "wideo", "vimeo", "wat");
$conf['file_ext'] = array_merge($conf['file_ext'], $py_addext);

function gvideoadd($content)
{
  global $page, $picture, $template, $py_addext, $conf;

  if (!isset($picture['current']['file']))
	{
		return $content;
	}
  $extension = strtolower(get_extension($picture['current']['file']));
  if (!in_array($extension, $py_addext) or $page['slideshow'])
	{
    return $content;
	}
	include_once( GVIDEO_PATH . '/gvideo.php');
  return $content;
}


function py_mimetype($location, $element_info)
{
  if ( empty( $element_info['tn_ext'] ) )
  {
    global $py_addext;
    $extension = strtolower(get_extension($element_info['path']));
    if (in_array($extension, $py_addext))
    {
      $location= 'plugins/' . GVIDEO_DIR . '/mimetypes/' . $extension . '.png';
    }
  }
  return $location;
}

if (script_basename() == 'admin')
{
	add_event_handler('get_admin_plugin_menu_links', 'gvideo_admin_menu');
	
	function gvideo_admin_menu($menu)
	{
		array_push($menu, array(
			'NAME' => 'PY GVideo',
			'URL' => get_admin_plugin_menu_link( GVIDEO_PATH . '/admin/pywaie_admin.php')));
		return $menu;
	}
}

add_event_handler('render_element_content', 'gvideoadd');
add_event_handler('get_thumbnail_location', 'py_mimetype', 60, 2);

?>