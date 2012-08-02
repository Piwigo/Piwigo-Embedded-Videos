<?php
if (!defined('GVIDEO_PATH')) die('Hacking attempt!');

if (isset($_POST['save_config']))
{
  $conf['gvideo'] = array(
    'autoplay' => $_POST['autoplay'],
    'width' => $_POST['width'],
    'height' => $_POST['height'],
    'vimeo' => array(
      'title' => (int)isset($_POST['vimeo']['title']),
      'portrait' => (int)isset($_POST['vimeo']['portrait']),
      'byline' => (int)isset($_POST['vimeo']['byline']),
      'color' => $_POST['vimeo']['color'],
      ),
    'dailymotion' => array(
      'logo' => (int)isset($_POST['dailymotion']['logo']),
      'title' => (int)isset($_POST['dailymotion']['title']),
      'color' => $_POST['dailymotion']['color'],
      ),
    'youtube' => array(),
    'wat' => array(),
    'wideo' => array(),
    'videobb' => array(),
    );
      
  conf_update_param('gvideo', serialize($conf['gvideo']));
  array_push($page['infos'], l10n('Information data registered in database'));
}


$template->assign(array(
  'gvideo' => $conf['gvideo'],
  'vimeo_colors' => array('00adef', 'ff9933', 'c9ff23', 'ff0179', 'ffffff'),
  'dailymotion_colors' => array('F7FFFD', 'E02C72', '92ADE0', 'E8D9AC', 'C2E165', '052880', 'FF0000', '834596'),
  ));


$template->set_filename('gvideo_content', dirname(__FILE__) . '/template/config.tpl');

?>