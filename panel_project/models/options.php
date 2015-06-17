<?php

function options_page() {
	echo "<div class=\"col-sm-6\"><h2>Admin Settings</h2>";
	if ($_POST['cron_reset']) start_cron_cycle($_POST['cron_freq']);
	if ($_POST['cron_clear']) clear_jobs();
	if ($_POST['cron_run']) perform_actions('cron_cycle');
	echo "<form method=\"POST\" class=\"form-horizontal\">";
	do_settings('ap_core');
	input_text_field('admin_email', 'Admin Email', 'admin@web.com', get_setting('admin_email', 'ap_core'), false, true);
	input_text_field('site_url', 'Site URL', 'https://...',  get_setting('site_url', 'ap_core'), false, true);
	input_checkbox_field('cron_task_run', null, 'Run tasks on cron cycle', get_setting('cron_task_run', 'ap_core'), true, false, true);
	input_checkbox_field('cron_reset', null, 'Initiate/Reset cron cycle', false, true);
	input_checkbox_field('cron_clear', null, 'Clear cron cycle', false, true);
	input_text_field('cron_freq', 'Interval', '5', get_setting('cron_freq', 'ap_core'), false, true);
	input_text_field('cron_php_path', 'Path to PHP', 'php', get_setting('cron_php_path', 'ap_core'), false, true);
	input_checkbox_field('cron_run', null, 'Run Cycle Now', false, true);
	submit_button('save_settings', 'Save Settings', NULL, true, true);
	echo "</form>";
	echo "</div>";
}

add_page('Admin', 'System Configuration', 'options_page');