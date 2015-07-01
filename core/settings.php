<?php
	
class CP_Settings {
	
	public function __construct() {
		
	}
	
	public function get($setting) {
		$setting_result = root()->db->get_where('settings', array('setting_name'=>$setting));
		if (count($setting_result->rows) > 0) {
			$value = $setting_result->rows[0]->setting_value;
			return $value;
		} else {
			return null;
		}
	}
	
	public function set($setting, $value) {
		if ($value === false) $value = 0;
		$exists = $this->get($setting);
		if ($exists !== null) {
			$setting_result = root()->db->update('settings', ['setting_value'=>$value], ['setting_name'=>$setting]);
		} else {
			$setting_result = root()->db->insert('settings', ['setting_value'=>$value, 'setting_name'=>$setting]);
		}
		return $setting_result;
	}
	
}