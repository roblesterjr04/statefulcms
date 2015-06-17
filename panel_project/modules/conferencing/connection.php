<?php
chdir('../../');
require_once('models/config.php');
require_once('includes/includes.php');

$confcall = $_REQUEST['To'];
$from = $_REQUEST['From'];
$cstatus = $_REQUEST['CallStatus'];
$account = get_group($confcall);
$plan = get_plan($confcall);
if ($confcall && ($cstatus == 'in-progress' || $cstatus == 'ringing')) {
	
	header('Content-Type: text/xml');
	$digits = $_REQUEST['Digits'];
	$voice = "man";
	$recorded = false;
	$wait = get_hold_music($confcall);
	
	if (!isset($wait)) $wait = 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient';
	$callback = 'log.php?code=' . $digits . '&fromNum=' . $from . '&roomNum=' . $confcall;
	$callback = urlencode($callback);
	if (!$digits) {
		echo "
		<Response>
			<Gather timeout=\"2\">
				<Say voice=\"$voice\">Please enter your 6 digit conference I D.</Say>
			</Gather>
			<Redirect method=\"POST\">connection.php</Redirect>
		</Response>";
	} else {
		$valid = validate_code($digits, $confcall);
		$max = get_max_parts($confcall);
		
		$conference_room = "room_$account" . "_$digits";
		$participants = get_participants($conference_room);
		$lang_parts = "is 1 caller";
		if ($participants > 1) $lang_parts = "are $participants callers";
		if ($valid) {
			//log_start($confcall, $from, $digits);
			echo "
			<Response>
				<Say voice=\"$voice\">Thank you. You are now being placed in to conference.</Say>
				";
				if ($recorded) {
					echo "<Say voice=\"$voice\">At the request of the administrator, this conference is being recorded.</Say>";
				}
				if ($participants > 0) echo "<Say voice=\"$voice\">There $lang_parts on the call. Please announce yourself.</Say>";
				else echo "<Say voice=\"$voice\">The conference has not yet started.</Say>";
				echo "
				<Dial>
					<Conference maxParticipants=\"$max\" waitUrl=\"$wait\">$conference_room</Conference>
				</Dial>
			</Response>";
		} else {
			echo "
			<Response>
				<Say voice=\"$voice\">You have made an invalid entry.</Say>
				<Redirect method=\"POST\">connection.php</Redirect>
			</Response>";
		}
	}
} else {
	$dur = $_REQUEST['Duration'];
	perform_actions('conf_call_ended', array(array('minutes'=>$dur, 'plan'=>$plan['slug'], 'sub'=>$plan['billing_sub'], 'account'=>str_replace('account_', '', $account), 'from'=>$from, 'to'=>$confcall)));
	logActivity($confcall . '|' . $plan['slug'] . '|' . $account, $dur);
}
?>


