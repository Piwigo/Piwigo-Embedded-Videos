<?php
// +-----------------------------------------------------------------------+
// | Piwigo - a PHP based photo gallery                                    |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2008-2012 Piwigo Team                  http://piwigo.org |
// | Copyright(C) 2003-2008 PhpWebGallery Team    http://phpwebgallery.net |
// | Copyright(C) 2002-2003 Pierrick LE GALL   http://le-gall.net/pierrick |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

if(!defined("PHPWG_ROOT_PATH")) die ("Hacking attempt!");

include_once(GVIDEO_PATH.'include/functions.inc.php');


// +-----------------------------------------------------------------------+
// | Basic checks                                                          |
// +-----------------------------------------------------------------------+

check_status(ACCESS_ADMINISTRATOR);

check_input_parameter('image_id', $_GET, false, PATTERN_ID);

$admin_photo_base_url = get_root_url().'admin.php?page=photo-'.$_GET['image_id'];
$self_url = GVIDEO_ADMIN.'-photo&amp;image_id='.$_GET['image_id'];

// +-----------------------------------------------------------------------+
// | Tabs                                                                  |
// +-----------------------------------------------------------------------+
include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$tabsheet = new tabsheet();
$tabsheet->set_id('photo');
$tabsheet->select('gvideo');
$tabsheet->assign();

// +-----------------------------------------------------------------------+
// | Picture infos                                                         |
// +-----------------------------------------------------------------------+
global $gvideo;

$query = '
SELECT *
  FROM '.IMAGES_TABLE.'
  WHERE id = '.$_GET['image_id'].'
;';
$picture = pwg_db_fetch_assoc(pwg_query($query));


// +-----------------------------------------------------------------------+
// | Update properties                                                     |
// +-----------------------------------------------------------------------+
if (isset($_POST['save_properties']))
{
  // check inputs
  if (empty($_POST['url']))
  {
    array_push($page['errors'], l10n('Please fill the video URL'));
  }
  if ( !empty($_POST['url']) and ($video = parse_video_url($_POST['url'])) === false )
  {
    array_push($page['errors'], l10n('Unable to contact host server'));
  }
  
  if (count($page['errors']) == 0)
  {
    include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');

    if ( $gvideo['url'] != $video['url'] )
    {
      // download thumbnail
      $thumb_name = $video['type'].'-'.$video['id'].'-'.uniqid().'.'.get_extension($video['thumbnail']);
      $thumb_source = $conf['data_location'].$thumb_name;
      if (download_remote_file($video['thumbnail'], $thumb_source) !== true)
      {
        $thumb_source = $conf['data_location'].get_filename_wo_extension($thumb_name).'.jpg';
        copy(GVIDEO_PATH.'mimetypes/'.$video['type'].'.jpg', $thumb_source);
      }
      
      // add image and update infos
      $image_id = add_uploaded_file($thumb_source, $thumb_name, null, null, $_GET['image_id']);
      
      $updates = array(
        'name' => pwg_db_real_escape_string($video['title']),
        'comment' => pwg_db_real_escape_string($video['description']),
        'author' => pwg_db_real_escape_string($video['author']),
        'is_gvideo' => 1,
        );
      
      single_update(
        IMAGES_TABLE,
        $updates,
        array('id' => $_GET['image_id']),
        true
        );
    }
    
    // register video
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
    
    $updates = array(
      'url' => $video['url'],
      'type' => $video['type'],
      'video_id' => $video['id'],
      'width' => $_POST['width'],
      'height' => $_POST['height'],
      'autoplay' => $_POST['autoplay'],
      );
      
    single_update(
      GVIDEO_TABLE,
      $updates,
      array('picture_id' => $_GET['image_id']),
      true
      );
      
    array_push($page['infos'], l10n('Video successfully updated'));
    $gvideo = array_merge($gvideo, $updates);
  }
}

// +-----------------------------------------------------------------------+
// | Update thumbnail (from Photo Update)                                  |
// +-----------------------------------------------------------------------+
if (isset($_FILES['photo_update']))
{
  include_once(PHPWG_ROOT_PATH.'admin/include/functions_upload.inc.php');
  
  if ($_FILES['photo_update']['error'] !== UPLOAD_ERR_OK)
  {
    array_push($page['errors'],
      file_upload_error_message($_FILES['photo_update']['error'])
      );
  }
  else
  {
    add_uploaded_file(
      $_FILES['photo_update']['tmp_name'],
      $_FILES['photo_update']['name'],
      null,
      null,
      $_GET['image_id']
      );

    array_push($page['infos'], l10n('The thumbnail was updated'));
  }
}

// +-----------------------------------------------------------------------+
// | Add film frame                                                        |
// +-----------------------------------------------------------------------+
if ( function_exists('imagecreatetruecolor') and isset($_GET['add_film_frame']) )
{
  include_once(GVIDEO_PATH . '/include/functions.inc.php');
  include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');
  
  $thumb_source = $conf['data_location'].$picture['file'];
  
  add_film_frame($picture['path'], $thumb_source);
  add_uploaded_file($thumb_source, $picture['file'], null, null, $_GET['image_id']);
  
  redirect($self_url);
}


// +-----------------------------------------------------------------------+
// | Template                                                              |
// +-----------------------------------------------------------------------+
if (empty($gvideo['height']))
{
  $gvideo['size_common'] = 'true';
}
if (empty($gvideo['autoplay']))
{
  $gvideo['autoplay_common'] = 'true';
}

if (function_exists('imagecreatetruecolor'))
{
  $template->assign('U_ADD_FILM_FRAME', $self_url.'&amp;add_film_frame=1');
}

$template->assign(array(
  'F_ACTION' => $self_url,
  'GVIDEO' => $gvideo,
  'TN_SRC' => DerivativeImage::thumb_url($picture).'?'.time(),
  'TITLE' => render_element_name($picture),
));

$template->set_filename('gvideo_content', dirname(__FILE__).'/template/photo.tpl');

?>