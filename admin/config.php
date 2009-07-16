<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Chargement des parametres
$params = unserialize($conf['PY_GVideo']);

// Mise a jour de la base de donnee
if (isset($_POST['submit']))
{
  $params  = array(
    'gvideo' => array(
      $_POST['pywaie_gvideo_h'],
      $_POST['pywaie_gvideo_w'],
      $_POST['pywaie_gvideo_autoplay'],
      $_POST['pywaie_gvideo_hl']),
    'ytube' => array(
      $_POST['pywaie_ytube_h'],
      $_POST['pywaie_ytube_w'],
      $_POST['pywaie_ytube_autoplay']),
    'dm' => array(
      $_POST['pywaie_dm_h'],
      $_POST['pywaie_dm_w'],
      $_POST['pywaie_dm_autoplay']),
    'wideo' => array(
      $_POST['pywaie_wideo_h'],
      $_POST['pywaie_wideo_w'],
      $_POST['pywaie_wideo_autoplay']),
    'vimeo' => array(
      $_POST['pywaie_vimeo_h'],
      $_POST['pywaie_vimeo_w'],
      $_POST['pywaie_vimeo_autoplay'])
    );
  
  $query = '
UPDATE ' . CONFIG_TABLE . '
  SET value="' . addslashes(serialize($params)) . '"
  WHERE param="PY_GVideo"
  LIMIT 1';
  pwg_query($query);
  
  array_push($page['infos'], l10n('py_info4'));
}

$template->assign(array(
  'PYWAIE_GVIDEO_H' => $params['gvideo'][0],
  'PYWAIE_GVIDEO_W' => $params['gvideo'][1],
  'PYWAIE_GVIDEO_HL' => $params['gvideo'][3],
  'PYWAIE_YTUBE_H' => $params['ytube'][0],
  'PYWAIE_YTUBE_W' => $params['ytube'][1],
  'PYWAIE_DM_H' => $params['dm'][0],
  'PYWAIE_DM_W' => $params['dm'][1],
  'PYWAIE_WIDEO_H' => $params['wideo'][0],
  'PYWAIE_WIDEO_W' => $params['wideo'][1],
  'PYWAIE_VIMEO_H' => $params['vimeo'][0],
  'PYWAIE_VIMEO_W' => $params['vimeo'][1]));

if ($params['gvideo'][2] == 'true')
{
  $template->assign(array('PYWAIE_GVIDEO_AUTOPLAY_TRUE' => 'checked="checked"'));
}
else
{
  $template->assign(array('PYWAIE_GVIDEO_AUTOPLAY_FALSE' => 'checked="checked"'));
}
if ($params['ytube'][2] == '1')
{
  $template->assign(array('PYWAIE_YTUBE_AUTOPLAY_TRUE' => 'checked="checked"'));
}
else
{
  $template->assign(array('PYWAIE_YTUBE_AUTOPLAY_FALSE' => 'checked="checked"'));
}
if ($params['dm'][2] == '1')
{
  $template->assign(array('PYWAIE_DM_AUTOPLAY_TRUE' => 'checked="checked"'));
}
else
{
  $template->assign(array('PYWAIE_DM_AUTOPLAY_FALSE' => 'checked="checked"'));
}
if ($params['wideo'][2] == 'true')
{
  $template->assign(array('PYWAIE_WIDEO_AUTOPLAY_TRUE' => 'checked="checked"'));
}
else
{
  $template->assign(array('PYWAIE_WIDEO_AUTOPLAY_FALSE' => 'checked="checked"'));
}
if ($params['vimeo'][2] == '1')
{
  $template->assign(array('PYWAIE_VIMEO_AUTOPLAY_TRUE' => 'checked="checked"'));
}
else
{
  $template->assign(array('PYWAIE_VIMEO_AUTOPLAY_FALSE' => 'checked="checked"'));
}

$template->set_filenames(array('plugin_admin_content' => dirname(__FILE__) . '/config.tpl'));
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>