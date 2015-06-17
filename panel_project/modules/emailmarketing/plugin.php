<?php

$setting_group = 'account_'.get_user()->accountid;

register_setting('campaigns',$setting_group);

function marketing_page() {
	$setting_group = 'account_'.get_user()->accountid;
	echo '<div class="col-sm-8">';
	echo '<h2>Email Marketing</h2>';
	submit_button('add_marketing_submit', 'Buy Campaign', null, true, false, 'add_marketing');
	submit_button('add_marketing_plan', 'Add Marketing Plan', null, true, false, 'add_marketing_plan');
	$collection = new DataObjects(get_setting('campaigns',$setting_group));
	echo '<h3>Campaigns and Plans</h3>';
	campaignTable($collection);
	echo '</div>';
}
add_page('Email Marketing', 'Email Marketing', 'marketing_page', 'Administrator');

function campaignTable($collection) {
	echo '<table class="table">';
	echo '<tr><th>Name</th><th>Sent</th></tr>';
	foreach ($collection->collection as $obj) {
		echo "<tr><td>{$obj->name}</td><td></td></tr>";
	}
	echo '</table>';
}

register_modal('add_marketing', 'Buy Campaign', 'add_marketing');

function buy_campaign($name, $code, $listfile) {
	$setting_group = 'account_'.get_user()->accountid;
	$mObject = new MarketingObject($name, $code, $listfile);
	$objects = new DataObjects(get_setting('campaigns',$setting_group));
	$objects->addObject($mObject);
	save_setting('campaigns', $objects, $setting_group);
}

function add_marketing() {
	if (isset($_POST['campaign_name'])) {
		buy_campaign($_POST['campaign_name'], $_POST['campaign_email'], '');
	}
	echo input_text_field('campaign_name','Campaign Name');
	echo input_text_area('campaign_email','Email HTML');
	echo input_file_field('campaign_list','Recipient List (*.csv)');
}

class MarketingObject extends DataObject {
	public $name;
	public $emailcode;
	public $sent = false;
	public $invoice;
	
	public function __construct($name, $email, $list) {
		$this->name = $name;
		$this->emailcode = $email;
		return $this;
	}
}

class MarketingPlan extends DataObject {
	public $sub;
	
	
}