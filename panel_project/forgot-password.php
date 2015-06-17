<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
require_once("includes/includes.php");

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//User has confirmed they want their password changed 
if(!empty($_GET["confirm"]))
{
	$token = trim($_GET["confirm"]);
	
	if($token == "" || !validateActivationToken($token,TRUE))
	{
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	}
	else
	{
		$rand_pass = getUniqueCode(15); //Get unique code
		$secure_pass = generateHash($rand_pass); //Generate random hash
		$userdetails = fetchUserDetails(NULL,$token); //Fetchs user details
		$mail = new userCakeMail();		
		
		//Setup our custom hooks
		$hooks = array(
			"searchStrs" => array("#GENERATED-PASS#","#USERNAME#"),
			"subjectStrs" => array($rand_pass,$userdetails["display_name"])
			);
		
		if(!$mail->newTemplateMsg("your-lost-password.txt",$hooks))
		{
			$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
		}
		else
		{	
			if(!$mail->sendMail($userdetails["email"],"Your new password"))
			{
				$errors[] = lang("MAIL_ERROR");
			}
			else
			{
				if(!updatePasswordFromToken($secure_pass,$token))
				{
					$errors[] = lang("SQL_ERROR");
				}
				else
				{	
					if(!flagLostPasswordRequest($userdetails["user_name"],0))
					{
						$errors[] = lang("SQL_ERROR");
					}
					else {
						$successes[]  = lang("FORGOTPASS_NEW_PASS_EMAIL");
					}
				}
			}
		}
	}
}

//User has denied this request
if(!empty($_GET["deny"]))
{
	$token = trim($_GET["deny"]);
	
	if($token == "" || !validateActivationToken($token,TRUE))
	{
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	}
	else
	{
		
		$userdetails = fetchUserDetails(NULL,$token);
		
		if(!flagLostPasswordRequest($userdetails["user_name"],0))
		{
			$errors[] = lang("SQL_ERROR");
		}
		else {
			$successes[] = lang("FORGOTPASS_REQUEST_CANNED");
		}
	}
}

//Forms posted
if(!empty($_POST))
{
	$email = $_POST["email"];
	
	//Perform some validation
	//Feel free to edit / change as required
	
	if(trim($email) == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
	}
	//Check to ensure email is in the correct format / in the db
	elseif(!isValidEmail($email) || !emailExists($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	if(count($errors) == 0)
	{
		
		//Check that the username / email are associated to the same account
		//Check if the user has any outstanding lost password requests
		$userdetails = fetchUserDetails(null, null, null, $email);
		if($userdetails["lost_password_request"] == 1)
		{
			$errors[] = lang("FORGOTPASS_REQUEST_EXISTS");
		}
		else
		{
			//Email the user asking to confirm this change password request
			//We can use the template builder here
			
			//We use the activation token again for the url key it gets regenerated everytime it's used.
			
			$mail = new userCakeMail();
			$confirm_url = lang("CONFIRM")."\n".$websiteUrl."forgot-password.php?confirm=".$userdetails["activation_token"];
			$deny_url = lang("DENY")."\n".$websiteUrl."forgot-password.php?deny=".$userdetails["activation_token"];
			
			//Setup our custom hooks
			$hooks = array(
				"searchStrs" => array("#CONFIRM-URL#","#DENY-URL#","#USERNAME#"),
				"subjectStrs" => array($confirm_url,$deny_url,$userdetails['displayname'])
				);
			
			if(!$mail->newTemplateMsg("lost-password-request.txt",$hooks))
			{
				$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
			}
			else
			{
				if(!$mail->sendMail($userdetails["email"],"Lost password request"))
				{
					$errors[] = lang("MAIL_ERROR");
				}
				else
				{
					//Update the DB to show this account has an outstanding request
					if(!flagLostPasswordRequest($userdetails["user_name"],1))
					{
						$errors[] = lang("SQL_ERROR");
					}
					else {
						
						$successes[] = lang("FORGOTPASS_REQUEST_SUCCESS");
					}
				}
			}
		}
	}
}

?><!DOCTYPE html>
<html>
<head>
<?php get_header_scripts(); ?>

</head><?php
echo "
<body style=\"background: url(signin-back.jpg) no-repeat 100% 0; \">
<div class='container'>
<div class=\"row\">
<div class=\"col-sm-3\">
<a href=\"http://www.rmlsoft.com\"><img src=\"logo.png\" style=\"width: 100%; margin-top: 20px;\" /></a>
</div>
</div>
<div class=\"row\"><div class='col-sm-4 col-sm-offset-4' style='margin-top: 75px;'>
<h2>Recover Password</h2>";

echo resultBlock($errors,$successes);

echo "
<form name='newLostPass' action='".$_SERVER['PHP_SELF']."' method='post' role='form'>
<div class='form-group'>
    <label for='username'>Email Address</label>
    <input type='email' class='form-control' name='email' placeholder='Email' autocomplete=\"off\">
  </div>
  
  <button type='submit' class='btn btn-block btn-warning'>Submit</button>
  <a href='login.php' class='btn btn-block btn-link' style='color: white; text-align: left;'>Return to Login</a>
  </form>
</div></div></div>
</body>
</html>";


?>
