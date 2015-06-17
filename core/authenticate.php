<?php
	
class CP_Login {
	
	public function __construct() {
		
	}
	
	public function wait() {
		unset($_SESSION['currentUser']);
		if (isset($_POST['username']) && isset($_POST['password'])) {
			
			$this->login($_POST['username'], $_POST['password']);
			
			root()->hooks->action->perform('cp_auth_wait');
		}
	}
	
	public function validate($user, $pass) {
		$pass = md5($pass);
		
		$invalid = 'Invalid username or password.';
		
		if (!self::isUser($user)) {
			CP_Notice::add('login', $invalid, 'error');
			return false;
		}
		$result = root()->db->get_where('users', array('user_name'=>$user, 'pass_word'=>$pass));
		
		if (!$result->num_rows) {
			CP_Notice::add('login', $invalid, 'error');
			return false;
		}
		
		return true;
		
	}
	
	public function isUser($user) {
		$result = root()->db->get_where('users', array('user_name'=>$user));
		if ($result->num_rows) return true;
		else return false;
	}
	
	public function login($user, $pass) {
		$valid = $this->validate($_POST['username'], $_POST['password']);
		
		if ($valid) {
			
			$user = new CP_User($user);
			
			$_SESSION['currentUser'] = $user;
			
			header('Location: index.php');
			
		}
		
	}
	
	public function isLoggedIn() {
		if (isset($_SESSION['currentUser'])) {
			return true;
		} else {
			return false;
		}
	}
	
	public function secure() {
		root()->hooks->action->perform('cp_login_secure');
		if (CP_REQUIRE_LOGIN && !$this->isLoggedIn()) {
			header("Location: /login.php");
		}
	}
	
}

class CP_User {
	
	public $username;
	
	public $user;
	
	public function __construct($username = null) {
		
		if ($username) {
			$this->username = $username;
			
			$this->_get();
			return $this;
		}
		
	}
	
	private function _get() {
		$user_db = root()->db->get_where('users', array('user_name'=>$this->username));
		if ($user_db->num_rows) {
			$this->user = $user_db;
		}
	}
	private function _getmeta() {
		
	}
	
}

class CP_Users extends CP_Object {
	
	public function __construct() {
		parent::__construct('CP_Users');
	}
	
	public function title() {
		return 'Users';
	}
	
	public function get_objects($limit = null, $offset = null) {
		$items = root()->db->get('users', $limit, $offset);
		return $items->rows;
	}
	
	public function object_list($limit = null, $offset = null) {
		$items = $this->get_objects($limit, $offset);
		$keys = ['email'=>['display'=>'Email', 'no_sort'=>true], 'user_name'=>['display'=>'User Name']];
		echo root()->components->table($items, $keys);
	}
	
}