<?php

require_once('model.php');

function conf_settings( $page ) {
	echo '<div class="col-sm-12">';
	echo '<h2>Conferencing Options</h2>';
	echo "<style>
	td { padding: 10px 5px; }
	</style>";
	echo "<form method=\"POST\" id=\"hold-music\">";
	$setting_group = 'account_'.get_user()->accountid;
	$action = $_POST['action'];
	$area = $_POST['sel_area'];
	if ($action == 'settings') do_settings($setting_group);
	if ($action == 'adding_conf') {
		if (strlen($area) == 3) add_conferencing($_POST['sel_plan'],$_POST['sel_area']);
		else alertUser(NOTE_TYPE_ERROR, 'You must enter an area code.');
	}
	$music = get_setting('hold_music', $setting_group);
	$set_plans = get_setting('conf_plans', $setting_group);
	if (!$music) $music = 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient';
	
	echo "<input type=\"hidden\" value=\"settings\" name=\"action\" />";
	echo "<table class=\"table\">
		<tr>
			<td>Hold Music</td>
			<td><label><input name=\"hold_music\" value=\"http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient\" type=\"radio\" ";
			if ($music == 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient') echo 'checked';
	echo "/>&nbsp;Ambient</label>&nbsp;
			<label><input name=\"hold_music\" value=\"http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical\" type=\"radio\" ";
			if ($music == 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical') echo 'checked';
	echo "/>&nbsp;Classical</label>&nbsp;
			<label><input name=\"hold_music\" value=\"http://twimlets.com/holdmusic?Bucket=com.twilio.music.guitars\" type=\"radio\" ";
			if ($music == 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.guitars') echo 'checked';
	echo "/>&nbsp;Guitars</label>
		</tr>
		</table>";
	submit_button('musicbutton', 'Save Settings', null, true, true);
	echo '<h3>Conferencing Plans</h3>';
	echo "</form>";
	if (has_permission('CustomerAdmin')) {
		echo "<form method=\"POST\" role=\"form\">";
		echo "<input type=\"hidden\" value=\"adding_conf\" name=\"action\" />";
		echo '<div class="row"><div class="col-sm-12"><p><a href="http://www.rmlsoft.com/products/conferencing" target="_blank">Compare Plans</a></p></div></div>';
		echo "<div class=\"row\">";
		echo "<div class=\"col-sm-3\"><p><button type=\"submit\" class=\"btn btn-success\" onclick=\"return confirm('This will add another billable conferencing number to your account, and your 30-day trial will begin. Are you sure?');\">Add Conferencing!</button></p></div>";
		echo "<div class=\"col-sm-3\"><p><input class=\"form-control\" type=\"tel\" maxlength=\"3\" placeholder=\"Area Code (Ex: 973)\" name=\"sel_area\" /></p></div>";
		echo "<div class=\"col-sm-6\"><p><select class=\"form-control\" name=\"sel_plan\">";
		$plans = get_plans();
		foreach ($plans as $plan) {
			echo "<option value=\"" . $plan['id'] . "\">" . $plan['name'] . "</option>";
		}
		echo "</select></p></div></div>";
		echo "<div class=\"row\"><div class=\"col-sm-12\"><h3>Billed Plans</h3><br/><table style=\"width: 100%;\">";
		if ($set_plans) {
			$set_plans = json_decode($set_plans);
			foreach ($set_plans as $set_plan) {
				$plan = get_plans($set_plan->plan);
				$local = $set_plan->numbers[0]->friendly_name;
				$local_num = str_replace('+','',$set_plan->numbers[0]->phone_number);
				$toll = $set_plan->numbers[1]->friendly_name;
				$toll_num = str_replace('+','',$set_plan->numbers[1]->phone_number);
				echo "<tr><td>" . $plan['name'] . "</td><td>";
				if (isset($local)) echo "Local: <a href=\"tel:$local_num\">+1 " . $local . "</a>";
				if (isset($toll)) echo " | Toll-Free: <a href=\"tel:$toll_num\">+1 " . $toll . "</a>";
				echo "</td><td><a href=\"" . basename($_SERVER['PHP_SELF']) . "?" . $_SERVER['QUERY_STRING'] . "&remove=" . $set_plan->id . "\" onclick=\"return confirm('This will remove this conference plan from your account. Are you sure?');\">Remove</a></td></tr>";
				echo "<tr style=\"border-bottom: 1px solid #ddd;\"><td>Conference IDs: </td><td colspan=\"2\"><p>";
				foreach ($set_plan->codes as $value) {
					echo '<a href="tel:' . $local_num . ',' . str_replace('-','',$value) . '">[' . str_replace("-", "&#8209;", $value) . ']</a> ';
				}
				echo "</p></td></tr>";
			}
		} else {
			echo '<tr><td>You are not being billed for any Conferencing Plans. There are no plans associated with your account.</td></tr>';
		}
		echo "</table></div></div>";
		echo "</form>";
	}
	echo "</div>";
	perform_actions('conf_page_loaded');
}

function conf_init() {
	$setting_group = 'account_'.get_user()->accountid;
	register_setting('hold_music', $setting_group);
	register_setting('conf_plans', $setting_group);
	$remplan = $_GET['remove'];
	if (isset($remplan)) remove_conferencing($remplan);
	add_page('Conferencing', 'Conferencing Settings', 'conf_settings', 'CustomerAdmin');
	add_dash_panel("Conference Usage", "dash_panel_usage");
	perform_actions('conf_loaded');
}
add_action('init', 'conf_init');

function dash_panel_usage( $obj ) {
	global $loggedInUser;
	//$account = perform_actions('get_user_meta', array($loggedInUser->user_id));
	$result = get_usage($loggedInUser->accountid);
	echo '<h4>Usage this month</h4>';
	foreach ($result as $r) {
		$number = $r['number'];
		$plan = $r['plan'];
		echo '<p>Plan: <strong>' . $plan . '</strong> - Number: <strong>' . format_phone_number($number) . '</strong> / Minutes: <strong>' . $r['minutes'] .'</strong></p>';
	}
}

function format_phone_number( $data ) {
	if(  preg_match( '/^\+\d(\d{3})(\d{3})(\d{4})$/', $data,  $matches ) )
	{
		$result = '+1 (' . $matches[1] . ') ' .$matches[2] . '-' . $matches[3];
		return $result;
	}
}

function add_conferencing($plan,$area) {
	$setting_group = 'account_'.get_user()->accountid;
	$plans = get_setting('conf_plans',$setting_group);
	$pcount = 0;
	$obj_plan = get_plans(intval($plan));
	$codes = generate_codes($obj_plan["codes"]);
	$mins = $obj_plan['mins'];
	$slug = $obj_plan['slug'];
	$numbers = get_numbers($plan,$area);
	if ($numbers && isset($plans) && count($numbers) > 0 && $plans != '') {
		$plans = json_decode($plans);
		$pcount = count($plans);
		$saveplan = apply_filter('conf_plan', array("id"=>$pcount,"plan"=>$plan,"slug"=>$slug,"numbers"=>$numbers,"codes"=>$codes,"mins"=>$mins));
		$plans[$pcount] = $saveplan;
	} elseif ($numbers && count($numbers) > 0) {
		$newplan = apply_filter('conf_plan', array("id"=>0,"plan"=>$plan,"slug"=>$slug,"numbers"=>$numbers,"codes"=>$codes,"mins"=>$mins));
		$plans = array();
		if ($newplan) $plans[0] = $newplan;
	}
	if ($numbers) {
		perform_actions('conf_hook_added_plan', array($obj_plan));
	} else {
		perform_actions('conf_hook_added_plan_failed', array($obj_plan));
	}
	$plans = json_encode($plans);
	if ($numbers) save_setting('conf_plans', $plans, $setting_group);
	if ($newplan || $saveplan) alertUser(NOTE_TYPE_SUCCESS, "Conference Plan added.");
	else alertUser(1, "Failed to add conference plan. Check billing.");
}

function remove_conferencing($plan) {
	$setting_group = 'account_'.get_user()->accountid;
	$plans = get_setting('conf_plans',$setting_group);
	$new_plans = array();
	$plan_remove = null;
	if ($plans) {
		$plans = json_decode($plans);
		$plan_remove = $plans[$plan];
		foreach ($plan_remove->numbers as $number_object) {
			rel_number($number_object->phone_number);
		}
		$i = -1;
		foreach ($plans as $splan) {
			if ($splan->id != $plan) {
				$i++;
				$splan->id = $i;
				$new_plans[] = $splan;
			}
		}
	}
	perform_actions('conf_hook_removed_plan', array($plan_remove));
	if (count($new_plans) > 0) $plans = json_encode($new_plans);
	else $plans = '';
	save_setting('conf_plans', $plans, $setting_group);
	//alertUser(NOTE_TYPE_SUCCESS, "Conference Plan Removed.");
	$_GET = array();
	header('Location: page/conf_settings');
}



