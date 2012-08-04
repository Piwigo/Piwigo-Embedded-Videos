<?php
if (!defined('GVIDEO_PATH')) die('Hacking attempt!');

/**
 * replace content on picture page
 */
function gvideo_element_content($content, $element_info)
{
  global $page, $picture, $template, $conf;
  
  $query = '
SELECT *
  FROM '.GVIDEO_TABLE.'
  WHERE picture_id = '.$element_info['id'].'
;';
  $result = pwg_query($query);
  
  if (!pwg_db_num_rows($result))
  {
    return $content;
  }
  
  remove_event_handler('render_element_content', 'default_picture_content', EVENT_HANDLER_PRIORITY_NEUTRAL);
  
  $conf['gvideo'] = unserialize($conf['gvideo']);
  
  $video = pwg_db_fetch_assoc($result);  
  if (empty($video['width']))
  {
    $video['width'] = $conf['gvideo']['width'];
    $video['height'] = $conf['gvideo']['height'];
  }
  if (empty($video['autoplay']))
  {
    $video['autoplay'] = $conf['gvideo']['autoplay'];
  }
  
  $video['config'] = $conf['gvideo'];
  if ($video['type'] == 'dailymotion')
  {
    $colors = array(
      'F7FFFD' => 'foreground=%23F7FFFD&amp;highlight=%23FFC300&amp;background=%23171D1B',
      'E02C72' => 'foreground=%23E02C72&amp;highlight=%23BF4B78&amp;background=%23260F18',
      '92ADE0' => 'foreground=%2392ADE0&amp;highlight=%23A2ACBF&amp;background=%23202226',
      'E8D9AC' => 'foreground=%23E8D9AC&amp;highlight=%23FFF6D9&amp;background=%23493D27',
      'C2E165' => 'foreground=%23C2E165&amp;highlight=%23809443&amp;background=%23232912',
      '052880' => 'foreground=%23FF0099&amp;highlight=%23C9A1FF&amp;background=%23052880',
      'FF0000' => 'foreground=%23FF0000&amp;highlight=%23FFFFFF&amp;background=%23000000',
      '834596' => 'foreground=%23834596&amp;highlight=%23CFCFCF&amp;background=%23000000',
      );
    $video['config']['dailymotion']['color'] = $colors[ $video['config']['dailymotion']['color'] ];
  }
  $template->assign('GVIDEO', $video);

  $template->set_filename('gvideo_content', dirname(__FILE__).'/../template/video_'.$video['type'].'.tpl');
  return $template->parse('gvideo_content', true);
}

/**
 * clean table at element deletion
 */
function gvideo_delete_elements($ids)
{
  $query = '
DELETE FROM '.GVIDEO_TABLE.'
  WHERE picture_id IN ('.implode(',', $ids).')
;';
  pwg_query($query);
}

/**
 * add message and link on edition page
 */
function gvideo_photo_edit()
{
  global $page, $template, $conf;
  
  if ($page['page'] != 'photo') return;
  
  $query = '
SELECT *
  FROM '.GVIDEO_TABLE.'
  WHERE picture_id = '.$_GET['image_id'].'
;';
  $result = pwg_query($query);
  
  if (!pwg_db_num_rows($result)) return;
    
  $video = pwg_db_fetch_assoc($result);
    
  array_push($page['warnings'], l10n('This element is a video added with "Embedded Video"'));
  
  if (function_exists('imagecreatetruecolor'))
  {
    if (isset($_GET['add_film_frame']))
    {
      include_once(GVIDEO_PATH . '/include/functions.inc.php');
      include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');
      
      $image_infos = pwg_db_fetch_assoc(pwg_query('SELECT path, file FROM '.IMAGES_TABLE.' WHERE id = '.$_GET['image_id'].';'));
      $thumb_source = $conf['data_location'].$image_infos['file'];
      
      add_film_frame($image_infos['path'], $thumb_source);
      add_uploaded_file($thumb_source, $image_infos['file'], null, null, $_GET['image_id']);
      $template->assign('TN_UPDATE', true);
    }
    
    $admin_photo_base_url = get_root_url().'admin.php?page=photo-'.$_GET['image_id'];
    $admin_url_start = $admin_photo_base_url.'-properties';
    $admin_url_start.= isset($_GET['cat_id']) ? '&amp;cat_id='.$_GET['cat_id'] : '';
    
    $template->assign('U_ADD_FILM_FRAME', $admin_url_start.'&amp;add_film_frame=1');
    $template->set_prefilter('picture_modify', 'gvideo_photo_edit_prefilter');
  }
}

function gvideo_photo_edit_prefilter($content)
{
  $search[0] = '{if !url_is_remote($PATH)}';
  $replace[0] = '<li><a href="{$U_ADD_FILM_FRAME}" id="refresh_video">{\'Add film effect\'|@translate}</a></li>'.$search[0];
  
  $search[1] = '{$TN_SRC}';
  $replace[1] = $search[1].'{if $TN_UPDATE}?'.time().'{/if}';
  
  return str_replace($search, $replace, $content);
}

?>