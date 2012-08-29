<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

include_once(PHPWG_PLUGINS_PATH . 'gvideo/include/install.inc.php');

/* install */
function plugin_install() 
{
  gvideo_install();
  
  define('gvideo_installed', true);
}

/* activate */
function plugin_activate()
{
  if (!defined('gvideo_installed'))
  {
    gvideo_install();
  }
}

/* uninstall */
function plugin_uninstall() 
{
  global $prefixeTable;
  
  pwg_query('DELETE FROM `'. CONFIG_TABLE .'` WHERE param = "gvideo" LIMIT 1;');
  pwg_query('DROP TABLE `'.$prefixeTable.'image_video`;');
  pwg_query('ALTER TABLE `' . IMAGES_TABLE . '` DROP `is_gvideo`;');
}

?>