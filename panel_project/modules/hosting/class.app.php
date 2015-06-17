<?php

class HostedApp {
	public $domain;
	public $active;
	public $app;
	public $subscription;
	
	function __construct() {
	
	}
	
	function createApp($domain, $app) {
	
	}
}

class HostedAppList {
	public $apps = array();
	
	public function loadApps($details) {
		if (is_string($details)) {
			$this->apps = json_decode($details);
		} else {
		
		}
	}
	
	public function jsonOutput() {
		return json_encode($this->apps);
	}
	
	public function removeApp($app) {
		unset($this->apps[$app->domain]);
	}
	
	public function addApp($app) {
		$this->apps[$app->domain] = $app;
	}
}