<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/


require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { 
	$redir = $_REQUEST['redir'];
	/*if ($redir) {
		header("Location: " .$redir);
		die();
	}*/
	header("Location: index.php"); 
	die(); 
}

//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$username = sanitize(trim($_POST["username"]));
	$password = trim($_POST["password"]);
	
	//Perform some validation
	//Feel free to edit / change as required
	if($username == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	}
	if($password == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	}

	if(count($errors) == 0)
	{
		//A security note here, never tell the user which credential was incorrect
		if(!emailExists($username))
		{
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
		}
		else
		{
			$userdetails = fetchUserDetails(NULL,NULL,NULL,$username);
			perform_actions('pre_user_authenticated', array($userdetails));
			//See if the user's account is activated
			if($userdetails["active"]==0)
			{
				$errors[] = lang("ACCOUNT_INACTIVE");
			}
			else
			{
				//Hash the password and use the salt from the database to compare the password.
				$entered_pass = generateHash($password,$userdetails["password"]);
				
				if($entered_pass != $userdetails["password"])
				{
					//Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
					$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
				}
				if(!$userdetails['enabled']) {
					$errors[] = "Your accound has been disabled.";
				}
				else
				{
					//Passwords match! we're good to go'
					
					//Construct a new logged in user object
					//Transfer some db data to the session object
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
					
					//Redirect to user account page
					$redir = $_REQUEST['redir'];
					/*if ($redir) {
						header("Location: " .$redir);
						die();
					}*/
					header("Location: index.php");
					die();
				}
			}
		}
	}
}

//require_once("models/header.php");
?>
<!DOCTYPE html>
<html>
<head>
<?php ap_head(); ?>
<style>
@media (max-width: 767px) {
	h2, label { color: white; }
}
</style>
</head><?php
$output = "<body style=\"background: url(";
$output .= apply_filter('login_backdrop_path','signin-back.jpg');
$output .= ") no-repeat 100% 0; \">
<div class='container'>
<div class=\"row\">
<div class=\"col-sm-3\">
<a href=\"http://www.rmlsoft.com\">";
$output .= apply_filter('site_logo','<img src="' . apply_filter('site_logo_path','logo.png') . '" style="width: 100%; margin-top: 20px;" />');
$output .= "</a>
</div>
</div>
<div class=\"row\"><div class='col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3' style='margin-top: 75px;'>
<h2>Login</h2>";
$output .= resultBlock($errors,$successes,false);
$output .= "<form name='login' action='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."' method='post' role='form'>
<div class='form-group'>
    <label for='username'>Email Address</label>
    <input type='email' class='form-control' name='username' placeholder='Email' autocomplete=\"off\">
  </div>
  <div class='form-group'>
    <label for='password'>Password</label>
    <input type='password' class='form-control' name='password' placeholder='Password' autocomplete=\"off\">
  </div>
  
  <button type='submit' class='btn btn-block btn-warning'>Sign In</button>
  <a href='http://www.rmlsoft.com/register' class='btn btn-block btn-link' style='color: white; text-align: left;'>Create an account</a>
  <a href='forgot-password.php' class='btn btn-block btn-link' style='color: white; text-align: left;'>Forgot your password?</a>
</form>
</div></div></div>
</body>";
echo apply_filter('login_page',$output);
?>
</html>
