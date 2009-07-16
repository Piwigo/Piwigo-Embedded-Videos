<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
load_language('plugin.lang', GVIDEO_PATH);

global $template;

// Gestion des onglets
if (!isset($_GET['tab']))
    $page['tab'] = 'add_page';
else
    $page['tab'] = $_GET['tab'];
    
$my_base_url = get_admin_plugin_menu_link(__FILE__);
    
$tabsheet = new tabsheet();
$tabsheet->add('add_page',
               l10n('py_addvideo'),
               $my_base_url.'&amp;tab=add_page');
$tabsheet->add('config',
               l10n('py_conf'),
               $my_base_url.'&amp;tab=config');
$tabsheet->select($page['tab']);
$tabsheet->assign();

include_once ($page['tab'] . '.php');

?>