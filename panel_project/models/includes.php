<?php

require_once("menu.php");
require_once("scripts.php");
require_once("tables.php");
require_once("modules.php");
require_once("cron.php");
require_once("api-func.php");

load_plugins();
perform_actions('init');

//Register core settings
register_setting('cron_task_run', 'ap_core');
register_setting('cron_php_path', 'ap_core');
register_setting('cron_freq', 'ap_core');
register_setting('site_url', 'ap_core');
register_setting('admin_email', 'ap_core');

define('BASE_URL', get_setting('site_url','ap_core'));
$c = $_SERVER['REQUEST_URI'];
$c = explode('?', $c);
define('CURRENT', $c[0]);

