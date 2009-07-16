<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

$params = unserialize($conf['PY_GVideo']);

$idvideo = file_get_contents($picture['current']['path']);
$sp = explode("/", $idvideo);

$template->assign(array(
  'ID_GVIDEO' => $sp[0],
  'H_GVIDEO' => !empty($sp[1]) ? $sp[1] : $params[$extension][0],
  'W_GVIDEO' => !empty($sp[2]) ? $sp[2] : $params[$extension][1],
  'AUTO_GVIDEO' => $params[$extension][2]));

if ($extension == 'gvideo')
{
  $template->assign(array(
    'LANG_GVIDEO' => $params[$extension][3],
    'START_GVIDEO' => !empty($sp[3]) ? "initialTime=" . $sp[3] : ''));
}

// bouton de telechargement
unset($template->smarty->_tpl_vars['current']['U_DOWNLOAD']);
$pi = pathinfo($picture['current']['path']);
$location = get_filename_wo_extension($pi['dirname'] . '/pwg_high/' . $pi['basename']);
foreach(array('.asf', '.wmv', '.divx', '.xvid', '.avi', '.AVI', '.qt', '.mov', '.mpg', '.mpeg', '.mp4', '.flv', '.zip', '.rar') as $high_ext)
{
  if (file_exists($location . $high_ext))
  {
    $template->smarty->_tpl_vars['current']['U_DOWNLOAD'] = $location . $high_ext;
  }
}

$template->set_filenames(array('default_content' => dirname(__FILE__) . '/template/pywaie_' . $extension . '.tpl'));
$content = $template->parse('default_content', true);

?>