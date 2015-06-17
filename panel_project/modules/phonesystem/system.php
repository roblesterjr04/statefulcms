<?php

chdir('../../');
require_once('models/config.php');
require_once('includes/includes.php');

$call = new PhoneCall($_REQUEST['To'], $_REQUEST['From'], $_REQIEST['Sid']);


header('Content-Type: text/xml');