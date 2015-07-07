<?
	
class Plugins extends CP_Object {
	
	public function __construct() {
		
		parent::__construct('Plugins');
		
	}
	
	public function get_item($slug) {
		$result = root()->db->get_where('object_items', ['name'=>$slug], 1)->rows;
		if (count($result)) {
			$result = $result[0];
			return parent::get_item($result->id);
		}
	}
	
	public function admin() {
		$ajax = new CP_Ajax('plugins_admin', 'plugins_admin_ajax', [], $this);
		$ajax->display()->update();
	}
	
	public function plugins_admin_ajax() {
		parent::admin();
	}
	
	public function object_list($limit = null, $offset = null, $order = null) {
		$items = root()->plugins->plugins;
		
		$columns = [
			'slug' => [
				'display'=>'Plugin',
			],
			'controls' => [
				'display'=>'Activate',
				'callback'=>'activate_plugin',
				'no_sort'=>true
			]
		];
		$table = new CP_Table('plugin_list', $items, $columns, ['class'=>'table'], $this);
		
		$table->display();
		
	}
	
	public function activate_plugin($row) {
		
		$plugin = root()->plugins->plugins[$row->slug];
		
		$active = $plugin->is_active();
		
		$class = $active ? 'btn-danger' : 'btn-default';
		$label = $active ? 'Deactivate' : 'Activate';
		$action = $active ? 'deact' : 'act';
		
		$button = new CP_Button('button_plugin', $label, ['class'=>'btn ' . $class, 'id'=>'button_plugin_'.$row->slug, 'data-id'=>$row->slug, 'data-action'=>$action], $this);
		
		return $button->control();
		
	}
	
	public function button_plugin_click($sender) {
		$plugin = root()->plugins->plugins[$sender->options['data-id']];
		$action = $sender->options['data-action'];
		
		if ($action == 'act') {
			$plugin->activate();
		} else {
			$plugin->deactivate();
		}
				
		root()->iface->refresh();
		
	}
	
}

class CP_Plugins {
	
	public $dir;
	public $plugins = [];
	
	public function __construct() {
		
		return $this;
		
	}
	
	public function init() {
		
		$this->dir = CP_WORKING_DIR . '/plugins/';
		
		$plugins = array_diff(scandir($this->dir), array('.','..'));
		
		if (count($plugins)) {
		
			foreach ($plugins as $plugin) {
				
				$this->plugins[$plugin] = new Plugin($plugin);
				
			}
		
		}
				
	}
	
	public function plugins() {
		
		return $this->plugins;
		
	}
	
}

class Plugin {
	
	public $path;
	public $slug;
	private $plugin;
	public $id;
	
	public function __construct($slug) {
		$this->plugin = root()->objects->get_object('Plugins');
		$this->slug = $slug;
		$path = root()->plugins->dir . $slug . '/' . $slug . '.php';
		$this->path = $path;
		$this->id = $this->is_active();
		if ($this->id && file_exists($path)) {
			require_once($path);
		}
	}
	
	public function activate() {
		$data = [
			'name'=>$this->slug
		];
		
		$this->plugin->save($data);
	}
	
	public function deactivate() {
		$this->plugin->remove($this->id);
		unset(root()->plugins->plugins[$this->slug]);
	}
	
	public function is_active($slug = false) {
		
		$slug = $slug ?: $this->slug;
		
		$item = $this->plugin->get_item($slug);
		
		return $item->id;
		
	}
	
}