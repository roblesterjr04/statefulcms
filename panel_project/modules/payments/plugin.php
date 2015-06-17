<?php
require_once(dirname(__FILE__) . '/stripe/lib/Stripe.php');
//Stripe::setApiKey("sk_test_suA4yjiZ6Vr641QXXV3SL8OO");
Stripe::setApiKey("sk_live_SmgDAfApFyjy7tdTj4YhspY5");

add_page('Billing', 'Billing Configurations', 'payment_page', 'CustomerAdmin');
add_filter('new_user', 'new_user_registered');
add_filter('update_accountid', 'existing_user_registered');
add_filter('conf_plan', 'add_conf_plan');
add_action('conf_hook_removed_plan', 'remove_conf_plan');
add_filter('verify_before_purchase', 'check_credit');
add_action('activate_hostedapps', 'create_sub');
add_action('deactivate_hostedapps', 'remove_sub');
add_filter('activate_checkbox', 'credit_filter');

add_dash_panel('Recent Invoices', 'billing_panel');

function billing_panel() {
	ajax('invoice_table(3);', 'Retrieving your invoices...');
}

function invoice_table($length = 100) {
	$cu = get_customer();
	$invoices = Stripe_Invoice::all(array(
		"customer" => $cu['id'],
		"limit" => $length)
	);
	if ($invoices->data) {
		echo '<div style="max-height: 450px; overflow-y: scroll;"><table class="table">';
		echo '<tr><th>Date</th><th>Items</th><th>Amount</th><th>Paid</th></tr>';
		foreach ($invoices->data as $inv) {
			$total = money_format('%(#10n', $inv['total'] / 100);
			$paid = $inv['paid'];
			$date = date('M j Y', $inv['date']);
			$lines = $inv['lines'];
			$items = '';
			$discount = $inv['discount'];
			if ($discount) {
				$perc = $discount['coupon']['percent_off'];
				$discount = '-'.$perc.'% (' . ($perc * $total) / 100 . ')';
			}
			foreach ($lines->data as $line) {
				$name = $line['description'];
				if ($line['plan']['name']) $name = $line['plan']['name'];
				$items .= '<strong>'.wordwrap($name, 30, '<br>') . '</strong><br>&rarr; $' . money_format('%(#10n', $line['plan']['amount'] / 100) . ' x' . $line['quantity'] . '<br>';
			}
			echo '<tr>';
			echo '<td>'.$date.'</td>';
			echo '<td>'.$items.'</td>';
			echo '<td>$'.$total.' '.$discount.'</td>';
			echo '<td>'.($paid ? 'PAID' : '&mdash;').'</td>';
			echo '</tr>';
		}
		echo '</table></div>';
	} else {
		echo '<p>No recent invoices.</p>';
	}
}

function add_conf_plan( $plan ) {
	$slug = $plan['slug'];
	$sub = create_sub($slug);
	if ($sub) $plan['billing_sub'] = $sub;
	return $plan;
}

function credit_filter( $content ) {
	if (check_credit(null)) {
		return $content;
	} else {
		return '<p>Please verify your payment information on the Billing page. Once valid, you will be able to activate your hosting plan.</p>';
	}
}

function check_credit( $content ) {
	$card = get_card();
	if (!$card) return false;
	else return true;
}

function check_first_and_create($plan) {
	if (check_credit(null)) {
		create_sub($plan);
	} else {
		return 'billing';
	}
}

function create_sub($plan) {
	$cu = get_customer();
	if ($cu && $plan) {
		$sub = $cu->subscriptions->create(array("plan"=>$plan));
		if ($sub) {
			return $sub['id'];
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function remove_sub($plan) {
	$cu = get_customer();
	if ($cu && $plan) {
		$sub = $cu->subscriptions->retrieve($plan)->cancel();
	}
}

function remove_conf_plan( $plan ) {
	$slug = $plan->billing_sub;
	remove_sub($slug);
}

function card_years() {
	$year = date('Y');
	$years = array();
	for ($i = $year; $i < $year + 14; $i++) {
		$years[] = $i;
	}
	return $years;
}

function card_months() {
	$months = array('01','02','03','04','05','06','07','08','09','10','11','12');
	return $months;
}

function payment_page( $page ) {
	echo '<div class="col-sm-12">';
	echo '<h2>Billing</h2>';
	echo "<form method=\"POST\" class=\"form-horizontal\" id=\"cc-entry\">";
	if (!empty($_POST)) {
		$errors = array();
		if (!$_POST['card_number']) $errors[] = 'You must enter the credit card number.';
		if (!$_POST['card_name']) $errors[] = 'You must enter the credit card name.';
		if (!$_POST['card_cvc']) $errors[] = 'You must enter the security code.';
		if (count($errors) == 0) $card = add_card($_POST['card_name'], $_POST['card_number'], $_POST['exp_month'], $_POST['exp_year'], $_POST['card_cvc']);
		if (!$card && count($errors) == 0) $errors[] = 'Failed to validate card information.';
		if (count($errors) > 0) {
			foreach ($errors as $err) {
				alertUser(1, $err);
			}
		} else {
			alertUser(0, "Card was added to your account.");
		}
	}
	echo '<div class="row">';
	echo '<div class="col-sm-6">';
	echo '<h3>Current Card</h3>';
	ajax('output_card();', 'Getting card info...');
	echo '</div>';
	echo '<div class="col-sm-6">';
	echo "<h3>Card Info</h3>";
	$output = input_text_field('card_name', 'Name on card:', 'As it appears on the card');
	$output .= input_text_field('card_number', 'Card:', 'Credit card #', null, false, false, false);
	$output .= input_select_dropdown('exp_month', card_months(), null, 'Expires (month):');
	$output .= input_select_dropdown('exp_year', card_years(), null, 'Expires (year):');
	$output .= input_text_field('card_cvc', 'CVC:', 'Security Code', null, false, false, false);
	$output .= '<a href="https://stripe.com/help/security" target="_blank"><img src="images/outline.png" style="float: right;" /></a>';
	echo $output;
	echo '</div>';
	echo '</div>';
	echo submit_button("billing", "Save Settings", null, null, true);
	echo "</form>";
	echo '<h2>Previous Invoices</h2>';
	ajax('invoice_table(24);', 'Retrieving Invoices...');
	echo '</div>';
}

function output_card() {
	$card = get_card();
	$loutput = static_field($card['name'], 'Name on card:');
	$loutput .= static_field('&#9679;&#9679;&#9679;&#9679; &#9679;&#9679;&#9679;&#9679; &#9679;&#9679;&#9679;&#9679; ' . $card['last4'], 'Card #:');
	$loutput .= static_field($card['type'], 'Type:');
	$loutput .= static_field($card['exp_month'].'/'.$card['exp_year'], 'Expires:');
	if ($card) echo $loutput;
	else echo '<p>You do not have a card registered. Please add a credit card for billing.</p>';
}

function get_customer( $accountid = NULL ) {
	global $loggedInUser;
	if (!$accountid) $accountid = $loggedInUser->accountid;
	try {
		$customer = Stripe_Customer::retrieve($accountid);
		return $customer;
	}
	catch (Exception $ex) {
		return false;
	}
}

function get_card( $accountid = NULL ) {
	global $loggedInUser;
	if (!$accountid) $accountid = $loggedInUser->accountid;
	try {
		$cards = Stripe_Customer::retrieve($accountid)->cards->all(array("limit"=>1));
		return $cards['data'][0];
	}
	catch (Exception $ex) {
		return false;
	}
}

function delete_card($id, $accountid = NULL) {
	global $loggedInUser;
	if (!$accountid) $accountid = $loggedInUser->accountid;
	try {
		$cu = Stripe_Customer::retrieve($accountid);
		$cu->cards->retrieve($id)->delete();
		return true;
	}
	catch (Exception $ex) {
		return false;
	}
}

function add_card($name, $number, $month, $year, $cvc) {
	$cu = get_customer();
	$cur_card = get_card();
	if ($cur_card) delete_card($cur_card['id']);
	if ($cu) {
		try {
			$card = $cu->cards->create(array("card" => array(
				"number"=>$number,
				"exp_month"=>$month,
				"exp_year"=>$year,
				"cvc"=>$cvc,
				"name"=>$name
			)));
			if ($card) return $card['id'];
			else return false;
		}
		catch (Exception $ex) {
			return false;
		}
	} else {
		return false;
	}
}

function register_customer( $email ) {
	$customer = Stripe_Customer::create(array(
		"email" => $email
	));
	return $customer['id'];
}

function new_user_registered( $user ) {
	$id = register_customer( $user->clean_email );
	$user->accountid = $id;
	return $user;
}

function existing_user_registered( $accountid ) {
	$customer = get_customer( $accountid[0] );
	if (!$customer) {
		$user = fetchUserDetails(null,null,$accountid[1]);
		$accountid = register_customer( $user['email'] );
	}
	return $accountid;
}

function invoice_minutes( $params ) {
	$customer = get_customer( $params['account'] );
	if ($customer) {
		$invoice = Stripe_Invoice::upcoming(array("customer" => $params['account'], "subscription"=>$params['sub']));
		$new_minutes = $params['minutes'];
		$from = $params['from'];
		$to = $params['to'];
		$start = date('Y-m-d H:i:s', $invoice->period_start);
		$end = date('Y-m-d H:i:s', $invoice->period_end);
		$usage = get_usage($params['account'], $start, $end);
		foreach ($usage as $planusage) {
			if ($planusage['plan'] == $params['plan']) {
				$plan = get_plans($params['plan']);
				$mins = $plan['mins'];
				$used_minutes = $planusage['minutes'];
				if ($used_minutes >= $mins) {
					//bill these minutes right away.
					$amount = minute_cost($new_minutes);
					bill_new_minutes($new_minutes, $amount, $invoice, $from, $to);
				} elseif ($used_minutes + $new_minutes >= $mins) {
					//bill the difference after adding the new minutes
					$bill_minutes = ($used_minutes + $new_minutes) - $mins;
					$amount = minute_cost($bill_minutes);
					bill_new_minutes($bill_minutes, $amount, $invoice, $from, $to);
				} else {
					bill_new_minutes($new_minutes, 0, $invoice, $from, $to);
				}
			}
		}
	}
}
add_action( 'conf_call_ended', 'invoice_minutes' );

function minute_cost($minutes) {
	$amount = floatval($minutes) * 1;
	return $amount;
}

function bill_new_minutes($minutes, $amount, $invoice, $from, $to) {
	if ($amount == 0) $minutes .= ' (included)';
	$customer = get_customer( $invoice->customer );
	if ($customer) {
		Stripe_InvoiceItem::create(array(
			"customer" => $invoice->customer,
			"amount" => $amount,
			"currency" => "usd",
			"invoice" => $invoice->id,
			"description" => "From: $from - To: $to : $minutes minutes used on ".date('Y-m-d'))
		);
	}
}
