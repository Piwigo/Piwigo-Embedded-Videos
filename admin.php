<?php
if (!defined('GVIDEO_PATH')) die('Hacking attempt!');

global $template, $page, $conf;

$conf['gvideo'] = unserialize($conf['gvideo']);

$page['tab'] = (isset($_GET['tab'])) ? $_GET['tab'] : $page['tab'] = 'add';


if ($page['tab'] != 'photo')
{
  // tabsheet
  include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
  $tabsheet = new tabsheet();
  $tabsheet->add('add', l10n('Add a video'), GVIDEO_ADMIN . '-add');
  $tabsheet->add('config', l10n('Configuration'), GVIDEO_ADMIN . '-config');
  $tabsheet->select($page['tab']);
  $tabsheet->assign();
}

// include page
include(GVIDEO_PATH . 'admin/' . $page['tab'] . '.php');

// template
$template->assign(array(
  'GVIDEO_PATH'=> GVIDEO_PATH,
  'GVIDEO_ABS_PATH'=> dirname(__FILE__).'/',
  'GVIDEO_ADMIN' => GVIDEO_ADMIN,
  ));
  
$template->assign_var_from_handle('ADMIN_CONTENT', 'gvideo_content');

?>