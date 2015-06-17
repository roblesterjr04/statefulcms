<?php

class PhoneSysDevice
{
	public $name;
	public $user;
	public $number;
	public $silent;
	public $admin;
	public $account;
	
	public function dial() {
	
	}
}

class CallStep
{
	public $call;
	
	public function __construct($sid = null) {
		if ($sid) {
			$client = $_SESSION['client_obj'];
			$this->call = $client->account->calls->get($sid);
		}
	}
}

class PhoneNumber
{
	public $number;
	public $account;
	public $sid;
	public $id;
	public $details;
	
	public function create($area, $AccountId) {
		$url = "http://" . $_SERVER['HTTP_HOST'] . substr(dirname(__FILE__), strpos(dirname(__FILE__), "/accounts")) . "/system.php";
		$client = $_SESSION['client_obj'];
		$SearchParams = array();
		$SearchParams['AreaCode'] = $area;
		$numbers = $client->account->available_phone_numbers->getList('US', 'Local', $SearchParams);
		$account = $client->accounts->get($AccountId);
		try {
			$number = $account->incoming_phone_numbers->create(array(
				'PhoneNumber' => $numbers[0]->number,
				'VoiceUrl' => $url,
				'StatusCallback' => $url
			));
		} catch (Exception $e) {
			$err = urlencode("Error purchasing number: {$e->getMessage()}");
			die($err);
		}
	}
	
	public function existing($nid, $naccount, $nsid, $nnumber) {
		$client = $_SESSION['client_obj'];
		$this->number = $nnumber;
		$this->account = $naccount;
		$this->sid = $nsid;
		$this->id = $nid;
		$this->details = $client->account->incoming_phone_numbers->get($nsid);
		return $this;
	}
}

class PhoneSystemUser
{
	public $name;
	public $id;
	
	function __construct($id, $name = NULL) {
		$this->name = $name;
		$this->id = $id;
	}
	
	public function devices() {
		
	}
	
	public function save() {
	
	}
	
	public function create() {
	
	}
	
}

class PhoneCall
{
	public $number_to;
	public $number_from;
	public $call_id;
	
	function __construct($to, $from, $id) {
	
	}
	
	public function end() {
	
	}
	
	public function transfer($to) {
	
	}
	
	public function hold($queue = NULL) {
	
	}
}