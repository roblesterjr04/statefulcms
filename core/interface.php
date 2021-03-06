<?php
	
class CP_Interface {
	
	public function __construct() {
		
	}
	
	public function notice($message, $type, $name, $time, $owner, $sender, $echo = true) {
		
	}
	
	public function script($script, $echo = true) {
		
	}
	
	public function refresh($echo = true) {
		$script = root()->hooks->filter->apply('iface_refresh', 'window.location.reload();');
		if ($echo) echo $script;
		return $script;
	}
	
	public function navigate($to, $echo = true) {
		$script = root()->hooks->filter->apply('iface_refresh', 'window.location.href = "'.$to.'";');
		if ($echo) echo $script;
		return $script;
	}
	
	public function confirm($message, $name, $owner, $sender, $echo = true) {
		$name = "confirm_$name";
		$event = 'response';
		$object = root()->encode($owner);
		$sender = root()->encode($sender);
		$script = "{$owner->slug}_sessionState = '$object'; var iface_confirm_response = confirm('$message') ? 'OK' : 'Cancel'; cp_state('$name', {$owner->slug}_sessionState, '$sender', '$event', iface_confirm_response, true);";
		if ($echo) echo $script;
		return $script;
	}
	
	public function prompt($message, $name, $owner, $sender, $echo = true) {
		$name = "prompt_$name";
		$event = 'response';
		$object = root()->encode($owner);
		$sender = root()->encode($sender);
		$script = "{$owner->slug}_sessionState = '$object'; var iface_prompt_response = prompt('$message'); cp_ajax('$name', {$owner->slug}_sessionState, '$sender', '$event', iface_prompt_response, true);";
		if ($echo) echo $script;
		return $script;
	}
	
	public function alert($message, $echo = true) {
		$script = root()->hooks->filter->apply('iface_alert', 'window.alert("'.$message.'");');
		if ($echo) echo $script;
		return $script;
	}
	
	public function console($message, $echo = true) {
		$script = root()->hooks->filter->apply('iface_console', 'if (console) console.log("'.$message.'");' . "\n");
		if ($echo) echo $script;
		return $script;
	}
	
}