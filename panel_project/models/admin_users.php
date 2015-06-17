<?php

add_page('Users', 'Users', 'admin_users');

function admin_users() {

	if (!securePage($_SERVER['PHP_SELF'])){die();}
	
	//Forms posted
	if(!empty($_POST))
	{
		$deletions = $_POST['delete'];
		if ($deletion_count = deleteUsers($deletions)){
			$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");
		}
	}
	
	$userData = fetchAllUsers(); //Fetch information for all users
	$isadmin = has_permission('Administrator');

	echo "<div class=\"col-sm-12\"><h2>Users</h2>";
	echo resultBlock($errors,$successes);
	echo "<form name='adminUsers' action='".$_SERVER['PHP_SELF']."' method='post'>
	<table class='table table-stripe'>
	<tr>
	<th>Delete</th><th class=\"hidden-xs\">Display Name</th><th class=\"visible-xs\">Name</th><th>Title</th><th class=\"hidden-xs\">Last Sign In</th>";
	if ($isadmin) echo '<th class="hidden-xs">Account</th>';
	echo "</tr>";
	
	//Cycle through users
	foreach ($userData as $v1) {
		echo "
		<tr>
		<td><input type='checkbox' name='delete[".$v1['id']."]' id='delete[".$v1['id']."]' value='".$v1['id']."'";
		if ($v1['user_name'] == $loggedInUser->username) echo ' disabled';
		echo "></td>
		<td><a href=\"admin_user.php?id=".$v1['id']."\">".$v1['display_name']."</a></td>
		<td>".$v1['title']."</td>
		<td class=\"hidden-xs\">
		";
		
		//Interprety last login
		if ($v1['last_sign_in_stamp'] == '0'){
			echo "Never";	
		}
		else {
			echo date("j M, Y", $v1['last_sign_in_stamp']);
		}
		echo "</td>";
		if ($isadmin) echo "<td class=\"hidden-xs\">" . $v1['accountid'] . "</td>";
		echo "</tr>";
	}
	
	echo "
	</table>
	<a href='add_user.php' class='btn btn-success'>Add</a>
	<input type='submit' name='submit_del' value='Delete' class='btn btn-danger' />
	</form>
	</div>
	";

}


