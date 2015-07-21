<?php
	
############################################
###### Lets handle our stateful AJAX #######
############################################
	
header('Content-type: text/javascript;');

require_once '../cp-config.php';
require_once '../core/init.php';

$state_return = true;

# Collect the data from the state request.

$object_coded = isset($_REQUEST['object']) ? $_REQUEST['object'] : false;
$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : false;
$callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : false;
$event = isset($_REQUEST['event']) ? $_REQUEST['event'] : false;
$sender_in = isset($_REQUEST['sender']) ? $_REQUEST['sender'] : false;

$object = root()->decode($object_coded); # Decode the object.
$sender = root()->decode($sender_in); # Decode the sender.

$slug = $object->_slug;

echo "{$slug}_sessionState = '$object_coded';\n"; # Output the javascript to the browser with the object we are working with. The encoded object.

$func = $callback . '_' . $event; # Define the function we are going to call out of our object.

# We can't change yet, we need to update the state with our changed value, and once the new state is saved. We can call the change callback.
if ($event == '_change' || $event == '_keyup') {
	if (get_class($sender) == 'CP_Editor') {
		echo "var newval = CKEDITOR.instances.$callback.getData();";
	} else if (get_class($sender) == 'CP_Checkbox') {
		echo "var newval = $('*[name=\"$callback\"]').is(':checked') ? 'checked' : 'no';\n";
	} else {
		echo "var newval = $('*[name=\"$callback\"]').val();\n";
	}
	root()->iface->console("Initiating state update...");
	echo "cp_state('$callback', {$slug}_sessionState, '$sender_in', 'update_state', newval, true, '".substr($event,1)."', '$slug');\n\n";
}

# We are being asked to update the state. 
else if ($event == 'update_state') {
	root()->iface->console("Updating the state...");
	$object->controls->$callback->val($data, false);
}

# We don't have anything else do do, so lets just execute the callback function.
else {

	$sender = root()->decode($sender_in);

	if (method_exists($object, $func)) {
		root()->iface->console("Executing event: $event :: Object: $slug :: Control: $callback");
		$object->$func($sender, $data);
	}

}