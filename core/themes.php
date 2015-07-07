<?php
	
class CP_Themes {
	
	protected $current_theme_dir;
	public $current_theme;
	
	public function __construct() {
		
		global $root;
		
		$ct = $root->settings->get('cp_current_theme');
		
		$this->current_theme = $ct;
		$this->current_theme_dir = CP_WORKING_DIR . '/themes/' . $ct;
		
	}
	
	public function init_theme() {
		include($this->current_theme_dir . '/functions.php');
	}
	
	public function get_theme_dir() {
		return $this->current_theme_dir;
	}
	
	public function get_theme_part($slug, $output = true) {
		global $root;
		$slug = root()->hooks->filter->apply('theme_part', $slug);
		$theme_part = $this->current_theme_dir . '/' . $slug . '.php';
		if ($output) {
			include($theme_part);
		} else {
			$output = file_get_contents($theme_part);
		}
		root()->hooks->action->perform($slug . '_loaded');
		if ($output) return $output;
	}
	
	public function cp_head() {
		root()->components->head();
		root()->hooks->action->perform('cp_head');
	}
	
	public function cp_footer() {
		root()->hooks->action->perform('cp_footer');
	}
	
	public function get_theme_url() {
		return root()->settings->get('cp_site_url').'/themes/' . $this->current_theme;
	}
	
	public function get_theme_details($slug = false) {
		if (!$slug) $slug = $this->current_theme;
		$theme_def = CP_WORKING_DIR . '/themes/' . $slug . '/theme.json';
		if (file_exists($theme_def)) {
			$json = file_get_contents($theme_def);
			$details = json_decode($json);
			return $details;
		} else {
			return false;
		}
	}
	
}

class CP_Components {
		
	public function admin_menu($menu = 'side', $class = 'nav nav-pills nav-stacked', $echo = true) {
		$objects = root()->hooks->stack['object'];
		$class = root()->hooks->filter->apply('admin_menu_class', $class);
		$output = '<ul class="'.$class.'">';
		foreach ($objects as $object=>$a) {
			if (class_exists($object)) {
				$item = new $object;
			} else {
				$item = new CP_Object($object);
			}
			
			if (in_array($menu, $item->menus)) $output .= $item->menu();
		}
		$output .= '</ul>';
		if ($echo) echo $output;
		return $output;
	}
	
	public function head() {
		
	}
	
	public function state_script() {
		echo '<script type="text/javascript">var state_host = "'.root()->settings->get('cp_site_url').'/admin/state.php";</script>';
		echo '<script type="text/javascript">var ajax_host = "'.root()->settings->get('cp_site_url').'/admin/ajax.php";</script>';
		echo '<script type="text/javascript" src="'.root()->settings->get('cp_site_url').'/js/state.js"></script>';
	}
	
	public function jquery() {
		echo '<script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.min.js"></script>';
	}
	
	public function admin_content() {
		$item = root()->objects->get_object();
		echo root()->hooks->filter->apply('admin_content', $item->admin());
		echo '<script>';
		$item->finished_loading();
		echo '</script>';
	}
	
	public function object_content() {
		$item = root()->objects->get_object();
		if (!$item->is_public) return;
		echo root()->hooks->filter->apply('object_content', $item->front_end());
	}
	
	public function table($data, $keys = false, $owner = false) {
		echo '<table class="table">';
		$thead = '';
		$row_output = '';
		$key_data = $keys;
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
			foreach ($data as $row) {
				if (is_array($row)) {
					$row = json_decode(json_encode($row), false);
				}
				$row = root()->hooks->filter->apply('cp_component_table_row_data', $row);
				$firstcol = in_array('id', $keys) ? 'id' : $keys[0];
				$firstval = $row->$firstcol;
				$row_output .= "<tr row-key=\"$firstcol\" row-key-val=\"$firstval\">";
				foreach ($keys as $col) {
					$func = isset($key_data[$col]['callback']) ? $key_data[$col]['callback'] : false;
					$callback = $owner && $func ? $owner->$func($row) : '';
					$value = isset($key_data[$col]['value']) ? $key_data[$col]['value'] : $callback;
					if (is_array($key_data[$col]) && !isset($key_data[$col]['display'])) continue;
					// Output of column
					$row_output .= '<td>' . root()->hooks->filter->apply('cp_component_table_cell', $value ?: $row->{$col}) . '</td>';
				}
				$row_output .= '</tr>';
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
			echo $thead;
			echo $row_output;
		} else {
			echo '<tr colspan="'.count($keys).'"><td>No Data to Display</td></tr>';
		}
		echo '</table>';
	}
	
}