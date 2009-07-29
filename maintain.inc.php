<?php

function plugin_install()
{
  $params = array(
    'gvideo' => array('480','640','true','en'),
    'ytube' => array('480','640','1'),
    'dm' =>  array('480','640','1'),
    'wideo' => array('480', '640', 'true'),
    'vimeo' => array('480', '640', '1'),
    'wat' => array('480', '640', 'true'),
    );

	$q = 'REPLACE INTO ' . CONFIG_TABLE . ' (param,value,comment)
VALUES ("PY_GVideo","' . addslashes(serialize($params)) . '" , "PY Gvideo plugin parameters");';
  pwg_query($q);
}

// La table de config se met à jour automatiquemlent lors de l'activation
function plugin_activate()
{
  global $conf;

  // Suppression des anciens paramètres (version < 1.7.o)
  if (isset($conf['pywaie_gvideo']))
  {
    pwg_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE param LIKE "pywaie_%";');
  }
  
  // Vérification des nouveaux paramètres
  if (!isset($conf['PY_GVideo'])
    or ($params = unserialize($conf['PY_GVideo']) and !isset($params['wat'])))
  {
    plugin_install();
  }
}

function plugin_uninstall()
{
  global $conf;
  
  // Suppression des anciens paramètres (version < 1.7.o)
  if (isset($conf['pywaie_gvideo']))
  {
    pwg_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE param LIKE "pywaie_%";');
  }

  pwg_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE param = "PY_GVideo";');
}

?>