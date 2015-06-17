<?php

$slug = $_REQUEST['mod'];
$key = $_POST['appkey'];
$auth = $_POST['authkey'];
$user = $_POST['user'];
$pass = $_POST['pass'];
require_once("models/config.php");
require_once("includes/includes.php");

if (empty($key)) {
	api_response('Failed 200');
}

if (empty($auth)) {
	if (!isUserLoggedIn()) {
		if (!emailExists($username)) die('Failed 100');
		$userdetails = fetchUserDetails(NULL,NULL,NULL,$username);
		if($userdetails["active"]==0) die('Failed 101');
		$entered_pass = generateHash($pass,$userdetails["password"]);
		if($entered_pass != $userdetails["password"]) die('Failed 102');
		if(!$userdetails['enabled']) die('Failed 103');
		$loggedInUser = new loggedInUser();
		$loggedInUser->email = $userdetails["email"];
		$loggedInUser->user_id = $userdetails["id"];
		$loggedInUser->hash_pw = $userdetails["password"];
		$loggedInUser->title = $userdetails["title"];
		$loggedInUser->displayname = $userdetails["display_name"];
		$loggedInUser->username = $userdetails["user_name"];
		$loggedInUser->accountid = $userdetails["account_number"];
		$loggedInUser->permissions = $userdetails["permissions"];
		$loggedInUser->profile_image = $userdetails["profile_image"];
		
		//Update last sign in
		$loggedInUser->updateLastSignIn();
		$_SESSION["userCakeUser"] = $loggedInUser;
	}
}