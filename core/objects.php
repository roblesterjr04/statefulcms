<?php
	

class CP_Object {
	
	protected $_slug;
	public $controls;
	
	public $is_public = true;
	
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
	
	public function save($data = []) {
		$saved = false;
		$type = $this->_slug;
		$id = $data['id'];
		$name = $data['name'];
		$meta = $data['meta'];
		$object_data = ['id'=>$id,'name'=>$name,'object_type'=>$type];
		$meta_data = $data['meta'];
		$saved = root()->db->update('object_items', $object_data, ['id'=>$id]);
		foreach ($data['meta'] as $key=>$value) {
			$saved = root()->db->update('objectmeta', ['meta_value'=>$value], ['meta_item'=>$id, 'meta_object'=>$type, 'meta_name'=>$key]);
		}
		return $saved;
	}
	
	public function remove($id) {
		$table = root()->db->prefix . 'object_items';
		root()->db->mySql->query("delete from $table where id = $id");
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
	
	public function finished_loading() {
		
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
	
	public function edit_link($id = null) {
		return root()->settings->get('cp_site_url').'/admin/?mod='.$this->_slug.($id?'&id='.$id:'');
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
	
	public function object_list($limit = null, $offset = null) {
		$items = $this->get_objects($limit, $offset);
		echo root()->components->table($items);
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

#############################
###### The Page Object ######
#############################

class CP_Page extends CP_Object {
	
	public function __construct() {
		parent::__construct('CP_Page');
	}
	
	public function title() {
		return 'Pages';
	}
	
	public function finished_loading() {
		//$this->controls->page_save->disable();
	}
	
	/**
	 * Function for the click event tied to the save button.
	 * 
	 * @access public
	 * @param mixed $sender
	 * @return void
	 */
	public function page_save_click($sender) {
		$controls = $this->controls;
		$id = $this->state->page_save_id;
		root()->iface->console("Attempting to save page at ID ($id)");
		$data = [
			'id' => $id,
			'name' => $controls->page_title->val(),
			'meta' => [
				'page_content' => $controls->page_content->val(),
				'date_modified' => date('n/j/Y')
			]
		];
		$result = $this->save($data);
		//$sender->disable();
		if ($result) root()->iface->console('(' . $data['name'] . ') saved successfully.');
		root()->iface->refresh();
	}
	
	/**
	 * Function for the change event on the page_content field of the interface.
	 * 
	 * @access public
	 * @return void
	 */
	public function page_content_change() {
		//$this->controls->page_save->enable();
	}
	
	/**
	 * Function for the change event on the page_title field of the interface.
	 * 
	 * @access public
	 * @param mixed $sender
	 * @param mixed $data
	 * @return void
	 */
	public function page_title_change($sender, $data) {
		$value = $this->controls->page_title->val();
		$this->controls->header_label->val($value);
		$this->controls->page_save->enable();
	}
	
	public function front_end() {
		$item = $this->get_item($_GET['id']);
		echo $item->page_content;
	}
	
	/**
	 * admin function. Echo the admin interface to the screen.
	 * 
	 * @access public
	 * @param bool $id (default: false)
	 * @return void
	 */
	public function admin($id = false) {
		if (empty($_GET['id']) && !$id) {
			parent::admin();
		} else {
			$this->state->page_save_id = $id ?: $_GET['id'];
			$item = $this->get_item($_GET['id']);
			$title_field = new CP_TextField('page_title', $item->name, array('placeholder'=>'Page Title', 'class'=>'form-control'), $this);
			$editor = new CP_Editor('page_content', $item->page_content, array('class'=>'form-control'), $this);
			$button = new CP_Button('page_save', 'Save', array('class'=>'btn btn-block btn-primary'), $this);
			$header = new CP_Label('header_label', $item->name, [], $this);
			?>
			<div class="row">
				<div class="col-sm-9">
					<h2><? $header->display() ?></h2>
					<h4>Title</h4>
					<? $title_field->display() ?>
					<h4>Content</h4>
					<? $editor->display() ?>
				</div>
				<div class="col-sm-3">
					<div class="panel panel-default">
						<div class="panel-body">
							<? $button->display(); ?>
						</div>
					</div>
				</div>
			</div>
			<?
		}
	}
	
	/**
	 * Overrides the parent object_list function. Provides data for which fields to show, and content for custom fields.
	 * 
	 * @access public
	 * @param mixed $limit (default: null)
	 * @param mixed $offset (default: null)
	 * @return void
	 */
	public function object_list($limit = null, $offset = null) {
		$items = $this->get_objects($limit, $offset);
		$columns = [
			'id'=>[],
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
		//echo root()->components->table($items, $columns, $this);
		$table = new CP_Table('pages_list', $items, $columns, ['class'=>'table'], $this);
		$table->display();
	}
	
	public function name_cell_link($row) {
		return '<a href="'.$this->edit_link($row->id).'">'.$row->name.'</a>';
	}
	
	public function control_cell($row) {
		$id = $row->id;
		$this->state->pages[$id] = $row;
		$button = new CP_Button('page_delete', 'Delete', array('class'=>'btn btn-danger', 'id'=>'page_delete_'.$id, 'delete-id'=>$id), $this);
		return $button->control();
	}
	
	public function page_delete_click($sender, $data) {
		$delete_id = $sender->options['delete-id'];
		$name = $sender->owner->state->pages[$delete_id]->name;
		$this->state->delete_page = $delete_id;
		root()->iface->confirm('Are you sure you want to delete page ' . $name, 'delete_page', $this, $sender);
	}
	
	/**
	 * Function called when the confirmation box is clicked, $data will either be 'OK' or 'Cancel'.
	 * 
	 * @access public
	 * @param mixed $sender
	 * @param mixed $data
	 * @return void
	 */
	public function confirm_delete_page_response($sender, $data) {
		$id = $this->state->delete_page;
		if ($data == 'OK') {
			$this->remove($id);
			root()->iface->console("Deleted Page ID: $id");
			$this->controls->pages_list->delete_row($id);
		}
	}
	
}

class Theme_Manager extends CP_Object {
	
	public $is_public = false;
	
	public function __construct() {
		parent::__construct('Theme_Manager');
	}
	
	public function title() {
		return 'Theme Manager';
	}
	
	public function theme_items($limit = null, $offset = null) {
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
		$items = $this->theme_items();
		//echo root()->components->table($items);
		$table = new CP_Table('theme_list', $items, null, ['class'=>'table'], $this);
		$table->display();
	}
	
}