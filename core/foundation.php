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
	
	public function __construct() {
		
	}
	
	public function init() {
		$this->core = new CP_Core();
		$this->db = new DB();
		$this->hooks = new CP_Hooks();
		$this->settings = new CP_Settings();
		$this->themes = new CP_Themes();
		$this->objects = new CP_Objects();
		$this->components = new CP_Components();
		$this->authentication = new CP_Login();
		$this->iface = new CP_Interface();
		
		$this->objects->add('CP_Page');
		$this->objects->add('Theme_Manager');
		$this->objects->add('CP_Users');
		
		$this->hooks->action->perform('init', $this);
		
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