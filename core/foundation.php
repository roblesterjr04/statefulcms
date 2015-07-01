<?
	
class CP_Core {
	public function __construct() {
		
	}
}

// Our foundation object, everything is an object of this.
	
class CP_Foundation {
	
	public $hooks;
	public $db;
	public $settings;
	public $themes;
	public $objects;
	public $authentication;
	public $components;
	public $fields;
	public $core;
	public $update;
	private $type;
	
	public function __construct($type = false) {
		$this->type = $type;
	}
	
	public function init() {
		$this->core = new CP_Core();
		$this->db = new DB();
		$this->hooks = new CP_Hooks();
		$this->settings = new CP_Settings();
		if ($this->type != 'install') $this->themes = new CP_Themes();
		$this->objects = new CP_Objects();
		if ($this->type != 'install') $this->components = new CP_Components();
		$this->authentication = new CP_Login();
		if ($this->type != 'install') $this->iface = new CP_Interface();
		if ($this->type != 'install') $this->update = new CP_Update();
		
		if ($this->type != 'install') $this->themes->init_theme();
		if ($this->type != 'install') $this->objects->init_global();
		
		if ($this->type != 'install') $this->objects->add('CP_Page');
		if ($this->type != 'install') $this->objects->add('Theme_Manager');
		if ($this->type != 'install') $this->objects->add('CP_Users');
		if ($this->type != 'install') $this->objects->add('Update_Control');
		
		if ($this->type != 'install') $this->hooks->action->perform('init', $this);
		return $this;
	}
	
	public function decode($object) {
		return unserialize(base64_decode($object));
	}
	
	public function encode($object) {
		return base64_encode(serialize($object));
	}
	
}

// Function to return the foundation from anywhere.

function root() {
	global $root;
	return $root;
}