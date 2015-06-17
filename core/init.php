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
require_once 'fields.php';

$root = new CP_Foundation();

$root->init();