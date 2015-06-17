<?php

require_once(realpath(dirname(__FILE__)) . '/twilio/Services/Twilio.php');

$AccountSid = "ACd13c9b7c0fec6081107fa7eae61441fb";
$AuthToken = "6dd9cc1e844f1dfec72e6187cf92b219";

$client = new Services_Twilio($AccountSid, $AuthToken);
//session_save_path('./');
session_start();
$_SESSION['client_obj'] = $client;

//if (isset($_REQUEST['To'])) require_once('../../models/db-settings.php');

function get_plans($id = null) {
	$plans = array(
		"0"=>array("id"=>1,"name"=>"Basic - $9.99/month","codes"=>3,"max"=>5,"slug"=>"basic","mins"=>120),
		"1"=>array("id"=>2,"name"=>"Standard - $19.99/month","codes"=>10,"max"=>10,"slug"=>"standard","mins"=>180),
		"2"=>array("id"=>3,"name"=>"Pro - $39.99/month","codes"=>20,"max"=>20,"slug"=>"pro","mins"=>500),
		"3"=>array("id"=>4,"name"=>"Exec - $49.99/month","codes"=>30,"max"=>40,"slug"=>"exec","mins"=>500)
	);
	if ($id && !is_string($id)) return $plans[$id - 1];
	elseif ($id && is_string($id)) {
		foreach ($plans as $plan) {
			if ($plan['slug'] == $id) return $plan;
		}
	}
	else return $plans;
}

function get_participants($name) {
	$cinst = $_SESSION['client_obj'];
	$callers = 0;
	$id = '';
	foreach ($cinst->account->conferences as $conference) {
	    if ($conference->friendly_name == $name && $conference->status == 'in-progress') {
	    	$id = $conference->sid;
	    }
	}
	if ($id != '') {
		foreach ($cinst->account->conferences->get($id)->participants as $partic) {
			$callers++;
		}
	}
	return $callers;
}

function get_hold_music($number) {
	global $mysqli;
	$group = get_group($number);
	$stmt = $mysqli->prepare(
		"SELECT setting_value from ap_settings where setting_name='hold_music' and setting_group = '$group'"
	);
	$stmt->execute();
	$stmt->bind_result($hm);
	while($stmt->fetch()) {
		return $hm;
	}
	$stmt->close();
}

function log_start($to, $from, $code) {
	global $mysqli;
	$stmt = $mysqli->prepare(
		"INSERT into ap_conf_log (to_num, from_num, code_used, time_stamp, action) values('$to','$from','$code',NOW(),'start')"
	);
	$stmt->execute();
	$stmt->close();
}

function log_end($to, $from, $code) {
	
	global $mysqli;
	$stmt = $mysqli->prepare(
		"INSERT into ap_conf_log (to_num, from_num, code_used, time_stamp, action) values('$to','$from','$code',NOW(),'end')"
	);
	$stmt->execute();
	$stmt->close();
}

function validate_code($code, $number) {
	if (strlen($code) < 6) return false;
	$group = get_group($number);
	global $mysqli;
	$stmt = $mysqli->prepare(
		"SELECT setting_value from ap_settings where setting_name = 'conf_plans' and setting_group = '$group'"
	);
	$stmt->execute();
	$result = NULL;
	$stmt->bind_result($result);
	$code = substr_replace($code, '-', 3, 0);
	while($stmt->fetch()) {
		$plans = json_decode($result);
		foreach ($plans as $plan) {
			foreach ($plan->numbers as $plan_number) {
				if ($plan_number->phone_number == $number) {
					return in_array($code, $plan->codes);
				}
			}
		}
	}
	return false;
}

function get_max_parts($number) {
	$group = get_group($number);
	global $mysqli;
	$stmt = $mysqli->prepare(
		"SELECT setting_value from ap_settings where setting_name = 'conf_plans' and setting_group = '$group'"
	);
	$stmt->execute();
	$result = NULL;
	$stmt->bind_result($result);
	while($stmt->fetch()) {
		$plans = json_decode($result);
		foreach ($plans as $plan) {
			foreach ($plan->numbers as $plan_number) {
				if ($plan_number->phone_number == $number) {
					$plan = get_plans($plan->plan);
					return $plan['max'];
				}
			}
		}
	}
	$stmt->close();
	return 0;
}

function get_plan( $number ) {
	$group = get_group($number);
	global $mysqli;
	$stmt = $mysqli->prepare(
		"SELECT setting_value from ap_settings where setting_name = 'conf_plans' and setting_group = '$group'"
	);
	$stmt->execute();
	$result = NULL;
	$stmt->bind_result($result);
	while($stmt->fetch()) {
		$plans = json_decode($result);
		foreach ($plans as $plan) {
			foreach ($plan->numbers as $plan_number) {
				if ($plan_number->phone_number == $number) {
					$plan = get_plans($plan->slug);
					return $plan;
				}
			}
		}
	}
	$stmt->close();
	return false;
}

function get_group($number) {
	global $mysqli;
	$stmt = $mysqli->prepare(
		"SELECT setting_group from ap_settings where setting_value like '%$number%'"
	);
	$stmt->execute();
	$result = NULL;
	$stmt->bind_result($result);
	while($stmt->fetch()) {
		return $result;
	}
	$stmt->close();
	return $result;
}

function get_numbers($plan, $area) {
	$verify = apply_filter('verify_before_purchase', true);
	global $loggedInUser;
	if ($verify) {
		if (strlen($area) < 3) return false; 
		switch ($plan) {
			case 1:
			case 2:
				$num = get_local($area);
				if (!$num) return false;
				break;
			case 3:
			case 4:
				$num = get_local($area);
				$tf = get_tollfree();
				if (!$num) return false;
				if (!$tf) return false;
				break;
		}
		$asid = perform_actions('get_user_meta', array($loggedInUser->user_id));
		//if (!$asid) $asid = create_account(fetchUserDetails(null,null,$loggedInUser->user_id));
		$numbers = array();
		$url = "http://" . $_SERVER['HTTP_HOST'] . substr(dirname(__FILE__), strpos(dirname(__FILE__), "/accounts")) . "/connection.php";
		if (isset($num)) {
			buy_number($num->phone_number, $asid, $url);
			$numbers[] = $num;
		}
		if (isset($tf)) {
			buy_number($tf->phone_number, $asid, $url);
			$numbers[] = $tf;
		}
	} else {
		return false;
	}
	return $numbers;
}

function create_account($user) {
	$name = $user['account_number'];
	$client = $_SESSION['client_obj'];
	$account = $client->accounts->create(array(
		"FriendlyName" => $name
	));
	$asid = $account->sid;
	$args = array("key"=>"AccountSid","value"=>$asid,"id"=>$user['id']);
	perform_actions('save_meta', array($args));
	return $user;
	//return $account->sid;
}
add_action('user_registered', 'create_account');

function new_sub_user($user) {
	$name = $user['account_number'];
	$client = $_SESSION['client_obj'];
	$account = null;
	foreach ($client->accounts->getIterator(0, 50, array("FriendlyName" => $name)) as $ac) {
		$account = $ac;
	}
	if ($account) {
		$asid = $account->sid;
		$args = array("key"=>"AccountSid","value"=>$asid,"id"=>$user['id']);
		perform_actions('save_meta', array($args));
		return $user;
	}
	return null;
}
add_action('subuser_added', 'new_sub_user');

function buy_number($PhoneNumber, $AccountId, $url) {
	$client = $_SESSION['client_obj'];
	$account = $client->accounts->get($AccountId);
	try {
		$number = $account->incoming_phone_numbers->create(array(
			'PhoneNumber' => $PhoneNumber,
			'VoiceUrl' => $url,
			'StatusCallback' => $url
		));
	} catch (Exception $e) {
		$err = urlencode("Error purchasing number: {$e->getMessage()}");
		die($err);
	}
	
}

function rel_number($PhoneNumber) {
	$client = $_SESSION['client_obj'];
	$sid = '';
	foreach ($client->account->incoming_phone_numbers->getIterator(0, 50, array(
			"PhoneNumber" => $PhoneNumber
		)) as $number
	) {
		$sid = $number->sid;
	}
	$number = $client->account->incoming_phone_numbers->delete($sid);
}


function generate_codes($count) {
	$codes = array();
	for ($i = 0; $i < $count; $i++) {
		$codes[] = strval(mt_rand(100,999)) . '-' . strval(mt_rand(100,999));
	}
	return $codes;
}

function get_local($area) {
	$client = $_SESSION['client_obj'];
	$SearchParams = array();
	$SearchParams['AreaCode'] = $area;
	$numbers = $client->account->available_phone_numbers->getList('US', 'Local', $SearchParams);
	if (count($numbers->available_phone_numbers) == 0) return false;
	return $numbers->available_phone_numbers[0];
}

function get_tollfree() {
	$client = $_SESSION['client_obj'];
	$numbers = $client->account->available_phone_numbers->getList('US', 'TollFree', array());
	if (count($numbers->available_phone_numbers) == 0) return false;
	return $numbers->available_phone_numbers[0];
}

function get_usage($account, $start = NULL, $end = NULL) {
	if (!$start) $start = date('Y-m-1');
	if (!$end) $end = date('Y-m-d H:i:s');
	global $mysqli;
	$account = str_replace('cus_', '', $account);
	$stmt = $mysqli->prepare("SELECT summary, sum(message) from ap_activity_log where summary like '%$account' and datestamp between ? and ? group by summary");
	$stmt->bind_param("ss", $start, $end);
	$stmt->execute();
	$stmt->bind_result($number, $minutes);
	$rows = array();
	while($stmt->fetch()) {
		$parts = explode('|', $number);
		$number = $parts[0];
		$plan = $parts[1];
		$rows[] = array("number"=>$number, "minutes"=>$minutes, "plan"=>$plan);
	}
	$stmt->close();
	return $rows;
}

function get_billable_minutes($plan, $account_number = NULL) {
	global $loggedInUser;
	if (!$account_number) $account_number = $loggedInUser->accountid;
	return 1000;
}







