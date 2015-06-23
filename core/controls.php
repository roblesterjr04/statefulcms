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
	
class CP_Control {
	
	public $options;
	public $name;
	public $owner;
	public $events = [];
	public $disabled = false;
	
	protected function atts($options) {
		$atts = [];
		foreach ($options as $option=>$v) {
			$atts[] = "$option=\"$v\"";
		}
		$atts = implode(" ", $atts);
		return $atts;
	}
	
	public function __construct($name, $options, $owner) {
		$options['data-control'] = 'control';
		$this->options = $options;
		$this->name = $name;
		$this->owner = $owner;
		$this->owner->add_control($this);
	}
	
	public function display() {
		echo $this->control();
	}
	
	public function markup() {
		if (!isset($this->options['id'])) $this->options['id'] = $this->name;
		if ($this->disabled) $this->options['disabled'] = 'disabled';
		$atts = $this->atts($this->options);
		$output = "<input name=\"{$this->name}\" $atts/>";
		return root()->hooks->filter->apply('cp_fields_control', $output);
	}
	
	public function control() {
		$sessionstate = $this->set_session_state();
		$markup = $this->markup();
		$markup .= $sessionstate;
		$markup .= $this->bind_events();
		return $markup;
	}
	
	private function bind_events() {
		$output = '';
		foreach ($this->events as $event) {
			$handler = $this->event_handler($event == 'change' || $event == 'keyup' ? '_'.$event : $event);
			$id = $this->options['id'];
			$output .= "<script>$('#$id').on('$event', function() { $handler });</script>\n";
		}
		return $output;
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
	
	public function bind($event) {
		$this->events[] = $event;
	}
	
	public function fade_out($echo = true) {
		$script = root()->hooks->filter->apply('field_fade_out', '$(\'*[name="'.$this->name.'"]\').fadeOut("fast");');
		if ($echo) echo $script;
		return $script;
	}
	
}

class CP_TextField extends CP_Control {
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		$options['type'] = 'text';
		parent::__construct($name, $options, $owner);
		$this->bind('click');
		$this->bind('keyup');
	}
}

class CP_Button extends CP_Control {
	
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		$options['type'] = 'submit';
		parent::__construct($name, $options, $owner);
		$this->bind('click');
	}
	
}

class CP_Editor extends CP_Control {
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		$options['id'] = $name;
		parent::__construct($name, $options, $owner);
	}
	
	public function markup() {
		$value = $this->options['value'];
		unset($this->options['value']);
		$atts = $this->atts($this->options);
		$event_handler = $this->event_handler('_keyup');
		$output = "<textarea class=\"ckeditor\" name=\"{$this->name}\" $atts>$value</textarea>";
		$output .= "<script>
			function {$this->name}_fn() {
				$('textarea#{$this->name}').html(CKEDITOR.instances.{$this->name}.getData());
				$event_handler
			}
			CKEDITOR.replace('{$this->name}'); 
		</script>";
		return $output;
	}
	
	public function update_state() {
		echo "{$this->name}_fn();";
	}
	
}

class CP_TextArea extends CP_Control {
	
	public function __construct($name, $text, $options = [], $owner) {
		$options['value'] = $text;
		parent::__construct($name, $options, $owner);
		$this->bind('change');
	}
	
	public function markup() {
		$value = $this->options['value'];
		unset($this->options['value']);
		$atts = $this->atts($this->options);
		$output = "<textarea name=\"{$this->name}\" $atts>$value</textarea>";
		return $output;
	}
	
}

class CP_Hidden extends CP_Control {
	public function __construct($name, $value, $options = [], $owner) {
		$options['value'] = $value;
		$options['type'] = 'hidden';
		parent::__construct($name, $options, $owner);
	}
}

class CP_Label extends CP_Control {
	public function __construct($name, $value, $options = [], $owner) {
		$options['value'] = $value;
		$options['name'] = $name;
		parent::__construct($name, $options, $owner);
	}
	
	public function markup() {
		$atts = $this->atts($this->options);
		$value = $this->options['value'];
		$output = "<span $atts>$value</span>";	
		return $output;	
	}
}

class CP_Timer extends CP_Control {

	public function __construct($name, $interval, $options = [], $owner) {
		$this->interval = $interval;
		parent::__construct($name, $options, $owner);
		$this->bind('tick');
	}
	
	public function markup() {
		$sender = base64_encode(serialize($this));
		$output = "<script type=\"text/javascript\" name=\"{$this->name}\">var int_{$this->name} = setInterval(function() { cp_ajax('{$this->name}', sessionState,'$sender', 'tick'); }, {$this->interval});</script>$sessionstate";
		return $output;
	}
	
}

class CP_NoticeArea extends CP_Control {
	
	public $notices = [];
	
	public function __construct($name, $options = [], $owner) {
		$options['data-notice'] = 'notice-area';
		parent::__construct($name, $options, $owner);
	}
	
	public function markup() {
		$atts = $this->atts($this->options);
		$output = "<div id=\"{$this->name}\" $atts></div>";
		return $output;
	}
	
	public function add_notice($notice, $name = 'notice', $type = 'primary', $time = 0) {
		$this->notices[$name] = [
			'type'=>$type,
			'time'=>$time,
			'message'=>$notice
		];
	}
	
	public function show_notice($notice) {
		
	}
	
}

class CP_Table extends CP_Control {
	
	public function __construct($name, $data, $keys = false, $options = [], $owner) {
		$this->table_data = $data;
		$this->table_key_data = $keys;
		if (count($data) > 0) {
			if ($keys) {
				$keys = array_keys($keys);
			} else {
				if (is_object($data[0])) {
					$keys = $data[0]->columns;
				} else {
					$keys = array_keys($data[0]);
				}
			}
		}
		$this->table_keys = $keys;
		parent::__construct($name, $options, $owner);
		root()->hooks->action->perform('new_cp_table', $this);
	}
	
	public function delete_row($rowid) {
		$animation = 'fadeOut';
		if (isset($this->options['delete_animation'])) $animation = $this->options['delete_animation'];
		echo '$(\'table[name="'.$this->name.'"] tr[row-key-val="'.$rowid.'"]\').'.$animation.'();';
	}
	
	public function add_row($row) {
		
	}
	
	private function _row($row) {
		$key_data = $this->table_key_data;
		$keys = $this->table_keys;
		if (is_array($row)) {
			$row = json_decode(json_encode($row), false);
		}
		$row = root()->hooks->filter->apply('cp_component_table_row_data', $row);
		$firstcol = in_array('id', $keys) ? 'id' : $keys[0];
		$firstval = $row->$firstcol;
		$this->row_key = $firstcol;
		$row_output = "<tr row-key=\"$firstcol\" row-key-val=\"$firstval\">";
		foreach ($keys as $col) {
			$func = isset($key_data[$col]['callback']) ? $key_data[$col]['callback'] : false;
			$callback = $this->owner && $func ? $this->owner->$func($row) : '';
			$value = isset($key_data[$col]['value']) ? $key_data[$col]['value'] : $callback;
			if (is_array($key_data[$col]) && !isset($key_data[$col]['display'])) continue;
			// Output of column
			$row_output .= '<td>' . root()->hooks->filter->apply('cp_component_table_cell', $value ?: $row->{$col}) . '</td>';
		}
		$row_output .= '</tr>';
		return $row_output;
	}
	
	public function markup() {
		$keys = $this->table_keys;
		$key_data = $this->table_key_data;
		$data = $this->table_data;
		$owner = $this->owner;
		$sessionstate = $this->set_session_state();
		$atts = $this->atts($this->options);
		$output = '<table name="'.$this->name.'" '.$atts.'>';
		$thead = '';
		$row_output = '';
		if (count($data) > 0) {
			foreach ($data as $row) {
				$row_output .= $this->_row($row);
			}
			$thead .= '<tr>';
			foreach ($keys as $key) {
				if (is_array($key_data[$key]) && !isset($key_data[$key]['display'])) continue;
				$sortcol = $key_data && isset($key_data[$key]['no_sort']) && $key_data[$key]['no_sort'];
				$thead .= '<th>';
				$thead .= (!$sortcol ? '<a href="#" class="table-sort" data-col="'.$key.'">' : '') . ($key_data ? ($key_data[$key]['display']?:$key) : $key) . (!$sortcol ? '</a>' : '');
				$thead .= '</th>';
			}
			$thead .= '</tr>';
			$output .= $thead;
			$output .= $row_output;
		} else {
			$output .= '<tr colspan="'.count($keys).'"><td>No Data to Display</td></tr>';
		}
		$output .= '</table>' . $sessionstate;
		return $output;
	}
}