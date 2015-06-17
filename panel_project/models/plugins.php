<?php

require_once("models/config.php");

function plugin_activate($active, $id) {
	$output = '<form method="POST">';
	if ($active) {
		$output .= '<input type="hidden" value="'.$id.'" name="Deactivate" /><input type="submit" value="Deactivate" name="action" />';
	} else {
		$output .= '<input type="hidden" value="'.$id.'" name="Activate" /><input type="submit" value="Activate" name="action" />';
	}
	$output .= '</form>';
	return $output;
}

function plugin_title($name) {
	global $module_plugins;
	return $module_plugins[$name]->name;
}

function plugin_descr($name) {
	global $module_plugins;
	return $module_plugins[$name]->description;
}

function plugin_page() {
	$errors = array();
	$successes = array();
	
	$plugin = $_POST[$_POST['action']];
	$action = $_POST['action'];
	
	if ($action == 'Activate') {
		activate_plugin($plugin);
	} else if ($action == 'Deactivate') {
		deactivate_plugin($plugin);
	}
	
	$isadmin = has_permission('Administrator');
	
	echo "<div class=\"col-sm-12\"><h2>Plugins</h2>";
	echo resultBlock($errors,$successes);
	echo get_data_table('ap_plugins', 
		'custom',
		array(
			array('title'=>'Plugin', 'ui'=>'plugin_title', 'params'=>array('plugin_name')),
			array('title'=>'Description', 'ui'=>'plugin_descr', 'params'=>array('plugin_name')),
			array('title'=>'Activate', 'ui'=>'plugin_activate', 'params'=>array('plugin_active', 'plugin_name'))
		)
	);
}

add_page('Plugins', 'Plugins', 'plugin_page');

