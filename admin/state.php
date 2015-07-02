<?php
	
############################################
###### Lets handle our stateful AJAX #######
############################################
	
header('Content-type: text/javascript;');

require_once '../cp-config.php';
require_once '../core/init.php';

$state_return = true;

//$params = unserialize($_REQUEST['params']);

$object = isset($_REQUEST['object']) ? $_REQUEST['object'] : false;
$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : false;

$callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : false;

$event = isset($_REQUEST['event']) ? $_REQUEST['event'] : false;

echo "sessionState = '$object';\n";

echo "if (console) console.log('*');\n";

$object = root()->decode($object);

$func = $callback . '_' . $event;

$sender_in = isset($_REQUEST['sender']) ? $_REQUEST['sender'] : false;

$sender = root()->decode($sender_in);

// We can't change yet, we need to update the state with our changed value, and once the new state is saved. We can call the change callback.
if ($event == '_change' || $event == '_keyup') {
	if (get_class($sender) == 'CP_Editor') {
		echo "var newval = CKEDITOR.instances.$callback.getData();";
	} else if (get_class($sender) == 'CP_Checkbox') {
		echo "var newval = $('*[name=\"$callback\"]').is(':checked') ? 'checked' : 'no';\n";
	} else {
		echo "var newval = $('*[name=\"$callback\"]').val();\n";
	}
	//echo "console.log('Executing state update: '+newval);\n";
	echo "cp_state('$callback', sessionState, '$sender_in', 'update_state', newval, true, '".substr($event,1)."');\n\n";
}

// We are being asked to update the state. 
else if ($event == 'update_state') {
	$object->update_control_state($callback, $data);
}

else {

	$sender = root()->decode($sender_in);

	if (method_exists($object, $func)) {
		echo "/* $func exists in owner. */\n\n";
		$object->$func($sender, $data);
	}

}