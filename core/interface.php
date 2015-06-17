<?php
	
class CP_Interface {
	
	public function __construct() {
		
	}
	
	public function confirm($message, $name, $owner, $sender, $echo = true) {
		$name = "confirm_$name";
		$event = 'response';
		$object = root()->encode($owner);
		$sender = root()->encode($sender);
		$script = "sessionState = '$object'; var iface_confirm_response = confirm('$message') ? 'OK' : 'Cancel'; cp_ajax('$name', sessionState, '$sender', '$event', iface_confirm_response, true);";
		if ($echo) echo $script;
		return $script;
	}
	
	public function prompt($message, $name, $owner, $sender, $echo = true) {
		$name = "prompt_$name";
		$event = 'response';
		$object = root()->encode($owner);
		$sender = root()->encode($sender);
		$script = "sessionState = '$object'; var iface_prompt_response = prompt('$message'); cp_ajax('$name', sessionState, '$sender', '$event', iface_prompt_response, true);";
		if ($echo) echo $script;
		return $script;
	}
	
	public function alert($message, $echo = true) {
		$script = root()->hooks->filter->apply('iface_alert', 'window.alert("'.$message.'");');
		if ($echo) echo $script;
		return $script;
	}
	
	public function console($message, $echo = true) {
		$script = root()->hooks->filter->apply('iface_console', 'if (console) console.log("'.$message.'");');
		if ($echo) echo $script;
		return $script;
	}
	
}