<?php

add_page('Hosted Applications', 'Hosted Applications', 'hosted_apps_page');

$setting_group = 'account_'.get_user()->accountid;
if (has_permission('Administrator') && !empty($_POST['ha_admin_user']) && $_POST['ha_admin_user'] != 'me') $setting_group = 'account_' . $_POST['ha_admin_user'];

register_setting('activate_hostedapps',$setting_group);
register_setting('hosting_sub', $setting_group);
register_setting('domain_name',$setting_group);
register_setting('own_domain', $setting_group);
register_setting('other_comments', $setting_group);
register_setting('hosted_domain', $setting_group);

function ha_users_list() {
	$users = fetchAllUsers();
	$options = array(array("text"=>'Me', "value"=>'me'));
	foreach ($users as $user) {
		$options[] = array("text"=>$user['display_name'], "value"=>$user['accountid']);
	}
	input_select_dropdown('ha_admin_user', $options, $_REQUEST['ha_admin_user'], 'User', true, false, true); 
}

function hosted_apps_page() {
	echo '<div class="col-sm-8">';
	echo '<h2>Hosted Applications</h2>';
	echo '<p>This tool only supports one site at this time. You can still ask for multiple sites! Speak to an account representative at <a href="mailto:customerservice@rmlsoft.com">customerservice@rmlsoft.com</a> to enable more sites.</p>';
	echo "<form method=\"POST\" class=\"form-horizontal\">";
	$setting_group = 'account_'.get_user()->accountid;
	if (has_permission('Administrator') && !empty($_POST['ha_admin_user']) && $_POST['ha_admin_user'] != 'me') $setting_group = 'account_' . $_POST['ha_admin_user'];
	$active = get_setting('activate_hostedapps',$setting_group);
	if ($_POST['activate_hostedapps'] == 1 && (!$active || $active == 0)) {
		$sub = perform_actions('activate_hostedapps',array('hostapps1'));
		$u = get_user();
		$message = "
			Email: {$u->email}<br>
			Domain: {$_POST['domain_name']}<br>
			Owned: {$_POST['own_domain']}<br>
			Generic: {$_POST['hosted_domain']}<br>
			Comments: {$_POST['other_comments']}
		";
		$aemail = get_setting('admin_email', 'ap_core');
		//mail($aemail, 'New Hosted App Request', $message, "From: RML Account Manager <$aemail>\r\n");
		$m = new userCakeMail();
		$m->sendMail($aemail, 'New Hosted App Request', $message);
		alertUser(0, 'The setup team has been notified and will be in contact with you very shortly. Thanks!');
	}
	$posted = isset($_POST['activate_hostedapps']);
	do_settings($setting_group);
	if ($sub) save_setting('hosting_sub', $sub, $setting_group);
	$sub = get_setting('hosting_sub', $setting_group);
	if ($posted && $active == 1) perform_actions('deactivate_hostedapps', array($sub));
	if (has_permission('Administrator')) {
		ha_users_list();
	}
	input_text_field('domain_name', 'Your desired domain', 'www.mysite.com', get_setting('domain_name', $setting_group), false, true);
	input_checkbox_field('own_domain', null, 'I already own this domain', get_setting('own_domain', $setting_group), true, false, true);
	input_text_field('hosted_domain', 'Or use a .hostedapp.us domain', 'mysite', get_setting('hosted_domain', $setting_group), false, true);
	input_text_area('other_comments', 'Other Comments', 'List any other details we need to know, and any information about the domain you already own, if applicable.', get_setting('other_comments', $setting_group), true);
	$activate_box = input_checkbox_field('activate_hostedapps', null, 'Activate Hosted Apps on my account', get_setting('activate_hostedapps',$setting_group), false, false, true);
	ajax('echo apply_filter(\'activate_checkbox\', $activate_box);', NULL, array('activate_box'=>$activate_box));
	submit_button('save_ha_settings', 'Update', null, true, true);
	echo '</form>';
	echo '</div>';
}

function hosted_domain_text( $content ) {
	$content = str_replace('<input class', '<input style="width: 100px; display: inline-block; text-align: right;" class', $content);
	$content = str_replace('</div></div>', '<span style="font-size: 14px;"> .hostedapp.us</span></div></div>', $content);
	return $content;
}
add_filter('input_text_field_hosted_domain', 'hosted_domain_text');