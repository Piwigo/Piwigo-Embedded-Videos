<?php
if (!defined('GVIDEO_PATH')) die('Hacking attempt!');

include_once(GVIDEO_PATH.'include/functions.inc.php');
include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');

if (isset($_POST['add_video']))
{
  // check inputs
  if (empty($_POST['url']))
  {
    array_push($page['errors'], l10n('Please fill the video URL'));
  }
  if ($_POST['name_server'] == 'false' and empty($_POST['name']))
  {
    array_push($page['errors'], l10n('Please fill the video name'));
  }
  if ($_POST['thumbnail_server'] == 'false' and empty($_FILES['thumbnail_src']))
  {
    array_push($page['errors'], l10n('Please add a thumbnail'));
  }
  if ( !empty($_POST['url']) and ($video = parse_video_url($_POST['url'])) === false )
  {
    array_push($page['errors'], l10n('Unable to contact host server'));
  }
  
  if (count($page['errors']) == 0)
  {
    // download thumbnail
    $thumb_name = $video['type'].'-'.$video['id'].'-'.uniqid();
    if ($_POST['thumbnail_server'] == 'true')
    {
      $thumb_name.= '.'.get_extension($video['thumbnail']);
      $thumb_source = $conf['data_location'].$thumb_name;
      if (download_remote_file($video['thumbnail'], $thumb_source) !== true)
      {
        $thumb_source = $conf['data_location'].get_filename_wo_extension($thumb_name).'.jpg';
        copy(GVIDEO_PATH.'mimetypes/'.$video['type'].'.jpg', $thumb_source);
      }
    }
    // upload thumbnail
    else
    {
      if ($_FILES['thumbnail_src']['error'] > 0) 
      {
        array_push($page['errors'], l10n('Unknown upload error'));
      }
      else if ( !in_array($_FILES['thumbnail_src']['type'], array('image/jpeg','image/png','image/gif')) )
      {
        array_push($page['errors'], l10n('Incorrect file type,').' '.sprintf(l10n('Allowed file types: %s.'), 'jpg, png, gif'));
      }
      
      $thumb_name.= '.'.get_extension($_FILES['thumbnail_src']['name']);
      $thumb_source = $_FILES['thumbnail_src']['tmp_name'];
    }
    
    if (count($page['errors']) == 0)
    {
      // add image and update infos
      $image_id = add_uploaded_file($thumb_source, $thumb_name, array($_POST['category']));
      
      if ($_POST['name_server'] == 'true')        $_POST['name'] = $video['title'];
      if ($_POST['description_server'] == 'true') $_POST['description'] = $video['description'];
      if ($_POST['author_server'] == 'true')      $_POST['author'] = $video['author'];
      
      $updates = array(
        'name' => pwg_db_real_escape_string($_POST['name']),
        'comment' => pwg_db_real_escape_string(@$_POST['description']),
        'author' => pwg_db_real_escape_string(@$_POST['author']),
        );
      
      single_update(
        IMAGES_TABLE,
        $updates,
        array('id' => $image_id),
        true
        );
      
      // register video
      if ($_POST['size_common'] == 'true')
      {
        $_POST['width'] = $_POST['height'] = '';
      }
      if ($_POST['autoplay_common'] == 'true')
      {
        $_POST['autoplay'] = '';
      }
      
      $insert = array(
        'picture_id' => $image_id,
        'type' => $video['type'],
        'video_id' => $video['id'],
        'width' => $_POST['width'],
        'height' => $_POST['height'],
        'autoplay' => $_POST['autoplay'],
        );
        
      single_insert(
        GVIDEO_TABLE,
        $insert
        );
        
      array_push($page['infos'], sprintf(
        l10n('Video successfully added. <a href="%s">View</a>'), 
        make_picture_url(array(
          'image_id' => $image_id,
          'category' => array(
            'id' => $_POST['category'],
            'name' => '',
            'permalink' => '',
            ),
          ))
        ));
      unset($_POST);
    }
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
  
// template
$template->assign(array(
  'upload_max_filesize' => $upload_max_filesize,
  'upload_max_filesize_shorthand' => $upload_max_filesize_shorthand,
  'gvideo' => $conf['gvideo'],
  'POST' => @$_POST,
  ));

$template->set_filename('gvideo_content', dirname(__FILE__) . '/template/add.tpl');

?>