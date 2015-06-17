<?php

//require_once(realpath(dirname(__FILE__)) . '/twilio/Services/Twilio.php'); // Loads the library
 
// Your Account Sid and Auth Token from twilio.com/user/account

register_setting('notif_number', 'notif_settings');
register_setting('notif_start', 'notif_settings');
register_setting('notif_end', 'notif_settings');
add_action('cron_cycle','check_mailbox');
add_page('Notify', 'CW Notify', 'notif_settings', 'Administrator');

function notif_settings() {
	do_settings('notif_settings');
	echo '<div class="col-sm-6">';
	echo '<form method="post" class="form-horizontal" id="setting_form">';
	input_text_field('notif_number', 'Number:', '+11234567890', get_setting('notif_number', 'notif_settings'), false, true);
	input_text_field('notif_start', 'Starting hour:', 'x', get_setting('notif_start', 'notif_settings'), false, true);
	input_text_field('notif_end', 'Ending hour:', 'x', get_setting('notif_end', 'notif_settings'), false, true);
	submit_button('submit', 'Save Settings', null, true);
	echo '</form>';
	echo '</div>';
}

function check_mailbox() {
	$hour = date('h') + 3;
	$start = intval(get_setting('notif_start', 'notif_settings'));
	$end = intval(get_setting('notif_end', 'notif_settings'));
	if ($start <= $hour && $hour < $end) {
		//echo '<br><br>Within time. Will run now.<br><br>';
		$server = "mail.hostedapp.us";
		$user = "cwnotify@hostedapp.us";
		$pass = "YVL00PyNGTy6";
		//$port = get_setting('mb_port','ticketing_settings');
		//$ssl = get_setting('mb_ssl','ticketing_settings');
		//$tls = get_setting('mb_tls','ticketing_settings');
		//$novalidate = get_setting('mb_val','ticketing_settings');
		if ($port) $port = ':'.$port; else $port = ':143';
		if ($ssl) $ssl = '/ssl'; else $ssl = '';
		if ($novalidate) $novalidate = '/novalidate-cert'; else $novalidate = '';
		if (!$tls) $tls = '/notls'; else $tls = '';
		$mbox = imap_open("{".$server.$port.$ssl.$novalidate.$tls."}INBOX", $user, $pass)
			or die("Failed to connect: " . imap_last_error());
		$mc = imap_check($mbox);
		$mn = $mc->Nmsgs;
		$messages = imap_fetch_overview($mbox, "1:$mn",0);
		process_messages($messages, $mbox);
		imap_close($mbox);
	}
}

function process_messages( $messages, $mstream ) {
	$m = array();
	foreach ($messages as $message) {
		if (!$message->seen) {
			$m[] = "From: ".$message->from;
			$muid = $message->uid;
			$mno = $message->msgno;
			$h = imap_header($mstream, $mno);
			$from = $h->from[0]->mailbox . "@" . $h->from[0]->host;
			$subject = $message->subject;
			$body = imap_fetchbody($mstream, $muid, 1, FT_UID);
			imap_delete($mstream, $mno);
			send_sms($body, $sub);
		}
	}
	//$m = implode("\n", $m);
	//file_put_contents('mail-log.txt',$m);
}

function send_sms( $text, $sub ) {
	$sid = "ACd13c9b7c0fec6081107fa7eae61441fb"; 
	$token = "6dd9cc1e844f1dfec72e6187cf92b219"; 
	$client = new Services_Twilio($sid, $token);
	 
	$sms = $client->account->sms_messages->create("+19738214184", get_setting('notif_number', 'notif_settings'), $text, array());
	
	'We sent a message!!!!! - '.$sub.'<br><br>';
}