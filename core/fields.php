<?php
	
class CP_Fields {
	
	public $controls;
	
	public function __construct() {
		
	}
	
	public function add($field) {
		$this->fields[$field->name] = $field;
		return $field;
	}
	
}
	
class CP_Field {
	
	public $options;
	public $name;
	public $owner;
	
	protected function atts($options) {
		$atts = [];
		foreach ($options as $option=>$v) {
			$atts[] = "$option=\"$v\"";
		}
		$atts = implode(" ", $atts);
		return $atts;
	}
	
	public function __construct($name, $options) {
		$this->options = $options;
		$this->name = $name;
	}
	
	public function display() {
		echo $this->control();
	}
	
	public function control() {
		$atts = $this->atts($this->options);
		$sessionstate = $this->set_session_state();
		$output = "<input name=\"{$this->name}\" $atts/>$sessionstate";
		return CP_Filter::apply('cp_fields_control', $output);
	}
	
	public function val($text, $echo = true) {
		if ($text) {
			$this->options['value'] = $text;
			$method = 'val';
			if (get_class($this) == 'CP_Label') $method = 'text';
			$script = '$(\'*[name="'.$this->name.'"]\').'.$method.'(\''.$text.'\');';
			if ($echo) echo $script;
			return $script;
		} else {
			return $this->options['value'];
		}
	}
	
	public function hide($echo = true) {
		$script = root()->hooks->filter->apply('field_hide', '$(\'*[name="'.$this->name.'"]\').hide();');
		if ($echo) echo $script;
		return $script;
	}
	
	public function show($echo = true) {
		$script = root()->hooks->filter->apply('field_show', '$(\'*[name="'.$this->name.'"]\').show();');
		if ($echo) echo $script;
		return $script;
	}
	
	public function disable($echo = true) {
		$script = root()->hooks->filter->apply('field_disable', '$(\'*[name="'.$this->name.'"]\').prop("disabled", true);');
		if ($echo) echo $script;
		return $script;
	}
	
	public function enable($echo = true) {
		$script = root()->hooks->filter->apply('field_enable', '$(\'*[name="'.$this->name.'"]\').prop("disabled", false);');
		if ($echo) echo $script;
		return $script;
	}
	
	public function bg_color($color, $echo = true) {
		$script = root()->hooks->filter->apply('field_bg_color', '$(\'*[name="'.$this->name.'"]\').css("background-color", "'.$color.'");');
		if ($echo) echo $script;
		return $script;
	}
	
	public function fore_color($color, $echo = true) {
		$script = root()->hooks->filter->apply('field_fore_color', '$(\'*[name="'.$this->name.'"]\').css("color", "'.$color.'");');
		if ($echo) echo $script;
		return $script;
	}
	
	public function delay($script, $time, $echo = true) {
		$script = root()->hooks->filter->apply('field_delay', 'setTimeout(function() { '.$script.' }, '.$time.');');
		if ($echo) echo $script;
		return $script;
	}
	
	public function remove($echo = true) {
		$script = root()->hooks->filter->apply('field_remove', '$(\'*[name="'.$this->name.'"]\').remove();');
		if ($echo) echo $script;
		return $script;
	}
	
	protected function event_handler($event) {
		$name = $this->name;
		$sender = base64_encode(serialize($this));
		return "cp_ajax('$name',sessionState,'$sender','$event'); return false;";
	}
	
	protected function set_session_state() {
		$object = base64_encode(serialize($this->owner));
		return "<script id=\"setSessionFor{$this->name}\">sessionState = '$object';</script>";
	}
	
	protected function save_state($object) {
		return base64_encode(serialize($object));
	}
	
}

class CP_TextField extends CP_Field {
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		$options['type'] = 'text';
		$this->name = $name;
		$owner->add_control($this);
		$this->owner = $owner;
		$options['onchange'] = parent::event_handler('_change');
		parent::__construct($name, $options);
		root()->hooks->action->perform('new_cp_textfield', $this);
	}
}

class CP_Button extends CP_Field {
	
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		$options['type'] = 'submit';
		$this->name = $name;
		$owner->add_control($this);
		$this->owner = $owner;
		$this->options = $options;
		$options['onclick'] = isset($options['onclick']) ? $options['onclick'] . parent::event_handler('click') : parent::event_handler('click');
		$this->options = $options;
		root()->hooks->action->perform('new_cp_button', $this);
	}
	
}

class CP_Editor extends CP_Field {
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		$this->name = $name;
		$owner->add_control($this);
		$this->owner = $owner;
		$options['onchange'] = parent::event_handler('_change');
		parent::__construct($name, $options);
		root()->hooks->action->perform('new_cp_editor', $this);
	}
	
	public function control() {
		$value = $this->options['value'];
		unset($this->options['value']);
		$atts = $this->atts($this->options);
		$output = "<textarea name=\"{$this->name}\" $atts>$value</textarea>";
		return $output;
	}
}

class CP_TextArea extends CP_Field {
	
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		$this->name = $name;
		$owner->add_control($this);
		$this->owner = $owner;
		$options['onchange'] = parent::event_handler('change');
		parent::__construct($name, $options);
		root()->hooks->action->perform('new_cp_textarea', $this);
	}
	
	public function control() {
		$value = $this->options['value'];
		unset($this->options['value']);
		$atts = $this->atts($this->options);
		$output = "<textarea name=\"{$this->name}\" $atts>$value</textarea>";
		return $output;
	}
	
}

class CP_Hidden extends CP_Field {
	public function __construct($name, $value, $options = [], $owner) {
		$options['value'] = $value;
		$options['type'] = 'hidden';
		$this->name = $name;
		parent::__construct($name, $options);
		root()->hooks->action->perform('new_cp_hidden', $this);
	}
}

class CP_Label extends CP_Field {
	public function __construct($name, $value, $options = [], $owner) {
		$options['value'] = $value;
		$options['name'] = $name;
		$this->name = $name;
		$owner->add_control($this);
		$this->owner = $owner;
		parent::__construct($name, $options);
		root()->hooks->action->perform('new_cp_label', $this);
	}
	
	public function control() {
		$sessionstate = $this->set_session_state();
		$atts = $this->atts($this->options);
		$value = $this->options['value'];
		$output = "<span $atts>$value</span>$sessionstate";	
		return $output;	
	}
}

class CP_Timer extends CP_Field {

	public function __construct($name, $interval, $owner) {
		$this->name = $name;
		$owner->add_control($this);
		$this->owner = $owner;
		$this->interval = $interval;
		parent::__construct($name, []);
		root()->hooks->action->perform('new_cp_timer', $this);
	}
	
	public function control() {
		$sessionstate = $this->set_session_state();
		$sender = base64_encode(serialize($this));
		$output = "<script type=\"text/javascript\" name=\"{$this->name}\">var int_{$this->name} = setInterval(function() { cp_ajax('{$this->name}', sessionState,'$sender', 'tick'); }, {$this->interval});</script>$sessionstate";
		return $output;
	}
	
}