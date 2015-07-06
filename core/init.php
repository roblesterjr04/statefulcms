<?php

session_start();

global $root;

require_once 'foundation.php';

require_once 'hooks.php';
require_once 'database.php';
require_once 'settings.php';
require_once 'themes.php';
require_once 'objects.php';
require_once 'authenticate.php';
require_once 'interface.php';
require_once 'controls.php';
require_once 'plugins.php';
require_once __DIR__ . '/../update/update.php';


$root = new CP_Foundation();

// Initialize the foundation. Lets get building...
$root->init();