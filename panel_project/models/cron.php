<?php

function register_job($job) {
	//$output = exec('crontab -l');
	$output = explode("\n", $output);
	if (!in_array($job, $output)) {
		$output[] = "MAILTO=\"\"\n".$job;
		$output = implode("\n", $output);
		file_put_contents('jobs.txt', $output);
		return exec('crontab jobs.txt');
	}
}

function clear_jobs() {
	return exec('crontab -r');
}

function start_cron_cycle($min = 5, $hour = '*') {
	$php = get_setting('cron_php_path', 'ap_core');
	if (!$php || $php == '') $php = 'php';
	register_job("*/$min $hour * * * $php " . getcwd() . "/execute_cron.php " . getcwd());
}

function get_jobs() {
	$output = exec('crontab -l');
	$output = explode("\n", $output);
	return $output;
}