<?php

require_once("models/config.php");
require_once("includes/includes.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

if(!empty($_POST)) {
	$email = trim($_POST["email"]);
	$username = trim(randomUserName());
	$password = getUniqueCode(15);
	$displayname = trim($_POST["displayname"]);
	$account_id = $loggedInUser->accountid;
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
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
			$user = apply_filter('add_user',$user);
			
			if(!$user->userCakeAddUser())
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
		perform_actions('subuser_added', array($user));
	}
}

include 'themes/default/header.php';

echo '<div class="col-sm-12">';
echo '<h2>Add User</h2>';
resultBlock($errors,$successes);
?>
<form class="form-horizontal" method="POST">
	<div class="form-group">
		<label class="col-sm-5">Full Name:</label>
		<div class="col-sm-7">
			<input type="text" class="form-control" name="displayname" placeholder="Name" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-5">Email Address:</label>
		<div class="col-sm-7">
			<input type="text" class="form-control" name="email" placeholder="Email Address" />
		</div>
	</div>
	<input type="submit" value="Add" class="btn btn-primary" name="submit" />
</form>
<?php
echo '</div>';

include 'themes/default/footer.php';

?>