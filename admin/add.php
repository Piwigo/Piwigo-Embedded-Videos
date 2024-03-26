<?php
defined('GVIDEO_PATH') or die('Hacking attempt!');

include_once(GVIDEO_PATH.'include/functions.inc.php');
include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');

if (isset($_POST['add_video']))
{
  $_POST['url'] = trim($_POST['url']);
  $_POST['add_film_frame'] = isset($_POST['add_film_frame']);
  
  if (empty($_POST['category']))
  {
    $page['errors'][] = l10n('Select an album');
  } 
  else if ($_POST['mode'] == 'provider')
  {
    // check inputs
    if (empty($_POST['url']))
    {
      $page['errors'][] = l10n('Please fill the video URL');
    }
    else if ( ($video = parse_video_url($_POST['url'], $safe_mode)) === false )
    {
      $page['errors'][] = l10n('an error happened');
    }
    else
    {
      if ($safe_mode === true)
      {
        $page['warnings'][] = l10n('Unable to contact host server');
        $page['warnings'][] = l10n('Video data like description and thumbnail might be missing');
      }
      
      if ($_POST['size_common'] == 'true')
      {
        $_POST['width'] = $_POST['height'] = '';
      }
      else if (!preg_match('#^([0-9]+)$#', $_POST['width']) or !preg_match('#^([0-9]+)$#', $_POST['height']))
      {
        $page['errors'][] = l10n('Width and height must be integers');
        $_POST['width'] = $_POST['height'] = '';
      }
      if ($_POST['autoplay_common'] == 'true')
      {
        $_POST['autoplay'] = '';
      }
      
      $image_id = add_video($video, $_POST);
    }
  }
  else
  {
    $_POST['embed_code'] = trim($_POST['embed_code']);
    
    if (empty($_POST['embed_code']))
    {
      $page['errors'][] = l10n('Please fill the embed code');
    }
    else
    {
      $image_id = add_video_embed($_POST);
    }
  }
  
  if (isset($image_id))
  {
    if (empty($page['warnings']))
    {
      $query = '
SELECT id, name, permalink
  FROM '.CATEGORIES_TABLE.'
  WHERE id = '.$_POST['category'].'
;';
      $category = pwg_db_fetch_assoc(pwg_query($query));

      $page['infos'][] = l10n(
        'Video successfully added. <a href="%s">View</a>',
        make_picture_url(array(
          'image_id' => $image_id,
          'category' => array(
            'id' => $category['id'],
            'name' => $category['name'],
            'permalink' => $category['permalink'],
            ),
          ))
        );
    }
    unset($_POST);
  }

  if (function_exists('empty_lounge'))
  {
    empty_lounge();
  }
}

if (!isset($_POST['mode']))
{
  $_POST = array(
    'mode' => 'provider',
    'category' => '',
    'title' => '',
    'url' => '',
    'embed_code' => '',
    'add_film_frame' => true,
    'size_common' => 'true',
    'width' => '',
    'height' => '',
    'autoplay_common' => 'true',
    'autoplay' => '0',
    );
}
  
if (!isset($_POST['safe_mode']))
{
  $_POST['safe_mode'] = !test_remote_download();
}

// template
$template->assign(array(
  'gd_available' => function_exists('imagecreatetruecolor'),
  'gvideo' => $conf['gvideo'],
  'POST' => @$_POST,
  'CACHE_KEYS' => get_admin_client_cache_keys(array('categories')),
  ));

$template->set_filename('gvideo_content', realpath(GVIDEO_PATH . 'admin/template/add.tpl'));
