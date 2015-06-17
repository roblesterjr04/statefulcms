<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$userId = $_GET['id'];

//Check if selected user exists
if(!userIdExists($userId)){
	header("Location: admin_users.php"); die();
}

$userdetails = fetchUserDetails(NULL, NULL, $userId); //Fetch user details
$isadmin = has_permission('Administrator');
if ($userdetails['account_number'] != $loggedInUser->accountid && !$isadmin) {
	logActivity('unauthorized activity', 'User ' . $loggedInUser->user_id . ' attempted to access the details of user ' . $userdetails['id']);
	die('You do not have permission to do this. This action has been logged and the systems administrator has been notified.');
}

$state = isset($_POST['enabled']) ? 1 : 0;
echo $state;
if($state != $userdetails['enabled']) {
	$state_str = $state == 1 ? 'enabled' : 'disabled';
	setUserEnabled($userdetails['id'], $state);
	$successes[] = $userdetails['display_name'] . "'s account has been $state_str.";
} 

//Forms posted
if(!empty($_POST))
{	
	//Delete selected account
	if(!empty($_POST['delete'])){
		$deletions = $_POST['delete'];
		if ($deletion_count = deleteUsers($deletions)) {
			$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");
		}
	}
	else
	{
		//Update display name
		if ($userdetails['display_name'] != $_POST['display']){
			$displayname = trim($_POST['display']);
			
			//Validate display name
			if(displayNameExists($displayname))
			{
				$errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			}
			elseif(minMaxRange(5,25,$displayname))
			{
				$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
			}
			elseif(!ctype_alnum($displayname)){
				$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
			}
			else {
				if (updateDisplayName($userId, $displayname)){
					$successes[] = lang("ACCOUNT_DISPLAYNAME_UPDATED", array($displayname));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
			
		}
		else {
			$displayname = $userdetails['display_name'];
		}
		
		
		//Activate account
		if(isset($_POST['activate']) && $_POST['activate'] == "activate"){
			if (setUserActive($userdetails['activation_token'])){
				$successes[] = lang("ACCOUNT_MANUALLY_ACTIVATED", array($displayname));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Update email
		if ($userdetails['email'] != $_POST['email']){
			$email = trim($_POST["email"]);
			
			//Validate email
			if(!isValidEmail($email))
			{
				$errors[] = lang("ACCOUNT_INVALID_EMAIL");
			}
			elseif(emailExists($email))
			{
				$errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
			}
			else {
				if (updateEmail($userId, $email)){
					$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Update title
		if ($userdetails['title'] != $_POST['title']){
			$title = trim($_POST['title']);
			
			//Validate title
			if(minMaxRange(1,50,$title))
			{
				$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
			}
			else {
				if (updateTitle($userId, $title)){
					$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($displayname, $title));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Remove permission level
		if(!empty($_POST['removePermission'])){
			$remove = $_POST['removePermission'];
			if ($deletion_count = removePermission($remove, $userId)){
				$successes[] = lang("ACCOUNT_PERMISSION_REMOVED", array ($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		if(!empty($_POST['addPermission'])){
			$add = $_POST['addPermission'];
			if ($addition_count = addPermission($add, $userId)){
				$successes[] = lang("ACCOUNT_PERMISSION_ADDED", array ($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		$userdetails = fetchUserDetails(NULL, NULL, $userId);
	}
}

$userPermission = fetchUserPermissions($userId);
$permissionData = fetchAllPermissions();

require_once("includes/includes.php");

include 'themes/default/header.php';

$output = '';

$output .= "
<div class=\"col-md-12\">
<h2>" . apply_filter('user_display_name', $userdetails['display_name']) . "</h2>";

$output .= resultBlock($errors,$successes, false);

$output .= "
<form class='form-horizontal' name='adminUser' action='".$_SERVER['PHP_SELF']."?id=".$userId."' method='post'>
<div class=\"row\">
<div class=\"col-md-6\">
<h3>User Information</h3>";
if ($isadmin)
$output .= static_field($userdetails['id'], 'ID:');
$output .= static_field($userdetails['user_name'], 'Username:');
$output .= input_text_field('display','Display Name:','Display Name',$userdetails['display_name']);
$output .= input_text_field('email','Email:','Email Address',$userdetails['email']);
//Display activation link, if account inactive
if ($userdetails['active'] == '1'){
	$output .= static_field('Yes', 'Active:');	
}
else{
	$output .= static_field('No', 'Active:');
	$output .= input_checkbox_field('activate', 'activate', 'Activate:');
}

$output .= input_text_field('title','Title:','User Title',$userdetails['title']);
/*<div class=\"form-group\">
<label class=\"col-md-5 control-label\">Title:</label>
<div class=\"col-md-7\"><input class='form-control' type='text' name='title' value='".$userdetails['title']."' /></div>
</div>*/
$output .= static_field(date("j M, Y", $userdetails['sign_up_stamp']),'Sign Up:');

//Last sign in, interpretation
if ($userdetails['last_sign_in_stamp'] == '0'){
	$output .= static_field('Never','Last Sign In:');
}
else {
	$output .= static_field(date("j M, Y", $userdetails['last_sign_in_stamp']),'Last Sign In:');
}

$output .= "</div>";
$output .= "<div class=\"col-md-6\">";
$output .= "<h3>Permission Membership</h3>";
if ($isadmin) {

	$output .= "<h4>Remove Permission:</h4>";
	
	//List of permission levels user is apart of
	foreach ($permissionData as $v1) {
		if(isset($userPermission[$v1['id']])){
			$output .= apply_filter('user_remove_permissions',input_checkbox_field('removePermission['.$v1['id'].']',$v1['id'],$v1['name']));
		}
	}
	
	//List of permission levels user is not apart of
	$output .= "<h4>Add Permission:</h4>";
	foreach ($permissionData as $v1) {
		if(!isset($userPermission[$v1['id']])){
			$output .= apply_filter('user_add_permissions',input_checkbox_field('addPermission['.$v1['id'].']',$v1['id'],$v1['name']));
		}
	}

}
if ($userdetails['user_name'] != $loggedInUser->username) {
$output .= input_checkbox_field('enabled',$userdetails['enabled'],'Enabled',$userdetails['enabled'] == 1);
$output .= input_checkbox_field('delete['.$userdetails['id'].']',$userdetails['id'],'Delete');
}

$output .= "</div>
</div>
<input type='submit' value='Update' class='btn btn-primary' />";
$output .= "
</form>
</div>";
echo apply_filter('admin_user', $output);
perform_actions('admin_user', array($userdetails));
include 'themes/default/footer.php';


?>
