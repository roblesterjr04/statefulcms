<?php

require_once('model.php');*/

$to = $_REQUEST['roomNum'];
$from = $_REQUEST['fromNum'];
$code = $_REQUEST['code'];

log_end($to, $from, $code);