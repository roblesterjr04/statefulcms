<?php

chdir('../../');
require_once('models/config.php');
require_once('includes/includes.php');

$input = @file_get_contents("php://input");
$event_json = json_decode($input);

// Do something with $event_json

if ($event_json->type == 'invoice.payment_succeeded') {
	echo 'Running';
	var_dump($event_json);
	$customer = $event_json->data->object->customer;
	$card = get_card($customer);
	$amount = get_billable_minutes($event_json->data->object->lines->data->plan->id, $customer);
	echo $customer;
	echo $card;
	echo $amount;
	Stripe_Charge::create(array(
		"amount" => $amount,
		"currency" => "usd",
		"card" => $card, // obtained with Stripe.js
		"customer" => $customer
	));
}
