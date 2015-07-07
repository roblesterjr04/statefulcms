<?
	
header('Content-type: text/html;');

require_once '../cp-config.php';
require_once '../core/init.php';

$object = isset($_REQUEST['object']) ? $_REQUEST['object'] : false;
$callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : false;
$event = isset($_REQUEST['event']) ? $_REQUEST['event'] : false;

$object = root()->decode($object);

$func = $event;

if (method_exists($object, $func)) {
	$object->$func();
}