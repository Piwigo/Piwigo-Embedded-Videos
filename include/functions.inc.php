<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

function parse_video_url($source_url, &$safe_mode=false)
{
  $source_url = 'http://'.preg_replace('#^http(s?)://#', '', trim($source_url));
  
  $url = parse_url($source_url);
  $url['host'] = str_replace('www.', '', $url['host']);
  $url['host'] = explode('.', $url['host']);
  
  $video = array(
    'type' => null,
    'video_id' => null,
    'url' => null,
    'title' => null,
    'description' => null,
    'thumbnail' => null,
    'author' => null,
    'tags' => null,
  );
  
  switch ($url['host'][0])
  {
    /* youtube 
     * API v2 is closed
     * API v3 requires authentication
     * we use oEmbed API which does not contain description and tags
     */
    case 'youtube':
    {
      parse_str($url['query'], $url['query']);
      if (empty($url['query']['v'])) return false;
      
      $video['video_id'] = $url['query']['v'];
    }
    
    case 'youtu': // youtu.be (short-url service)
    {
      $video['type'] = 'youtube';
      
      if (empty($video['video_id']))
      {
        if ($url['host'][1] != 'be') return false;
        $url['path'] = explode('/', $url['path']);
        $video['video_id'] = $url['path'][1];
      }
      
      $video['url'] = 'https://youtube.com/watch?v='.$video['video_id'];
      $video['title'] = 'YouTube #'.$video['video_id'];
      
      $api_url = 'https://www.youtube.com/oembed?url='.$video['url'].'&format=json';
      $json = gvideo_download_remote_file($api_url, true);
      
      if ($json===false || $json=='file_error')
      {
        $safe_mode = true;
      }
      else
      {
        if (strip_tags($json) == 'Not Found') return false; // unknown video
        if (strip_tags($json) == 'Unauthorized') return false; // private video
        
        $json = json_decode($json, true);
        $video = array_merge($video, array(
          'title' => $json['title'],
          'thumbnail' => $json['thumbnail_url'],
          'author' => $json['author_name'],
          ));
      }
      
      break;
    }
      
    /* vimeo */
    case 'vimeo':
    {
      $video['type'] = 'vimeo';
      
      $url['path'] = explode('/', $url['path']);
      $video['video_id'] = $url['path'][1];
      
      $video['url'] = 'http://vimeo.com/'.$video['video_id'];
      $video['title'] = 'Vimeo #'.$video['video_id'];
      
      // simple API for public videos
      $api_url_1 = 'http://vimeo.com/api/v2/video/'.$video['video_id'].'.json';
      $json = gvideo_download_remote_file($api_url_1, true);
      
      if ($json===false || $json=='file_error')
      {
        $safe_mode = true;
      }
      else
      {
        if (trim($json)!=$video['video_id'].' not found.')
        {
          $json = json_decode($json, true);
          $video = array_merge($video, array(
            'title' => $json[0]['title'],
            'description' => $json[0]['description'],
            'thumbnail' => $json[0]['thumbnail_large'],
            'author' => $json[0]['user_name'],
            'tags' => $json[0]['tags'],
            ));
        }
        else
        {
          // oEmbed API, for private videos, doesn't return keywords
          $api_url_2 = 'http://vimeo.com/api/oembed.json?url='.rawurlencode($video['url']);
          $json = gvideo_download_remote_file($api_url_2, true);
          
          if ($json===false || $json=='file_error')
          {
            $safe_mode = true;
          }
          else
          {
            $json = json_decode($json, true);
            $video = array_merge($video, array(
              'title' => $json['title'],
              'description' => $json['description'],
              'thumbnail' => $json['thumbnail_url'],
              'author' => $json['author_name'],
              ));
          }
        }
        
        // default thumbnail has no extension
        if ($video['thumbnail'] == 'http://i.vimeocdn.com/video/default_640')
        {
          $video['thumbnail'] = 'http://i.vimeocdn.com/video/default_640.jpg';
        }
      }
      
      break;
    }
      
    /* dailymotion */
    case 'dailymotion':
    {
      $url['path'] = explode('/', $url['path']);
      if ($url['path'][1] != 'video') return false;
      $video['video_id'] = $url['path'][2];
    }
    
    case 'dai':  // dai.ly (short-url service)
    {
      $video['type'] = 'dailymotion';
      
      if (empty($video['video_id']))
      {
        if ($url['host'][1] != 'ly') return false;
        $video['video_id'] = ltrim($url['path'], '/');
      }
      
      $video['url'] = 'http://dailymotion.com/video/'.$video['video_id'];
      $video['title'] = 'Dailymotion #'.$video['video_id'];
      
      $api_url = 'https://api.dailymotion.com/video/'.$video['video_id'].'?fields=description,thumbnail_large_url,title,owner.username,tags'; // DM doesn't accept non secure connection
      $json = gvideo_download_remote_file($api_url, true);
        
      if ($json===false || $json=='file_error')
      {
        $safe_mode = true;
      }
      else
      {
        $json = json_decode($json, true);
        
        if (@$json['error']['type'] == 'access_forbidden') return false; // private video
        
        $video = array_merge($video, array(
          'title' => $json['title'],
          'description' => $json['description'],
          'thumbnail' => preg_replace('#\?([0-9]+)$#', null, $json['thumbnail_large_url']),
          'author' => $json['owner.username'],
          'tags' => implode(',', $json['tags']),
          ));
      }
      
      break;
    }
      
    /* wat */
    // URL doesn't contain ID... require to contact host
    case 'wat':
    {
      $html = gvideo_download_remote_file($source_url, true);

      if ($json===false || $json=='file_error')
      {
        return false;
      }
      
      $video['type'] = 'wat';
      $video['url'] = $source_url;
      
      preg_match('#<meta name="twitter:player" content="https://www.wat.tv/embedframe/([^">]+)">#', $html, $matches);
      if (empty($matches[1])) return false;
      $video['video_id'] = $matches[1];

      preg_match('#<meta property="og:title" content="([^">]*)">#', $html, $matches);
      $video['title'] = $matches[1];
      
      preg_match('#<meta property="og:description" content="([^">]*)">#s', $html, $matches);
      $video['description'] = $matches[1];
      
      preg_match('#<meta property="og:image" content="([^">]+)">#', $html, $matches);
      $video['thumbnail'] = $matches[1];
      
      preg_match('#<meta property="video:director" content="http://www.wat.tv/([^">]+)">#', $html, $matches);
      $video['author'] = $matches[1];
      
      preg_match_all('#<meta property="video:tag" content="([^">]+)">#', $html, $matches);
      $video['tags'] = implode(',',  $matches[1]);
      
      break;
    }
      
    /* wideo */
    case 'wideo':
    {
      $video['type'] = 'wideo';
      
      $url['path'] = explode('/', $url['path']);
      $video['video_id'] = rtrim($url['path'][2], '.html');
      
      $video['url'] = 'http://wideo.fr/video/'.$video['video_id'].'.html';
      $video['title'] = 'Wideo #'.$video['video_id'];
      
      $html = gvideo_download_remote_file($source_url, true);
        
      if ($json===false || $json=='file_error')
      {
        $safe_mode = true;
      }
      else
      {
        preg_match('#<meta property="og:title" content="([^">]*)" />#', $html, $matches);
        $video['title'] = $matches[1];
        
        preg_match('#<meta property="og:description" content="([^">]*)" />#s', $html, $matches);
        $video['description'] = $matches[1];
        
        preg_match('#<meta property="og:image" content="([^">]+)" />#', $html, $matches);
        $video['thumbnail'] = $matches[1];
        
        preg_match('#<li id="li_author">Auteur :  <a href=(?:[^>]*)><span>(.*?)</span></a>#', $html, $matches);
        $video['author'] = $matches[1];
        
        preg_match('#<meta name="keywords" content="([^">]+)" />#', $html, $matches);
        $video['tags'] = $matches[1];
      }
      
      break;
    }

    default:
      return false;   
  }
  
  return $video;
}

/**
 * @params:
 *  $video (from parse_video_url)
 *  $config :
 *    - category, integer
 *    - add_film_frame, boolean
 *    - sync_description, boolean
 *    - sync_tags, boolean
 *    - with, integer
 *    - height, integer
 *    - autoplay, integer (0-1)
 */
function add_video($video, $config)
{
  global $page, $conf;
  
  $query = '
SELECT picture_id
  FROM '.GVIDEO_TABLE.'
  WHERE type = "'.$video['type'].'"
    AND video_id = "'.$video['video_id'].'"
;';
  $result = pwg_query($query);
  
  if (pwg_db_num_rows($result))
  {
    $page['warnings'][] = l10n('This video was already registered');
    list($image_id) = pwg_db_fetch_row($result);
    return $image_id;
  }
  
  $thumb = gvideo_get_thumbnail($video);
  
  if ($config['add_film_frame'])
  {
    add_film_frame($thumb['source']);
  }
  
  // add image and update infos
  $image_id = add_uploaded_file($thumb['source'], $thumb['name'], array($config['category']));
  
  $updates = array(
    'name' => pwg_db_real_escape_string($video['title']),
    'author' => pwg_db_real_escape_string($video['author']),
    'is_gvideo' => 1,
    );
    
  if ($config['sync_description'] and !empty($video['description']))
  {
    $updates['comment'] = pwg_db_real_escape_string($video['description']);
  }
  
  if ($config['sync_tags'] and !empty($video['tags']))
  {
    $tags = pwg_db_real_escape_string($video['tags']);
    set_tags(get_tag_ids($tags), $image_id);
  }
  
  single_update(
    IMAGES_TABLE,
    $updates,
    array('id' => $image_id),
    true
    );
  
  // register video
  if (!preg_match('#^([0-9]*)$#', $config['width']) or !preg_match('#^([0-9]*)$#', $config['height']))
  {
    $config['width'] = $config['height'] = '';
  }
  if ($config['autoplay']!='0' and $config['autoplay']!='1')
  {
    $config['autoplay'] = '';
  }
  
  $insert = array(
    'picture_id' => $image_id,
    'url' => $video['url'],
    'type' => $video['type'],
    'video_id' => $video['video_id'],
    'width' => $config['width'],
    'height' => $config['height'],
    'autoplay' => $config['autoplay'],
    );
    
  single_insert(
    GVIDEO_TABLE,
    $insert
    );
    
  return $image_id;
}

function gvideo_get_thumbnail($video)
{
  global $conf;

  include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');

  // download thumbnail
  $thumb = array();
  $thumb['ext'] = (empty($video['thumbnail']) or $video['type'] == 'vimeo') ? 'jpg' : get_extension($video['thumbnail']);
  $thumb['name'] = $video['type'].'-'.$video['video_id'].'-'.uniqid().'.'.$thumb['ext'];
  $thumb['source'] = $conf['data_location'].$thumb['name'];

  if (empty($video['thumbnail']) or gvideo_download_remote_file($video['thumbnail'], $thumb['source']) !== true)
  {
    $thumb['source'] = $conf['data_location'].get_filename_wo_extension($thumb['name']).'.jpg';
    copy(GVIDEO_PATH.'mimetypes/'.$video['type'].'.jpg', $thumb['source']);
  }

  return $thumb;
}

/**
 * @params:
 *  $config :
 *    - url, string
 *    - category, integer
 *    - add_film_frame, boolean
 *    - title, string
 *    - embed_code, string
 */
function add_video_embed($config)
{
  global $page, $conf;
  
  $query = '
SELECT picture_id
  FROM '.GVIDEO_TABLE.'
  WHERE url = "'.$config['url'].'"
;';
  $result = pwg_query($query);
  
  if (pwg_db_num_rows($result))
  {
    $page['warnings'][] = l10n('This video was already registered');
    list($image_id) = pwg_db_fetch_row($result);
    return $image_id;
  }
  
  include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');
  
  // upload thumbnail
  if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK)
  {
    $source_filepath = $_FILES['thumbnail_file']['tmp_name'];
    list(,, $type) = getimagesize($source_filepath);
    
    if (IMAGETYPE_PNG == $type || IMAGETYPE_GIF == $type || IMAGETYPE_JPEG == $type)
    {
      $thumb_name = $_FILES['thumbnail_file']['name'];
      $thumb_source = $conf['data_location'].$thumb_name;
      move_uploaded_file($source_filepath, $thumb_source);
    }
  }
  
  if (!isset($thumb_source))
  {
    $thumb_name = 'embed-'.uniqid().'.jpg';
    $thumb_source = $conf['data_location'].$thumb_name;
    copy(GVIDEO_PATH.'mimetypes/any.jpg', $thumb_source);
  }
  
  if ($config['add_film_frame'])
  {
    add_film_frame($thumb_source);
  }
  
  // add image and update infos
  $image_id = add_uploaded_file($thumb_source, $thumb_name, array($config['category']));
  
  if (empty($config['title']))
  {
    $config['title'] = get_filename_wo_extension($thumb_name);
  }
  
  $updates = array(
    'name' => pwg_db_real_escape_string($config['title']),
    'is_gvideo' => 1,
    );
  
  single_update(
    IMAGES_TABLE,
    $updates,
    array('id' => $image_id),
    true
    );

  $insert = array(
    'picture_id' => $image_id,
    'url' => $config['url'],
    'type' => 'embed',
    'video_id' => 'embed',
    'width' => '',
    'height' => '',
    'autoplay' => '',
    'embed' => $config['embed_code']
    );

  single_insert(
    GVIDEO_TABLE,
    $insert
    );
    
  return $image_id;
}

/**
 * test if a download method is available
 * @return: bool
 */
if (!function_exists('test_remote_download'))
{
  function test_remote_download()
  {
    return function_exists('curl_init') || ini_get('allow_url_fopen');
  }
}

/**
 * download a remote file
 *  - needs cURL or allow_url_fopen
 *  - take care of SSL urls
 *
 * @param: string source url
 * @param: mixed destination file (if true, file content is returned)
 */
function gvideo_download_remote_file($src, $dest, $headers=array())
{
  if (empty($src))
  {
    return false;
  }
  
  $return = ($dest === true) ? true : false;
  
  $headers[] = 'Accept-language: en';
  
  /* curl */
  if (function_exists('curl_init'))
  {
    if (!$return)
    {
      $newf = fopen($dest, "wb");
    }
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $src);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if (!ini_get('safe_mode'))
    {
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    }
    if (strpos($src, 'https://') !== false)
    {
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    if (!$return)
    {
      curl_setopt($ch, CURLOPT_FILE, $newf);
    }
    else
    {
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }
    
    $out = curl_exec($ch);
    curl_close($ch);
    
    if ($out === false)
    {
      return 'file_error';
    }
    else if (!$return)
    {
      fclose($newf);
      return true;
    }
    else
    {
      return $out;
    }
  }
  /* file get content */
  else if (ini_get('allow_url_fopen'))
  {
    if (strpos($src, 'https://') !== false and !extension_loaded('openssl'))
    {
      return false;
    }
    
    $opts = array(
      'http' => array(
        'method' => "GET",
        'user_agent' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
        'header' => implode("\r\n", $headers),
      )
    );

    $context = stream_context_create($opts);
    
    if (($file = file_get_contents($src, false, $context)) === false)
    {
      return 'file_error';
    }
    
    if (!$return)
    {
      file_put_contents($dest, $file);
      return true;
    }
    else
    {
      return $file;
    }
  }
  
  return false;
}

/**
 * and film frame to an image (need GD library)
 * @param: string source
 * @param: string destination (if null, the source is modified)
 * @return: void
 */
function add_film_frame($src, $dest=null)
{
  if (empty($dest))
  {
    $dest = $src;
  }
  
  // we need gd library
  if (!function_exists('imagecreatetruecolor'))
  {
    if ($dest != $src) copy($src, $dest);
    return;
  }
  
  // open source image
  switch (strtolower(get_extension($src)))
  {
    case 'jpg':
    case 'jpeg':
      $srcImage = imagecreatefromjpeg($src);
      break;
    case 'png':
      $srcImage = imagecreatefrompng($src);
      break;
    case 'gif':
      $srcImage = imagecreatefromgif($src);
      break;
    default:
      if ($dest != $src) copy($src, $dest);
      return;
  }

  // Crop if too large
  $srcWidth = imagesx($srcImage);
  $srcHeight = imagesy($srcImage);
  if (($srcWidth/$srcHeight) > (15/12))
  {
    $newWidth = round($srcHeight*15/12);
    $x_start = round(($srcWidth - $newWidth) / 2);
    $srcImage = imagecrop($srcImage, array('x'=>$x_start,'y'=>0,'width'=>$newWidth,'height'=>$srcHeight));
  }
  
  // source properties
  $srcWidth = imagesx($srcImage);
  $srcHeight = imagesy($srcImage);
  $const = intval($srcWidth * 0.04);
  $bandRadius = floor($const/8);

  // band properties
  $imgBand = imagecreatetruecolor($srcWidth + 6*$const, $srcHeight + 3*$const);
  
  $black = imagecolorallocate($imgBand, 0, 0, 0);
  $white = imagecolorallocate($imgBand, 245, 245, 245);
  
  // and dots
  $y_start = intval(($srcHeight + 3*$const) / 2);
  $aug = intval($y_start / 5) + 1;
  $i = 0;

  while ($y_start + $i*$aug < $srcHeight + 3*$const)
  {
    imagefilledroundrectangle($imgBand, (3/4)*$const, $y_start + $i*$aug - $const/2, (9/4)*$const - 1, $y_start + $i*$aug + $const/2 - 1, $white, $bandRadius);
    imagefilledroundrectangle($imgBand, (3/4)*$const, $y_start - $i*$aug - $const/2, (9/4)*$const - 1, $y_start - $i*$aug + $const/2 - 1, $white, $bandRadius);

    imagefilledroundrectangle($imgBand, $srcWidth + (15/4)*$const, $y_start + $i*$aug - $const/2, $srcWidth + (21/4)*$const - 1, $y_start + $i*$aug + $const/2 - 1, $white, $bandRadius);
    imagefilledroundrectangle($imgBand, $srcWidth + (15/4)*$const, $y_start - $i*$aug - $const/2, $srcWidth + (21/4)*$const - 1, $y_start - $i*$aug + $const/2 - 1, $white, $bandRadius);

    ++$i;
  }

  // add source to band
  imagecopy($imgBand, $srcImage, 3*$const, (3/2)*$const, 0, 0, $srcWidth, $srcHeight);
  
  // save image
  switch (strtolower(get_extension($dest)))
  {
    case 'jpg':
    case 'jpeg':
      imagejpeg($imgBand, $dest, 85);
      break;
    case 'png':
      imagepng($imgBand, $dest);
      break;
    case 'gif':
      imagegif($imgBand, $dest);
      break;
  }
}

/**
 * create a rectangle with round corners
 * http://www.php.net/manual/fr/function.imagefilledrectangle.php#42815
 */
function imagefilledroundrectangle(&$img, $x1, $y1, $x2, $y2, $color, $radius)
{
  $x1 = intval($x1);
  $x2 = intval($x2);

  imagefilledrectangle($img, $x1+$radius, $y1, $x2-$radius, $y2, $color);
  
  if ($radius > 0)
  {
    imagefilledrectangle($img, $x1, $y1+$radius, $x2, $y2-$radius, $color);
    imagefilledellipse($img, $x1+$radius, $y1+$radius, $radius*2, $radius*2, $color);
    imagefilledellipse($img, $x2-$radius, $y1+$radius, $radius*2, $radius*2, $color);
    imagefilledellipse($img, $x1+$radius, $y2-$radius, $radius*2, $radius*2, $color);
    imagefilledellipse($img, $x2-$radius, $y2-$radius, $radius*2, $radius*2, $color);
  }
}
