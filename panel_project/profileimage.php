<?php

require_once("models/config.php");
require_once("includes/includes.php");

header("Content-type: image/png");

if (isset($_GET['email'])) {
	$user = fetchUserDetails(null,null,null,$_GET['email']);
	echo strval($user['profile_image']);
	exit();
}

global $loggedInUser;
echo $loggedInUser->profile_image;