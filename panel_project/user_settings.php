<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he is not logged in
if(!isUserLoggedIn()) { header("Location: login.php"); die(); }

if(!empty($_POST))
{
	$errors = array();
	$successes = array();
	$password = $_POST["password"];
	$password_new = $_POST["passwordc"];
	$password_confirm = $_POST["passwordcheck"];
	
	$errors = array();
	$email = $_POST["email"];
	
	//Perform some validation
	//Feel free to edit / change as required
	
	//Confirm the hashes match before updating a users password
	$entered_pass = generateHash($password,$loggedInUser->hash_pw);
	
	/*if (trim($password) == ""){
		//$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	}*/
	if($entered_pass != $loggedInUser->hash_pw && $password != "")
	{
		//No match
		$errors[] = lang("ACCOUNT_PASSWORD_INVALID");
	}	
	if($email != $loggedInUser->email)
	{
		if(trim($email) == "")
		{
			$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
		}
		else if(!isValidEmail($email))
		{
			$errors[] = lang("ACCOUNT_INVALID_EMAIL");
		}
		else if(emailExists($email))
		{
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE", array($email));	
		}
		
		//End data validation
		if(count($errors) == 0)
		{
			$loggedInUser->updateEmail($email);
			$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
		}
	}
	
	if ($password_new != "" OR $password_confirm != "")
	{
		if(trim($password_new) == "")
		{
			$errors[] = lang("ACCOUNT_SPECIFY_NEW_PASSWORD");
		}
		else if(trim($password_confirm) == "")
		{
			$errors[] = lang("ACCOUNT_SPECIFY_CONFIRM_PASSWORD");
		}
		else if(minMaxRange(8,50,$password_new))
		{	
			$errors[] = lang("ACCOUNT_NEW_PASSWORD_LENGTH",array(8,50));
		}
		else if($password_new != $password_confirm)
		{
			$errors[] = lang("ACCOUNT_PASS_MISMATCH");
		}
		
		//End data validation
		if(count($errors) == 0)
		{
			//Also prevent updating if someone attempts to update with the same password
			$entered_pass_new = generateHash($password_new,$loggedInUser->hash_pw);
			
			if($entered_pass_new == $loggedInUser->hash_pw && $password)
			{
				//Don't update, this fool is trying to update with the same password Â¬Â¬
				$errors[] = lang("ACCOUNT_PASSWORD_NOTHING_TO_UPDATE");
			}
			elseif ($password != "")
			{
				//This function will create the new hash and update the hash_pw property.
				$loggedInUser->updatePassword($password_new);
				$successes[] = lang("ACCOUNT_PASSWORD_UPDATED");
			}
		}
	}
	if ($_FILES["file"]["error"] > 0) {
		$errors[] = "Error: " . $_FILES["file"]["error"];
	} else {
		$content = file_get_contents($_FILES["file"]["tmp_name"]);
		$loggedInUser->updateProfileImage($content);
		$successes[] = "Profile Image Updated";
	}
	if(count($errors) == 0 AND count($successes) == 0){
		$errors[] = lang("NOTHING_TO_UPDATE");
	}
}

require_once("includes/includes.php");

include 'themes/default/header.php';
$output = '';
$output .= "<div class='col-sm-7'>";
$output .= '<h3>Profile Settings</h3>';
$output .= resultBlock($errors,$successes,false);
$output .= static_field(substr($loggedInUser->accountid, 0, 10) . '<span style="color: red;">' . substr($loggedInUser->accountid, 10) . '</span>','Account ID:');
$output .= static_field('<image style="width: 100px; height: 100px;" src="' . user_gravatar() . '" />', 'Profile Image:');
$output .= static_field('If you do not choose an image, control panel looks to <a href="https://en.gravatar.com/" target="_blank">Gravatar</a> for an image.','Note:');
$output .= input_file_field('file', 'Change Image:');
$output .= input_text_field('email','Email:','Email Address',$loggedInUser->email);
$output .= '<h3>Change Password</h3>';
$output .= input_text_field('password','Old Password:',null,null,true);
$output .= input_text_field('passwordc','New Password:',null,null,true);
$output .= input_text_field('passwordcheck','Confirm Password:',null,null,true);
$output .= submit_button('submit','Save',apply_filter('admin_button_class','btn btn-primary'));
$output .= "</div>";

$output = form_wrap($output, $_SERVER['PHP_SELF'], 'POST');

echo apply_filter('user_settings',$output);

include 'themes/default/footer.php';

?>

