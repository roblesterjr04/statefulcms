<?php
	
############################################
###### Lets handle our stateful AJAX #######
############################################
	
header('Content-type: text/javascript;');

require_once '../cp-config.php';
require_once '../core/init.php';

//$params = unserialize($_REQUEST['params']);

$object = $_REQUEST['object'];
$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : false;

$callback = $_REQUEST['callback'];

echo "sessionState = '$object';\n";

echo "if (console) console.log('*');\n";

$object = root()->decode($object);

$func = $_REQUEST['callback'] . '_' . $_REQUEST['event'];

// We can't change yet, we need to update the state with our changed value, and once the new state is saved. We can call the change callback.
if ($_REQUEST['event'] == '_change') {
	if (get_class($sender) == 'CP_Editor') {
		echo "var newval = CKEDITOR.instances.$callback.getData();";
	} else {
		echo "var newval = $('*[name=\"$callback\"]').val();\n";
	}
	$sender = $_REQUEST['sender'];
	//echo "console.log('Executing state update: '+newval);\n";
	echo "cp_ajax('$callback', sessionState, '$sender', 'update_state', newval, true);\n\n";
}

// We are being asked to update the state. 
else if ($_REQUEST['event'] == 'update_state') {
	$object->update_control_state($_REQUEST['callback'], $data);
}

else {

	$sender = root()->decode($_REQUEST['sender']);

	if (method_exists($object, $func)) {
		echo "/* $func exists in owner. */\n\n";
		$object->$func($sender, $data);
	}

}