<?php


chdir($_SERVER['argv'][1]);
//chmod(__FILE__, 0755);
require_once('models/config.php');
require_once('includes/includes.php');
echo 'Starting...';
$run = get_setting('cron_task_run', 'ap_core' );
perform_actions('cron_cycle');
file_put_contents('cron-log.txt', 'Job ran.');