<?php

class AudioResponse extends CallStep
{
	public function panelFace() {
		
	}
}

class TransferCall extends CallStep
{
	public function panelFace() {
		
	}
}

class HoldCall extends CallStep
{
	private $queuename = 'default';
	private $wait = 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient';
	public $holdmessage = 'Please hold for the next agent.';
	
	public function panelFace() {
		
	}
	
	public function callOutput() {
		echo '<Say>'.$this->holdmessage.'</Say>';
		echo '<Enqueue waitUrl="'.$this->wait.'">' . $this->queuename . '</Enqueue>';
	}
}