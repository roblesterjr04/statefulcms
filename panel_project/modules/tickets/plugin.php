<?php

register_setting('mb_server', 'ticketing_settings');
register_setting('mb_email', 'ticketing_settings');
register_setting('mb_user', 'ticketing_settings');
register_setting('mb_password', 'ticketing_settings');
register_setting('mb_smtp', 'ticketing_settings');
register_setting('mb_port', 'ticketing_settings');
register_setting('mb_ssl', 'ticketing_settings');
register_setting('mb_val', 'ticketing_settings');
register_setting('mb_tls', 'ticketing_settings');
register_permission('Ticket Technician');
register_permission('Ticket Administrator');

define('SECRET_KEY', 'UlQkGHqt9Jil#xhLKFZUONXivSZx!adW');

function setting_checkbox_class( $content ) {
	return 'col-md-7 col-md-offset-5';
}
add_filter('input_checkbox_field_class_mb_ssl','setting_checkbox_class');
add_filter('input_checkbox_field_class_mb_val','setting_checkbox_class');
add_filter('input_checkbox_field_class_mb_tls','setting_checkbox_class');

function encrypt_mb_pass( $content ) {
	global $loggedInUser;
	$key = substr(fnEncrypt( SECRET_KEY, $loggedInUser->email ), 0, 32);
	return fnEncrypt( $content, $key );
}
add_filter('save_setting_mb_password', 'encrypt_mb_pass');
add_filter('save_setting_mb_user', 'encrypt_mb_pass');
add_filter('save_setting_mb_email', 'encrypt_mb_pass');

function decrypt_mb_pass( $content ) {
	global $loggedInUser;
	$key = substr(fnEncrypt( SECRET_KEY, $loggedInUser->email ), 0, 32);
	return fnDecrypt( $content, $key );
}
add_filter('get_setting_mb_password', 'decrypt_mb_pass');
add_filter('get_setting_mb_user', 'decrypt_mb_pass');
add_filter('get_setting_mb_email', 'decrypt_mb_pass');

function tickets_scripts() {
	echo '<link rel="stylesheet" href="' . pluginPath(__FILE__) . '/style.css" />';
}
add_action('ap_head', 'tickets_scripts');

db_add_table('support_tickets', array(
		array("name"=>"id", "type"=>"bigint", "attr"=>"NOT NULL AUTO_INCREMENT KEY"),
		array("name"=>"summary", "type"=>"char(250)"),
		array("name"=>"open_date", "type"=>"datetime"),
		array("name"=>"assigned_to", "type"=>"char(50)"),
		array("name"=>"requestor", "type"=>"char(50)"),
		array("name"=>"status", "type"=>"int"),
		array("name"=>"sub_status", "type"=>"int")
	));
db_add_table('support_comments', array(
		array("name"=>"id", "type"=>"bigint", "attr"=>"NOT NULL AUTO_INCREMENT KEY"),
		array("name"=>"ticket_id", "type"=>"bigint"),
		array("name"=>"comment", "type"=>"text"),
		array("name"=>"tech_email", "type"=>"char(50)"),
		array("name"=>"comment_date", "type"=>"datetime"),
		array("name"=>"user_email", "type"=>"char(50)"),
		array("name"=>"private", "type"=>"int"),
		array("name"=>"uid", "type"=>"char(75), UNIQUE(uid)")
	));
db_add_table('support_status', array(
		array("name"=>"id", "type"=>"bigint", "attr"=>"NOT NULL AUTO_INCREMENT KEY"),
		array("name"=>"status", "type"=>"char(50)")
	), array("types"=>"s", "rows"=>array(
			array("status"=>"Open"),
			array("status"=>"Closed")
		)
	));

function tickets_page() {
	echo '<div class="col-sm-12">';
	echo '<h2>Support Tickets</h2>';
	echo '<div class="row">';
	if (isset($_GET['ticket'])) {
		$id = $_GET['ticket'];
		$ticket = get_tickets( 0, $id );
		$ticket = $ticket[0];
		echo '<form class="form" method="POST" id="comment_form">';
		global $loggedInUser;
		if (isset($_POST['comment'])) {
			add_comment($_POST['add_comment'], $loggedInUser->email, $_POST['private'], null, false, uniqid());
			if ($_POST['private'] == 0) send_response($ticket);
		}
		echo '<div class="col-sm-8">';
		echo '<div class="panel panel-primary">';
		echo '<div class="panel-heading">Comments</div>';
		echo '<div class="panel-body comments">';
		$comments = get_comments();
		if ($comments) {
			foreach ($comments as $comment) {
				$t = $comment['tech'] != NULL;
				echo '<div class="row comment">';
				if (!$t) {
					echo '<div class="col-xs-2">';
					echo '<img src="' . user_gravatar($comment['user']) . '" />';
					echo '</div>';
				} else {
					echo '<div class="col-xs-2 visible-xs">';
					echo '<img src="' . user_gravatar($comment['tech']) . '" />';
					echo '</div>';
				}
				if ($t) $tech = get_user($comment['tech']);
				echo '<div class="col-xs-10">';
				echo '<div class="alert ' . ($t ? ' alert-warning' : ' alert-info') . '">';
				echo '<p>' . ($t ? $tech['display_name'] : $comment['user']) . ' ' . $comment['date'] . '</p>';
				echo '<p>' . str_replace("\n","<br/>",$comment['comment']) . '</p>';
				echo '</div>';
				echo '</div>';
				if ($t) {
					echo '<div class="col-xs-2 hidden-xs">';
					echo '<img src="' . user_gravatar($comment['tech']) . '" />';
					echo '</div>';
				}
				echo '</div>';
			}
		}
		echo '</div>';
		echo '</div>';
		echo '<div class="panel panel-primary">';
		echo '<div class="panel-body">';
		echo input_text_area('add_comment', 'Add new comment', 'Comment...', '', false, true);
		echo input_checkbox_field('private',null,'Private', false, false, true);
		echo submit_button('comment', 'Add Comment');
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="col-sm-4">';
		echo '<div class="panel panel-primary">';
		echo '<div class="panel-heading">Details</div>';
		echo '<div class="panel-body">';
		echo static_field($ticket['id'],'Ticket #','ticket_id',false,true);
		echo input_select_dropdown('ticket_status',get_statuses(),$ticket['status'],'Status',false,true);
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</form>';
	} else {
		echo '<div class="col-sm-12">';
		tickets_panel();
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
}
add_page('Tickets', 'Ticketing system', 'tickets_page', 'Tickets Technician');

function tickets_mail_subpage() {
	echo '<div class="col-sm-12">';
	echo '<h2>Mail Settings</h2>';
	if (isset($_POST['submit'])) do_settings('ticketing_settings');
	echo '<form method="post" class="form-horizontal" id="setting_form">';
	echo '<div class="row"><div class="col-sm-6">';
	echo '<h3>Incoming Settings</h3>';
	input_text_field('mb_server', 'Server:', 'mail.server.com', get_setting('mb_server', 'ticketing_settings'), false, true);
	input_text_field('mb_email', 'Email Address:', 'user@server.com', get_setting('mb_email', 'ticketing_settings'), false, true);
	input_text_field('mb_user', 'Username:', 'user or user@server.com', get_setting('mb_user', 'ticketing_settings'), false, true);
	input_text_field('mb_password', 'Password:', 'Password', get_setting('mb_password', 'ticketing_settings'), true, true);
	input_text_field('mb_port', 'Port:', 'Default (143)', get_setting('mb_port', 'ticketing_settings'), false, true);
	input_checkbox_field('mb_ssl', null, 'Use SSL', get_setting('mb_ssl', 'ticketing_settings'), true, false, true);
	input_checkbox_field('mb_val', null, 'Don\'t validate Cert', get_setting('mb_val', 'ticketing_settings'), true, false, true);
	input_checkbox_field('mb_tls', null, 'No TLS', get_setting('mb_tls', 'ticketing_settings'), true, false, true);
	submit_button('submit', 'Save Settings', null, true);
	echo '</div></div>';
	echo '</form>';
	echo '</div>';
}
add_page('Mail Settings', 'Mail Settings', 'tickets_mail_subpage', 'Tickets Administrator', 'tickets_config');

function tickets_config() {
	
}
add_page('Tickets Configuration', 'Tickets Configuration', 'tickets_config', 'Tickets Administrator');

function tickets_panel() {
	echo '<table class="table">';
	$page = 0;
	if ($_GET['ticket_page']) $page = $_GET['ticket_page'];
	$ts = get_tickets($page);
	$count = get_ticket_count();
	if ($count > count($ts)) $paged = true;
	echo '<tr><th>ID</th><th>Summary</th></tr>';
	foreach ($ts as $ticket) {
		echo '<tr><th>' . $ticket['id'] . '</th><td><a href="page/tickets_page/ticket/' . $ticket['id'] . '">' . $ticket['summary'] . '</a></td></tr>';
	}
	echo '</table>';
	if ($paged) {
		echo '<ul class="pagination">';
		echo '<li' . ($page == 0 ? ' class="disabled"' : '') . '>' . ($page == 0 ? '<span>' : '<a href="'.CURRENT.'?ticket_page=' . ($page - 10) . '">') . '&laquo;' . ($page == 0 ? '</span>' : '</a>') . '</li>';
		$pages = $count / 10;
		for ($i = 0; $i < $pages; $i++) {
			echo '<li' . ($page == $i * 10 ? ' class="active"' : '') . '><a href="'.CURRENT.'?ticket_page=' . $i * 10 . '">' . ($i + 1) . '</a></li>';
		}
		echo '<li' . ($page + 10 == $i * 10 ? ' class="disabled"' : '') . '>' . ($page + 10 == $i * 10 ? '<span>' : '<a href="'.CURRENT.'?ticket_page=' . ($page + 10) . '">') . '&raquo;' . ($page + 10 == $i * 10 ? '</span>' : '</a>') . '</li>';
		echo '</ul>';
	}
}
add_dash_panel('Tickets', 'tickets_panel', 12, 'Administrator');

function get_tickets( $start = 0, $id = NULL ) {
	global $mysqli;
	$tickets = array();
	$stmt = $mysqli->prepare("SELECT id, summary, open_Date, assigned_to, requestor, status FROM support_tickets " . ( $id != NULL ? "where id = $id" : '') .
		( $id == NULL ? "LIMIT $start,10" : " LIMIT 1"));
	$stmt->execute();
	$stmt->bind_result($id, $summary, $open_date, $assigned_to, $requestor, $status);
	while($stmt->fetch()) {
		$tickets[] = array(
			"id"=>$id,
			"summary"=>$summary,
			"open_date"=>$open_date,
			"assigned_to"=>$assigned_to,
			"requestor"=>$requestor,
			"status"=>$status
		);
	}
	$stmt->close();
	//if (count($tickets) == 1) return $tickets[0];
	return $tickets;
}

function get_statuses() {
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT id, status from support_status");
	if ($stmt) {
		$statuses = array();
		$stmt->execute();
		$stmt->bind_result($id, $status);
		while($stmt->fetch()) {
			$statuses[] = array("value"=>$id,"text"=>$status);
		}
		$stmt->close();
		return $statuses;
	}
	return false;
}

function get_comments( $id = NULL, $notprivate = false ) {
	if (!$id) $id = $_GET['ticket'];
	global $mysqli;
	$comments = array();
	$stmt = $mysqli->prepare("SELECT id, ticket_id, comment, tech_email, comment_date, user_email from support_comments where ticket_id = $id" . 
		($notprivate ? " and private = 0" : "") . " order by comment_date ASC");
	if ($stmt) {
		$stmt->execute();
		$stmt->bind_result($id, $ticket, $comment, $tech, $date, $user);
		while($stmt->fetch()) {
			$comments[] = array(
				"id"=>$id,
				"ticket"=>$ticket,
				"comment"=>$comment,
				"tech"=>$tech,
				"user"=>$user,
				"date"=>$date
			);
		}
		$stmt->close();
		//if (count($comments) == 1) return $comments[0];
		return $comments;
	} else {
		return false;
	}
}

function add_comment( $text, $email, $private, $id = NULL, $user = false, $uid = null ) {
	if (!$id) $id = $_GET['ticket'];
	$e = 'tech_email';
	if ($user) $e = 'user_email';
	$private = $private ? 1 : 0;
	global $mysqli;
	$stmt = $mysqli->prepare("INSERT into support_comments (comment, comment_date, $e, ticket_id, private, uid) values('$text',NOW(),'$email',$id,$private,'$uid')");
	if ($stmt) {
		$stmt->execute();
		$stmt->close();
		return true;
	}
	return false;
}

function send_response( $ticket ) {
	$user = get_user();
	$name = $user->displayname;
	$support = get_setting('mb_email', 'ticketing_settings');
	$to = $ticket['requestor'];
	$subject = $ticket['summary'] . ' TICKET_'.$ticket['id'].'_'.uniqid();
	$comments = array_reverse(get_comments(null, true));
	$headers = "From: $name <" . strip_tags($support) . ">\r\n";
	$headers .= "Reply-To: ". strip_tags($support) . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	$body = '<p>~~Please reply above this line~~</p><table name="comments" style="width: 100%; font-family: Helvetica;">';
	foreach ($comments as $comment) {
		$tech = $comment['tech'] != NULL ? get_user($comment['tech']) : NULL;
		$body .= '<tr><td rowspan="2" style="width: 80px;"><img width="35" height="35" style="margin-bottom: 20px;" src="http:' . 
			($comment['tech'] == null ? user_gravatar($comment['user']) : user_gravatar($comment['tech'])) .
			'" /></td><td>' . ($tech ? $tech['display_name'] : $comment['user']) . ' ' . $comment['date'] . '</td></tr><tr><td valign="top">' . $comment['comment'] . '</td></tr>';
	}
	$body .= '</table>';
	mail($to, $subject, $body, $headers);
}

add_action('cron_cycle', 'check_mailbox' );

function check_mailbox() {
	$server = get_setting('mb_server','ticketing_settings');
	$user = get_setting('mb_user','ticketing_settings');
	$pass = get_setting('mb_password','ticketing_settings');
	$port = get_setting('mb_port','ticketing_settings');
	$ssl = get_setting('mb_ssl','ticketing_settings');
	$tls = get_setting('mb_tls','ticketing_settings');
	$novalidate = get_setting('mb_val','ticketing_settings');
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
add_action('cron_cycle', 'check_mailbox' );

function process_messages( $messages, $mstream ) {
	$m = array();
	foreach ($messages as $message) {
		if (!$message->seen)
			$m[] = "From: ".$message->from;
			$muid = $message->uid;
			$mno = $message->msgno;
			$h = imap_header($mstream, $mno);
			$from = $h->from[0]->mailbox . "@" . $h->from[0]->host;
			$subject = $message->subject;
			$body = imap_fetchbody($mstream, $muid, 1, FT_UID);
			$rs = strpos($body, '~~Please reply above this line~~');
			if ($rs !== FALSE) $body = substr($body, 0, $rs);
			if (strpos($subject, 'TICKET_') !== FALSE) {
				$t = explode("TICKET_", $subject);
				$t = $t[1];
				$uid = explode("_",$t);
				$tid = $uid[0];
				$uid = $uid[1];
				add_comment($body, $from, 0, $tid, true, $uid);
				imap_delete($mstream, $muid, FT_UID);
			} else {
				$uid = $message->uid;
				$tid = create_ticket($from, $subject);
				if ($tid) add_comment($body, $from, 0, $tid, true, $uid);
				imap_delete($mstream, $uid, FT_UID);
			}
	}
	$m = implode("\n", $m);
	file_put_contents('mail-log.txt',$m);
}

function create_ticket($from, $summary) {
	global $mysqli;
	$stmt_in = $mysqli->prepare("INSERT INTO support_tickets (summary, open_date, requestor, status, sub_status) values(?,NOW(),?,1,1)");
	$stmt_in->bind_param("ss", $summary, $from);
	$stmt_in->execute();
	$stmt_out = $mysqli->prepare("SELECT LAST_INSERT_ID()");
	$stmt_out->execute();
	$stmt_out->bind_result($id);
	while($stmt_out->fetch()) {
		$stmt_out->close();
		$stmt_in->close();
		return $id;
	}
	$stmt_out->close();
	$stmt_in->close();
	return false;
}

function get_ticket_count() {
	global $mysqli;
	$tickets = array();
	$stmt = $mysqli->prepare("SELECT count(id) FROM support_tickets");
	$stmt->execute();
	$stmt->bind_result($id);
	while($stmt->fetch()) {
		return $id;
	}
	$stmt->close();
	return false;
}