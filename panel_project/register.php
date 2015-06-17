<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
require_once("includes/includes.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { die('You already have an account, and are currently logged in.'); }

//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$email = trim($_POST["email"]);
	//$username = trim($_POST["username"]);
	$username = trim(randomUserName());
	$displayname = trim($_POST["displayname"]);
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	$captcha = md5($_POST["captcha"]);
	$account_id = trim(randomUserName());
	
	if ($captcha != $_SESSION['captcha'])
	{
		$errors[] = lang("CAPTCHA_FAIL");
	}
	if(minMaxRange(5,25,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,25));
	}
	/*if(!ctype_alnum($username)){
		$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	}*/
	if(minMaxRange(5,25,$displayname))
	{
		$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
	}
	/*if(!ctype_alnum($displayname)){
		$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
	}*/
	if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $confirm_pass)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user object
		$user = new User($username,$displayname,$password,$email,$account_id);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status)
		{
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			$user = apply_filter('new_user',$user);
			
			if(!$user->userCakeAddUser())
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
			
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
		$newuser = fetchUserDetails($user->username);
		perform_actions( 'user_registered', array($newuser) );
	}
}

echo "
<div id='main'>";

?><!DOCTYPE html>
<html>
<head>
<?php get_header_scripts(); ?>

</head><?php
echo "
<body>
<div class='container'><div class='col-sm-6 col-sm-offset-3'>
<div class='panel panel-default'>
<div class='panel-body'>";
resultBlock($errors,$successes);
echo "
<div id='regbox'>
<form name='newUser' action='".$_SERVER['PHP_SELF']."' method='post'>

<!--<div class='form-group'>
<label>User Name:</label>
<input type='text' name='username' class='form-control' />
</div>-->
<div class='form-group'>
<label>Full Name:</label>
<input type='text' name='displayname' class='form-control' />
</div>
<div class='form-group'>
<label>Password:</label>
<input type='password' name='password' class='form-control' />
</div>
<div class='form-group'>
<label>Confirm:</label>
<input type='password' name='passwordc' class='form-control' />
</div>
<div class='form-group'>
<label>Email:</label>
<input type='text' name='email' class='form-control' />
</div>
<div class='form-group'>
<label>Security Code:</label>
<img src='models/captcha.php'>
</div>
<div class='form-group'>
<label>Enter Security Code:</label>
<input name='captcha' type='text' class='form-control' />
</div>
<div class='form-group'>
<label>&nbsp;<br>
<input type='submit' value='Register' class='form-control btn btn-primary' />
</div>

</form>
</div>";

?>
</div></div></div>
</body>
</html>
