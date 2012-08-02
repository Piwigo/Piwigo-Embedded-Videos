<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function parse_video_url($source_url)
{
  $source_url = 'http://'.preg_replace('#^http(s?)://#', null, $source_url);
  
  $url = parse_url($source_url);
  $url['host'] = str_replace('www.', null, $url['host']);
  $url['host'] = explode('.', $url['host']);
  
  $video = array();
  switch ($url['host'][0])
  {
    /* youtube */
    case 'youtube':
    {
      $video['type'] = 'youtube';

      parse_str($url['query'], $url['query']);
      if (empty($url['query']['v'])) return false;
      
      $video['id'] = $url['query']['v'];
    }
    
    case 'youtu': // youtu.be (short-url service)
    {
      if (empty($video))
      {
        $video['type'] = 'youtube';
        
        $url['path'] = explode('/', $url['path']);
        $video['id'] = $url['path'][1];
      }
      
      $api_url = 'http://gdata.youtube.com/feeds/api/videos/'.$video['id'].'?v=2&alt=json';
      $json = download_remote_file($api_url, true);
      if ($json === false or $json == 'file_error') return false;
      
      $json = json_decode($json, true);
      $video = array_merge($video, array(
        'title' => $json['entry']['title']['$t'],
        'description' => $json['entry']['media$group']['media$description']['$t'],
        'thumbnail' => $json['entry']['media$group']['media$thumbnail'][2]['url'],
        'author' => $json['entry']['author'][0]['name']['$t'],
        ));
      break;
    }
      
    /* vimeo */
    case 'vimeo':
    {
      $video['type'] = 'vimeo';
      
      $url['path'] = explode('/', $url['path']);
      $video['id'] = $url['path'][1];
      
      $api_url = 'http://vimeo.com/api/v2/video/'.$video['id'].'.json';
      $json = download_remote_file($api_url, true);
      if ($json === false or $json == 'file_error') return false;
      
      $json = json_decode($json, true);
      $video = array_merge($video, array(
        'title' => $json[0]['title'],
        'description' => $json[0]['description'],
        'thumbnail' => $json[0]['thumbnail_large'],
        'author' => $json[0]['user_name'],
        ));
      break;
    }
      
    /* dailymotion */
    case 'dailymotion':
    {
      $video['type'] = 'dailymotion';
      
      $url['path'] = explode('/', $url['path']);
      if ($url['path'][1] != 'video') return false;
      $video['id'] = $url['path'][2];
      
      $api_url = 'https://api.dailymotion.com/video/'.$video['id'].'?fields=description,id,thumbnail_large_url,title,owner.username'; // DM doesn't accept non secure connection
      $json = download_remote_file($api_url, true);
      if ($json === false or $json == 'file_error') return false;
      
      $json = json_decode($json, true);
      $json['thumbnail_large_url'] = preg_replace('#\?([0-9]+)$#', null, $json['thumbnail_large_url']);
      
      $video = array_merge($video, array(
        'id' => $json['id'],
        'title' => $json['title'],
        'description' => $json['description'],
        'thumbnail' => $json['thumbnail_large_url'],
        'author' => $json['owner.username'],
        ));
      break;
    }
      
    /* wat */
    case 'wat':
    {
      $video['type'] = 'wat';
      
      $html = download_remote_file($source_url, true);
      if ($html === false or $html == 'file_error') return false;
      
      preg_match('#<meta property="og:video" content="http://www.wat.tv/swf2/([^"/>]+)" />#', $html, $matches);
      if (empty($matches[1])) return false;
      $video['id'] = $matches[1];
      
      preg_match('#<meta name="name" content="([^">]*)" />#', $html, $matches);
      $video['title'] = $matches[1];
      
      preg_match('#<p class="description"([^>]*)>(.*?)</p>#s', $html, $matches);
      $video['description'] = $matches[2];
      
      preg_match('#<meta property="og:image" content="([^">]+)" />#', $html, $matches);
      $video['thumbnail'] = $matches[1];
      
      $video['author'] = null;
      break;
    }
      
    /* wideo */
    case 'wideo':
    {
      $video['type'] = 'wideo';
      
      $url['path'] = explode('/', $url['path']);
      $video['id'] = rtrim($url['path'][2], '.html');
      
      $html = download_remote_file($source_url, true);
      if ($html === false or $html == 'file_error') return false;
            
      preg_match('#<meta property="og:title" content="([^">]*)" />#', $html, $matches);
      $video['title'] = $matches[1];
      
      preg_match('#<meta property="og:description" content="([^">]*)" />#', $html, $matches);
      $video['description'] = $matches[1];
      
      preg_match('#<meta property="og:image" content="([^">]+)" />#', $html, $matches);
      $video['thumbnail'] = $matches[1];
      
      preg_match('#<li id="li_author">Auteur :  <a href="\#"([^>]*)><span>(.*?)</span></a>#', $html, $matches);
      $video['author'] = $matches[2];
      break;
    }
      
    /* videobb */
    case 'videobb':
    {
      $video['type'] = 'videobb';
      
      if (!empty($url['query']))
      {
        parse_str($url['query'], $url['query']);
        if (empty($url['query']['v'])) return false;
        $video['id'] = $url['query']['v'];
      }
      else
      {
        $url['path'] = explode('/', $url['path']);
        if ($url['path'][1] != 'video') return false;
        $video['id'] = $url['path'][2];
      }
      
      $html = download_remote_file($source_url, true);
      if ($html === false or $html == 'file_error') return false;
            
      preg_match('#<meta content="videobb - ([^">]*)"  name="title" property="" />#', $html, $matches);
      $video['title'] = $matches[1];
      
      $video['description'] = null;
      
      preg_match('#<link rel="image_src" href="([^">]+)" type="image/jpeg" />#', $html, $matches);
      $video['thumbnail'] = $matches[1];
      
      $video['author'] = null;
      break;
    }
      
    default:
      return false;   
  }
  
  return $video;
}

/**
 * download a remote file
 *  - needs cURL or allow_url_fopen
 *  - take care of SSL urls
 *
 * @param: string source url
 * @param: mixed destination file (if true, file content is returned)
 */
if (!function_exists('download_remote_file'))
{
  function download_remote_file($src, $dest)
  {
    if (empty($src))
    {
      return false;
    }
    
    $return = ($dest === true) ? true : false;
    
    /* curl */
    if (function_exists('curl_init'))
    {
      if (!$return)
      {
        $newf = fopen($dest, "wb");
      }
      $ch = curl_init();
      
      curl_setopt($ch, CURLOPT_URL, $src);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-language: en"));
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
      if (strpos($src, 'https://') !== false)
      {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      }
      if (!$return)
      {
        curl_setopt($ch, CURLOPT_FILE, $newf);
      }
      else
      {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      }
      
      if (($out = curl_exec($ch)) === false)
      {
        return 'file_error';
      }
      
      curl_close($ch);
      
      if (!$return)
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
          'header' => "Accept-language: en",
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
}

?>