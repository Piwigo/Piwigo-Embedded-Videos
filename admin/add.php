<?php
if (!defined('GVIDEO_PATH')) die('Hacking attempt!');

include_once(GVIDEO_PATH.'include/functions.inc.php');
include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');

if (isset($_POST['add_video']))
{
  $_POST['url'] = trim($_POST['url']);
  // check inputs
  if (empty($_POST['url']))
  {
    array_push($page['errors'], l10n('Please fill the video URL'));
  }
  else if ( ($video = parse_video_url($_POST['url'], isset($_POST['safe_mode']))) === false )
  {
    if (isset($_POST['safe_mode']))
    {
      array_push($page['errors'], l10n('an error happened'));
    }
    else
    {
      array_push($page['errors'], l10n('Unable to contact host server'));
      array_push($page['errors'], l10n('Try in safe-mode'));
    }
    $_POST['safe_mode'] = true;
  }
  
  if (count($page['errors']) == 0)
  {
    if ($_POST['size_common'] == 'true')
    {
      $_POST['width'] = $_POST['height'] = '';
    }
    else if ( !preg_match('#^([0-9]+)$#', $_POST['width']) or !preg_match('#^([0-9]+)$#', $_POST['height']) )
    {
      array_push($page['errors'], l10n('Width and height must be integers'));
      $_POST['width'] = $_POST['height'] = '';
    }
    if ($_POST['autoplay_common'] == 'true')
    {
      $_POST['autoplay'] = '';
    }
    $_POST['add_film_frame'] = isset($_POST['add_film_frame']);
    
    
    $image_id = add_video($video, $_POST);
    
    
    $query = '
SELECT id, name, permalink
  FROM '.CATEGORIES_TABLE.'
  WHERE id = '.$_POST['category'].'
;';
    $category = pwg_db_fetch_assoc(pwg_query($query));
      
    array_push($page['infos'], sprintf(
      l10n('Video successfully added. <a href="%s">View</a>'), 
      make_picture_url(array(
        'image_id' => $image_id,
        'category' => array(
          'id' => $category['id'],
          'name' => $category['name'],
          'permalink' => $category['permalink'],
          ),
        ))
      ));
    unset($_POST);
  }
}

// categories
$query = '
SELECT id,name,uppercats,global_rank
  FROM '.CATEGORIES_TABLE.'
;';
display_select_cat_wrapper($query, array(), 'category_parent_options');

// upload limit
$upload_max_filesize = min(
  get_ini_size('upload_max_filesize'),
  get_ini_size('post_max_size')
  );
$upload_max_filesize_shorthand = 
  ($upload_max_filesize == get_ini_size('upload_max_filesize')) ?
  get_ini_size('upload_max_filesize', false) :
  get_ini_size('post_max_filesize', false);
  
if (!isset($_POST['safe_mode']))
{
  $_POST['safe_mode'] = !test_remote_download();
}

// template
$template->assign(array(
  'upload_max_filesize' => $upload_max_filesize,
  'upload_max_filesize_shorthand' => $upload_max_filesize_shorthand,
  'gd_available' => function_exists('imagecreatetruecolor'),
  'gvideo' => $conf['gvideo'],
  'POST' => @$_POST,
  ));

$template->set_filename('gvideo_content', dirname(__FILE__) . '/template/add.tpl');

?>