<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class gvideo_maintain extends PluginMaintain
{
  private $default_conf = array(
    'autoplay' => 0,
    'width' => 640,
    'height' => 360,
    'sync_description' => 1,
    'sync_tags' => 1,
    'vimeo' => array(
      'title' => 1,
      'portrait' => 1,
      'byline' => 1,
      'color' => '00adef',
      ),
    'dailymotion' => array(
      'logo' => 1,
      'title' => 1,
      'color' => 'F7FFFD',
      ),
    'youtube' => array(),
    'wat' => array(),
    'wideo' => array(),
    );
    
  private $table;
  
  function __construct($plugin_id)
  {
    global $prefixeTable;
    
    parent::__construct($plugin_id);
    $this->table = $prefixeTable . 'image_video';
  }

  function install($plugin_version, &$errors=array())
  {
    global $conf;

    // add config parameter
    if (empty($conf['gvideo']))
    {
      conf_update_param('gvideo', $this->default_conf, true);
    }
    else
    {
      $conf['gvideo'] = safe_unserialize($conf['gvideo']);

      if (!isset($conf['gvideo']['sync_description']))
      {
        $conf['gvideo']['sync_description'] = 1;
        $conf['gvideo']['sync_tags'] = 1;
        
        conf_update_param('gvideo', $conf['gvideo']);
      }
    }

    // create table
  $query = '
CREATE TABLE IF NOT EXISTS `' . $this->table . '` (
  `picture_id` mediumint(8) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `type` varchar(64) NOT NULL,
  `video_id` varchar(128) NOT NULL,
  `width` smallint(9) DEFAULT NULL,
  `height` smallint(9) DEFAULT NULL,
  `autoplay` tinyint(1) DEFAULT NULL,
  `embed` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;';
    pwg_query($query);
    
    // update video_id lenght
    pwg_query('ALTER TABLE `' . $this->table . '` CHANGE `video_id` `video_id` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
    
    // new collumn in images table
    $result = pwg_query('SHOW COLUMNS FROM `' . IMAGES_TABLE . '` LIKE "is_gvideo";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `' . IMAGES_TABLE . '` ADD `is_gvideo` TINYINT(1) NOT NULL DEFAULT 0;');
      
      $query = '
UPDATE `' . IMAGES_TABLE . '`
  SET is_gvideo = 1
  WHERE id IN(
    SELECT picture_id FROM `' . $this->table . '`
    )
;';
      pwg_query($query);
    }
    
    // new column "embed"
    $result = pwg_query('SHOW COLUMNS FROM `' . $this->table . '` LIKE "embed";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `' . $this->table . '` ADD `embed` text DEFAULT NULL;');
    }
    
    // remove old configuration
    if (isset($conf['PY_GVideo']))
    {
      conf_delete_param('PY_GVideo');
    }
  }

  function update($old_version, $new_version, &$errors=array())
  {
    $this->install($new_version, $errors);
  }

  function uninstall()
  {
    conf_delete_param('gvideo');

    pwg_query('DROP TABLE `' . $this->table . '`;');

    pwg_query('ALTER TABLE `'. IMAGES_TABLE .'` DROP `is_gvideo`;');
  }
}
