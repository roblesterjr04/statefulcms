<?php

//require_once('twilio/Services/Twilio.php');
require_once('class.device.php');
require_once('class.callsteps.php');

$phone_table = new Table('ap_phone_devices');
$users_table = new Table('ap_phone_users');
$hunts_table = new Table('ap_phone_hunts');
$numbers_table = new Table('ap_phone_numbers');

$phone_table->createWithFields(array(
	array("name"=>"id", "type"=>"bigint", "attr"=>"NOT NULL AUTO_INCREMENT"),
	array("name"=>"name", "type"=>"char(50)", "attr"=>"NOT NULL"),
	array("name"=>"account", "type"=>"char(50)", "attr"=>"NOT NULL"),
	array("name"=>"user_id", "type"=>"int", "attr"=>""),
	array("name"=>"identifier", "type"=>"char(50)", "attr"=>"NOT NULL, PRIMARY KEY (id)")
));

$users_table->createWithFields(array(
	array("name"=>"id", "type"=>"bigint", "attr"=>"NOT NULL AUTO_INCREMENT"),
	array("name"=>"user_id", "type"=>"int", "attr"=>"NOT NULL"),
	array("name"=>"ext", "type"=>"bigint", "attr"=>""),
	array("name"=>"dir", "type"=>"int", "attr"=>", PRIMARY KEY (id)")
));

$hunts_table->createWithFields(array(
	array("name"=>"id", "type"=>"bigint", "attr"=>"NOT NULL AUTO_INCREMENT"),
	array("name"=>"name", "type"=>"char(50)", "attr"=>"NOT NULL"),
	array("name"=>"account", "type"=>"char(50)", "attr"=>"NOT NULL"),
	array("name"=>"ext", "type"=>"bigint", "attr"=>""),
	array("name"=>"dir", "type"=>"int", "attr"=>", PRIMARY KEY (id)")
));

$numbers_table->createWithFields(array(
	array("name"=>"id", "type"=>"bigint", "attr"=>"NOT NULL AUTO_INCREMENT"),
	array("name"=>"account", "type"=>"char(50)", "attr"=>"NOT NULL"),
	array("name"=>"sid", "type"=>"char(100)", "attr"=>"NOT NULL"),
	array("name"=>"pnumber", "type"=>"char(15)", "attr"=>"NOT NULL, PRIMARY KEY (id)")
));

function phone_system() {
	$ar = new AudioResponse();
	$ar->panelFace();
	$tr = new TransferCall();
	
}

function get_ps_numbers() {
	global $mysqli;
	global $loggedInUser;
	$numbers = array();
	$accountid = $loggedInUser->accountid;
	if ($mysqli) {
		$stmt = $mysqli->prepare("SELECT id, account, sid, pnumber from ap_phone_numbers where account=?");
		$stmt->bind_param("s",$accountid);
		$stmt->execute();
		$stmt->bind_result($id, $account, $sid, $number);
		while($stmt->fetch()) {
			$num = new PhoneNumber();
			$num = $num->existing($id, $account, $sid, $number);
			$numbers[] = $num;
		}
		$stmt->close();
	}
	if (count($numbers) > 0) return $numbers;
	else return false;
}

function get_ps_users() {
	global $mysqli;
	global $loggedInUser;
	$users = array();
	$accountid = $loggedInUser->accountid;
	if ($mysqli) {
		$stmt = $mysqli->prepare("SELECT au.id, u.display_name from ap_phone_users au join uc_users u on u.id = au.user_id and u.account_number = ?");
		$stmt->bind_param("s",$accountid);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while($stmt->fetch()) {
			$user = new PhoneSystemUser($id, $name);
			$users[] = $user;
		}
		$stmt->close();
	}
	if (count($users) > 0) return $users;
	else return false;
}

function phone_numbers_panel($wrap = false) {
	if (!is_array($wrap) && $wrap) {
		echo '<div class="panel panel-default">';
		echo '<div class="panel-heading"><h3 class="panel-title">Numbers</h3></div>';
		echo '<div class="panel-body">';
	}
	$numbers = get_ps_numbers();
	if ($numbers) {
	echo '<table class="table">';
	foreach ($numbers as $number) {
		echo '<tr>';
		echo '<td>' . $number->number . '</td><td><a href="#">Delete</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	} else {
	echo '<p>No numbers</p>';
	}
	if (!is_array($wrap) && $wrap) {
		echo '</div>';
		echo '</div>';
	}
}
add_dash_panel('Phone Numbers', 'phone_numbers_panel', 6, 'Administrator');

function phone_users_panel($wrap = false) {
	if (!is_array($wrap) && $wrap) {
		echo '<div class="panel panel-default">';
		echo '<div class="panel-heading"><h3 class="panel-title">Users</h3></div>';
		echo '<div class="panel-body">';
	}
	$users = get_ps_users();
	if ($users) {
	echo '<table class="table">';
	foreach ($users as $user) {
		echo '<tr>';
		echo '<td>' . $user->name . '</td><td><a href="#">Delete</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	} else {
	echo '<p>No Users</p>';
	}
	if (!is_array($wrap) && $wrap) {
		echo '</div>';
		echo '</div>';
	}
}
add_dash_panel('Phone Users', 'phone_users_panel', 6, 'Administrator');

function phone_system_config() {
	?>
		<div class="col-md-12">
			<h2>Phone System Settings</h2>
			<div class="row">
				<div class="col-md-6">
					<?php
						phone_numbers_panel(true);
					?>
				</div>
				<div class="col-md-6">
					<?php
						phone_users_panel(true);
					?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<?php
						
					?>
				</div>
				<div class="col-md-6">
					<?php
						
					?>
				</div>
			</div>
		</div>
	<?php
}

add_page('Controller', 'Phone System', 'phone_system', 'Administrator');
add_page('Settings', 'Settings', 'phone_system_config', 'Administrator', 'phone_system');

function ps_create_account($user) {
	$name = 'PS_' . $user['account_number'];
	$client = $_SESSION['client_obj'];
	$account = $client->accounts->create(array(
		"FriendlyName" => $name
	));
	$asid = $account->sid;
	$args = array("key"=>"PS_AccountSid","value"=>$asid,"id"=>$user['id']);
	perform_actions('save_meta', array($args));
	return $user;
	//return $account->sid;
}
add_action('user_registered', 'ps_create_account');

function ps_new_sub_user($user) {
	$name = 'PS_' . $user['account_number'];
	$client = $_SESSION['client_obj'];
	$account = null;
	foreach ($client->accounts->getIterator(0, 50, array("FriendlyName" => $name)) as $ac) {
		$account = $ac;
	}
	if ($account) {
		$asid = $account->sid;
		$args = array("key"=>"PS_AccountSid","value"=>$asid,"id"=>$user['id']);
		perform_actions('save_meta', array($args));
		return $user;
	}
	return null;
}
add_action('subuser_added', 'ps_new_sub_user');