<?php

add_page('Wordpress Blogs', '<strong>FREE</strong> Wordpress Blogs', 'wordpress_page');
add_dash_panel('Wordpress Blogs', 'blog_panel');

$setting_group = 'account_'.get_user()->accountid;
register_setting('wp_user', $setting_group);
register_setting('my_sites', $setting_group);

function wordpress_page() {
	$setting_group = 'account_'.get_user()->accountid;
	$errors = array();
	echo '<div class="col-sm-8">';
	echo '<h2>Free Blog Hosting</h2>';
	echo '<p>Welcome! Below you will find your complimentary hosted blogs. Feel free to have as many as you want!</p>';
	echo '<p>Fill out the form below, then check your email!</p>';
	echo '<form method="POST" class="form-horizontal" id="blog-entry">';
	if (isset($_POST['api_call'])) {
		if (empty($_POST['wp_user']) && !get_setting('wp_user', $setting_group)) $errors[] = 'You must enter a username';
		if (empty($_POST['title'])) $errors[] = 'You must give the blog a title';
		if (empty($_POST['slug'])) $errors[] = 'You must give the blog a shortname';
		if (count($errors) == 0) {
			add_wordpress($_POST, json_decode(get_setting('my_sites', $setting_group)));
			do_settings($setting_group);
		} else {
			processErrors($errors);
		}
	}
	input_text_field('title', 'Blog Title', 'My Awesome Blog', '', false, true);
	input_text_field('slug', 'Shortname', 'mysite', '', false, true);
	if (!get_setting('wp_user', $setting_group)) {
		input_text_field('wp_user', 'Username for Blogs', 'mybloguser', '', false, true);
		echo static_field('You will use this username to log in to your blogs');
	} else {
		echo static_field(get_setting('wp_user', $setting_group), 'Username for Blogs');
	}
	submit_button('api_call', 'Add Blog!', null, true, true);
	
	echo '</form>';
	echo '<h3>My Blogs</h3>';
	ajax('sites_table();', 'Getting your blogs...');
	echo '<p>If you would like to request a plugin or theme to be available for your blog, please email <a href="mailto:support@rmlsoft.com">support@rmlsoft.com</a>.</p>';
	echo '</div>';
}

function processErrors($errors) {
	foreach($errors as $error) {
		alertUser(1, $error);
	}
}

function blog_panel() {
	if (has_permission('Administrator')) ajax('sites_table(true);', 'Getting your blogs...');
	else ajax('sites_table();', 'Getting your blogs...');
}

function sites_table($all = false) {
	$sites = get_wordpress_sites();
	$setting_group = 'account_'.get_user()->accountid;
	$existing = json_decode(get_setting('my_sites', $setting_group));
	$thesites = array();
	foreach ($sites as $site) {
		if (in_array($site->path, $existing) || $all) {
			$thesites[] = $site;
		}
	}
	if (count($thesites) > 0) {
		echo '<table class="table">';
		foreach ($thesites as $site) {
			echo '<tr><td>' . $site->blogname . '</td><td><a href="' . $site->siteurl . '" target="_blank">Visit Site</a></td><td><a href="' . $site->siteurl . '/wp-admin" target="_blank">Manage</a></td></tr>';
		}
		echo '</table>';
	} else {
		echo '<p>You do not have any blogs yet. Create one!</p>';
	}
	echo '<p>To remove a blog, please email <a href="mailto:customerservice@rmlsoft.com">customerservice@rmlsoft.com</a>.</p>';
}

function get_wordpress_sites() {
	$ch = curl_init();
	$setting_group = 'account_'.get_user()->accountid;
	
	global $loggedInUser;
	
	$fields = array();
	
	$fields['get_sites'] = 'get';
	
	$url = 'http://wordpress.hostedapp.us';
	
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
	$fields_string = rtrim($fields_string, '&');
	
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, true);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	//execute post
	$result = curl_exec($ch);
	
	$re_array = json_decode($result);
	
	
	
	//close connection
	curl_close($ch);
	
	return $re_array;
}

function add_wordpress($fields, $sites) {
	$ch = curl_init();
	$setting_group = 'account_'.get_user()->accountid;
	
	global $loggedInUser;
	
	
	
	$fields['path'] = '/' . $fields['slug'] . '/';
	$fields['domain'] = 'wordpress.hostedapp.us';
	$fields['email'] = $loggedInUser->email;
	$fields['user'] = ($fields['wp_user'] ? get_setting('wp_user', $setting_group) : get_setting('wp_user', $setting_group));
	
	$sites[] = $fields['path'];
	
	$url = 'http://wordpress.hostedapp.us';
	
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
	$fields_string = rtrim($fields_string, '&');
	
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, true);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	//execute post
	$result = curl_exec($ch);
	
	if ($result == 'success') {
		alertUser(0, 'Wordpress Site Added' . ($_POST['wp_user'] ? 'You will receive an email with the password for '.$_POST['wp_user'] : 'You will receive an activation email. Your password has not changed. Disregard the new password it gives you.'));
		save_setting('my_sites', json_encode($sites), $setting_group);
	}
	else {
		alertUser(1, 'Wordpress Site Failed');
	}
	//close connection
	curl_close($ch);
}