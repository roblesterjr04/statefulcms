<?php
	
global $cp_hooks;
	
class CP_Hooks {
	
	public $stack = [];
	public $filter;
	public $action;
	public $notice;
	
	public function __construct() {
		$this->filter = new CP_Filter();
		$this->action = new CP_Action();
		$this->notice = new CP_Notice();
	}
	
	public function add_hook($hook, $type, $callback, $priority, $params, $meta = 0) {
		global $cp_hooks;
		$hook_properties = [
			'callback'=>$callback,
			'priority'=>$priority,
			'params'=>$params,
			'meta'=>$meta
		];
		//$cp_hooks[$type][$hook][] = $hook_properties;		
		$this->stack[$type][$hook][] = $hook_properties;
	}
	
	public function call($params, $type) {
		$cp_hooks = $this->stack;
		$hook = $params[0];
		$params = array_slice($params, 1);
		if (isset($cp_hooks[$type]) && array_key_exists($hook, $cp_hooks[$type])) {
			$hooks = $cp_hooks[$type][$hook];
			foreach ($hooks as $run) {
				$callback = $run['callback'];
				$num_parms = $run['params'];
				$meta = $run['meta'];
				if ($type == 'action') {
					call_user_func_array($callback, array_slice($params, 0, $num_parms));
				} elseif ($type == 'notice') {
					return CP_Filter::apply('cp_notice', $callback, $meta);
				} else {
					return call_user_func_array($callback, array_slice($params, 0, $num_parms));
				}
			}
		} else {
			if ($type == 'filter') {
				return $params[0];
			}
			if ($type == 'notice') {
				return '';
			}
		}
	}
	
}

class CP_Notice extends CP_Hooks {
	
	public function __construct() {
		
	}
	
	public function add($hook, $notice, $type) {
		root()->hooks->add_hook($hook, 'notice', $notice, 0, 0, $type);
	}
	
	public static function get() {
		$params = func_get_args();
		echo root()->hooks->call($params, 'notice');
	}
}
	
class CP_Action extends CP_Hooks {
	
	public function __construct() {
		
	}
	
	public function add($action, $callback, $priority = 10, $params = 0) {
		root()->hooks->add_hook($action, 'action', $callback, $priority, $params);
	}
	
	public function perform() {
		$params = func_get_args();
		root()->hooks->call($params, 'action');
	}
	
}

class CP_Filter extends CP_Hooks {
	
	public function __construct() {
		
	}
	
	public function add($filter, $callback, $priority = 10, $params = 1) {
		root()->hooks->add_hook($filter, 'filter', $callback, $priority, $params);
	}
	
	public static function apply() {
		$params = func_get_args();
		return root()->hooks->call($params, 'filter');
	}
	
}