<?php
	

class CP_Object {
	
	protected $_slug;
	public $controls;
	
	public function __construct($name) {
		$this->_slug = $name;
		if (isset($_GET['mod']) && $_GET['mod'] == $name) $this->active = true;
	}
	
	public function title() {
		return $this->_slug;
	}
	
	public function admin() {
		echo '<h2>' . $this->title() . '</h2>';
		$this->object_list();
	}
	
	public function front_end() {
		$this->admin();
	}
	
	protected function save($data = []) {
		$type = $this->_slug;
		$id = $data['id'];
		$name = $data['name'];
		$meta = $data['meta'];
		$object_data = ['id'=>$id,'name'=>$name,'object_type'=>$type];
		return root()->db->update('object_items', $object_data);
	}
	
	protected function delete($id) {
		root()->db->delete('object_items', $id);
	}
	
	public function menu_parts() {
		return [
			'title'=>$this->title(),
			'link'=>$this->edit_link()
		];
	}
	
	public function hyperlink() {
		return '';
	}
	
	public function menu() {
		$parts = (object) $this->menu_parts();
		return '<li class="'.($this->active ? 'active' : '').'"><a href="'.$parts->link.'">' . $parts->title . '</a></li>';
	}
	
	public function init() {
		$this->state = new stdClass();
		$this->controls = new stdClass();
	}
	
	public function init_async() {
		
	}
	
	public function get_item($id) {
		$item = root()->db->get_where('object_items', ['id'=>$id], 1);
		if ($item) {
			$row = $item->rows[0];
			$meta = root()->db->get_where('objectmeta', ['meta_item'=>$row->id, 'meta_object'=>$this->_slug])->rows;
			if ($meta) {
				$meta_array = [];
				foreach ($meta as $meta_row) {
					$row->{$meta_row->meta_name}=$meta_row->meta_value;
					$row->columns[] = $meta_row->meta_name;
					$meta_array[$meta_row->meta_name]=$meta_row->meta_value;
				}
			}
			$row->row_array = array_merge($meta_array, $row->row_array);
			return $row;
		} else {
			return false;
		}
	}
	
	public function update_control_state($control, $value) {
		$this->controls->$control->val($value, false);
		$encoded_object = base64_encode(serialize($this));
		//echo "console.log('Updating State... $control: $value');\n";
		echo "sessionState = '$encoded_object';\n";
	}
	
	public function add_control($control) {
		$name = $control->name;
		$this->controls->$name = $control;
		return $control;
	}
	
	public function edit_link($module = false, $id = null) {
		return root()->settings->get('cp_site_url').'/admin/?mod='.($module?:$this->_slug).($id?'&id='.$id:'');
	}
	
	public function get_objects($limit = null, $offset = null) {
		$items = root()->db->get_where('object_items', array('object_type'=>$this->_slug), $limit, $offset);
		$objects = [];
		if ($items->rows) {
			foreach ($items->rows as $row) {
				$meta = root()->db->get_where('objectmeta', ['meta_item'=>$row->id, 'meta_object'=>$this->_slug])->rows;
				if ($meta) {
					$meta_array = [];
					foreach ($meta as $meta_row) {
						$row->{$meta_row->meta_name}=$meta_row->meta_value;
						$row->columns[] = $meta_row->meta_name;
						$meta_array[$meta_row->meta_name]=$meta_row->meta_value;
					}
				}
				$row->row_array = array_merge($meta_array, $row->row_array);
				$objects[] = $row;
			}
		}
		return $objects;
	}
	
}
	
class CP_Objects {
	
	protected $active;
	
	public function __construct() {
		$this->init_global();
	}
	
	public function get_object($slug = false) {
		global $object;
		if ($slug) {
			$key = $slug;
			if (class_exists($key)) {
				$item = new $key;
			} else {
				$item = new CP_Object($key);
			}
			root()->hooks->action->perform('get_object', $item);
			return $item;
		} else {
			root()->hooks->action->perform('get_object', $object);
			return $object;
		}
	}
	
	public function object_list($limit = null, $offset = null) {
		$items = $this->get_objects($limit, $offset);
		echo root()->components->table($items);
	}
	
	public function add($object) {
		root()->hooks->action->perform('cp_object_add', $object);
		root()->hooks->add_hook($object, 'object', '', 0, 0);
	}
	
	public function init_global() {
		global $object;
		$object = false;
		if (isset($_GET['mod'])) {
			$key = $_GET['mod'];
			if (class_exists($key)) {
				$item = new $key;
			} else {
				$item = new CP_Object($key);
			}
			$object = $item;
		}
		$cp_hooks = root()->hooks->stack;
		foreach ($cp_hooks['object'] as $hook=>$a) {
			if (class_exists($hook)) {
				$out = new $hook;
			} else {
				$out = new CP_Object($hook);
			}
			$out->init();
		}
	}
	
}

class CP_Page extends CP_Object {
	
	public function __construct() {
		parent::__construct('CP_Page');
	}
	
	public function init() {
		root()->hooks->action->add('cp_ajax_save_page', function($data) {
			$page = $data['object'];
			$page->save($data['id']);
		}, 10, 1);
		parent::init();
	}
		
	public function title() {
		return 'Pages';
	}
	
	public function page_save_click() {
		$controls = $this->controls;
		$id = $this->state->page_save_id;
		root()->iface->console("Attempting to save page at ID ($id)");
		$data = [
			'id' => $id,
			'name' => $controls->page_title->val(),
			'meta' => [
				'page_content' => $controls->page_content->val(),
				'date_modified' => date('j/n/Y')
			]
		];
		$result = $this->save($data);
		if ($result) root()->iface->console('(' . $data['name'] . ') saved successfully.');
	}
	
	public function confirm_delete_page_response($sender, $data) {
		$id = $this->state->delete_page;
		if ($data == 'OK') {
			root()->iface->console("Deleted Page ID: $id");
		}
	}
	
	public function page_content_change() {
		$value = $this->controls->page_content->val();
		root()->iface->console('Autosave: ' . $value);
	}
	
	public function page_title_change($sender, $data) {
		$value = $this->controls->page_title->val();
		$this->controls->header_label->val($value);
		//root()->iface->console($value);
	}
	
	public function admin($id = false) {
		if (empty($_GET['id']) && !$id) {
			parent::admin();
		} else {
			$this->state->page_save_id = $id ?: $_GET['id'];
			echo '<div class="row">';
			echo '<div class="col-sm-9">';
			$item = $this->get_item($_GET['id']);
			$header = new CP_Label('header_label', $item->name, [], $this);
			echo '<h2>'.$header->control().'</h2>';
			echo '<h4>Title</h4>';
			$title_field = new CP_TextField('page_title', $item->name, array('placeholder'=>'Page Title', 'class'=>'form-control'), $this);
			$title_field->display();
			echo '<h4>Content</h4>';
			$editor = new CP_Editor('page_content', $item->page_content, array('class'=>'form-control'), $this);
			$editor->display();
			echo '</div>';
			echo '<div class="col-sm-3">';
			echo '<div class="panel panel-default"><div class="panel-body">';
			$button = new CP_Button('page_save', 'Save', array('class'=>'btn btn-block btn-primary'), $this);
			$button->display();
			echo '</div></div>';
			echo '</div>';
			echo '</div>';
		}
	}
	
	public function object_list($limit = null, $offset = null) {
		$items = $this->get_objects($limit, $offset);
		$keys = [
			'name'=>[
				'display'=>'Name', 
				'callback'=>'name_cell_link'
			],
			'date_modified'=>['display'=>'Last Modified'],
			'controls'=>[
				'display'=>'Action',
				'no_sort'=>true,
				'callback'=>'control_cell'
			]
		];
		echo root()->components->table($items, $keys, $this);
	}
	
	public function name_cell_link($row) {
		return '<a href="'.$this->edit_link('CP_Page',$row->id).'">'.$row->name.'</a>';
	}
	
	public function control_cell($row) {
		$id = $row->id;
		$this->state->pages[$id] = $row;
		$button = new CP_Button('page_delete', 'Delete', array('class'=>'btn btn-danger', 'delete-id'=>$id), $this);
		return $button->control();
	}
	
	public function page_delete_click($sender, $data) {
		$delete_id = $sender->options['delete-id'];
		$name = $this->state->pages[$delete_id]->name;
		$this->state->delete_page = $delete_id;
		root()->iface->confirm('Are you sure you want to delete page ' . $name, 'delete_page', $this, $sender);
	}
	
}

class Theme_Manager extends CP_Object {
	
	public function __construct() {
		parent::__construct('Theme_Manager');
	}
	
	public function title() {
		return 'Theme Manager';
	}
	
	public function object_items($limit = null, $offset = null) {
		$themes = [];
		$dir = CP_WORKING_DIR . '/themes';
		$theme_dirs = scandir($dir);
		foreach ($theme_dirs as $theme) {
			$details = root()->themes->get_theme_details($theme);
			if ($details) {
				$themes[] = [
					'Title' => $details->title,
					'Description' => $details->description
				];
			}
		}
		return $themes;
	}
	
	public function object_list($limit = null, $offset = null) {
		$items = $this->object_items();
		echo root()->components->table($items);
	}
	
}