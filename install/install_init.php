<?php

session_start();

global $root;

require_once __DIR__ . '/../core/foundation.php';
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/hooks.php';
require_once __DIR__ . '/../core/settings.php';
require_once __DIR__ . '/../core/objects.php';
require_once __DIR__ . '/../core/authenticate.php';

$root = new CP_Foundation('install');

// Initialize the foundation. Lets get building...
$root->init();