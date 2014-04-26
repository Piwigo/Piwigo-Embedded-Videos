<?php
defined('GVIDEO_PATH') or die('Hacking attempt!');

/**
 * some stuff at the begining of picture.php
 */
function gvideo_prepare_picture($picture)
{
  if ($picture['current']['is_gvideo'])
  {
    // remove default parser
    remove_event_handler('render_element_content', 'default_picture_content', EVENT_HANDLER_PRIORITY_NEUTRAL);
    
    // add custom parser
    add_event_handler('render_element_content', 'gvideo_element_content', EVENT_HANDLER_PRIORITY_NEUTRAL-10, 2);

    if (defined('ADMINTOOLS_ID'))
    {
      global $template;

      load_language('plugin.lang', GVIDEO_PATH);

      $template->assign(array(
        'GVIDEO_PATH' => GVIDEO_PATH,
        'U_GVIDEO_EDIT' => get_root_url().GVIDEO_ADMIN.'-photo&amp;image_id='.$picture['current']['id'],
        ));

      $template->set_prefilter('ato_public_controller', 'gvideo_admintools');
    }
  }
  
  return $picture;
}

function gvideo_admintools($content)
{
  $search = '{if isset($ato.U_DELETE)}';
  $replace = '
{if isset($U_GVIDEO_EDIT)}
  {combine_css path=$GVIDEO_PATH|cat:\'template/fontello/css/gvideo.css\'}
  <li><a class="icon-gvideo-movie" href="{$U_GVIDEO_EDIT}">{\'Video properties\'|translate}</a></li>
{/if}
{if isset($ato.U_DELETE)}';

  return str_replace($search, $replace, $content);
}

/**
 * replace content on picture page
 */
function gvideo_element_content($content, $element_info)
{
  if (!$element_info['is_gvideo'])
  {
    return $content;
  }
  
  global $page, $picture, $template, $conf;
  
  // remove some actions
  $template->assign('U_SLIDESHOW_START', null);
  $template->assign('U_METADATA', null);
  $template->append('current', array('U_DOWNLOAD' => null), true);
  
  $query = '
SELECT *
  FROM '.GVIDEO_TABLE.'
  WHERE picture_id = '.$element_info['id'].'
;';
  $video = pwg_db_fetch_assoc(pwg_query($query));
  
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
  
  global $user;
  // hide stripped overlay preventing to click on video object
  if (strpos('stripped', $user['theme']) !== false)
  {
    $template->block_html_style(null, '.hideTabs{ display:none !important; }');
  }

  if ($video['type'] == 'embed')
  {
    return $video['embed'];
  }
  else
  {
    $template->set_filename('gvideo_content', realpath(GVIDEO_PATH . 'template/video_'.$video['type'].'.tpl'));
    return $template->parse('gvideo_content', true);
  }
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
 * add a prefilter to the Batch Downloader
 */
function gvideo_add_prefilter($prefilters)
{
	$prefilters[] = array(
    'ID' => 'gvideo',
    'NAME' => l10n('Videos'),
  );
  
	return $prefilters;
}

/**
 * perform added prefilter
 */
function gvideo_apply_prefilter($filter_sets, $prefilter)
{
  if ($prefilter == 'gvideo')
  {
    $query = 'SELECT picture_id FROM '.GVIDEO_TABLE.';';
    $filter_sets[] = query2array($query, null, 'picture_id');
  }
  
	return $filter_sets;
}
