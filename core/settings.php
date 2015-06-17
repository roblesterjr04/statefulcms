<?php
	
class CP_Settings {
	public $root;
	
	public function __construct() {
		global $root;
		$this->root = $root;
	}
	
	public function get($setting) {
		$setting_result = $this->root->db->get_where('settings', array('setting_name'=>$setting));
		if (count($setting_result->rows) > 0) {
			return $setting_result->rows[0]->setting_value;
		} else {
			return false;
		}
	}
	
	public function set($setting) {
		
	}
	
}