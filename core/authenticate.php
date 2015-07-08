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
	
	public function secure($force = false) {
		$require = root()->hooks->filter->apply('cp_require_login', CP_REQUIRE_LOGIN);
		root()->hooks->action->perform('cp_login_secure');
		if (($require && !$this->isLoggedIn() && !$force) || ($force && !$this->isLoggedIn())) {
			$url = root()->settings->get('cp_site_url');
			header("Location: $url/login.php");
		}
	}
	
	public function required() {
		return root()->hooks->filter->apply('cp_require_login', CP_REQUIRE_LOGIN);
	}
	
}

class CP_User {
	
	public $username;
	
	public $user;
	
	public function __construct($username = null) {
		
		if ($username) {
			$this->username = $username;
			
			$this->_get();
			return $this->user;
			$properties = get_object_vars($this->user);
		}
		
	}
	
	private function _get() {
		$user_db = root()->db->get_where('users', array('user_name'=>$this->username));
		if ($user_db->num_rows) {
			$this->user = $user_db[0];
		}
	}
	private function _getmeta() {
		
	}
	
}

class CP_Users extends CP_Object {
	
	public function __construct() {
		parent::__construct('CP_Users');
		$this->object_table = 'users';
	}
	
	public function title() {
		return 'Users';
	}
	
	public function get_objects($limit = null, $offset = null) {
		$items = root()->db->get_where('users', '1=1', $limit, $offset);
		$objects = [];
		if (count($items->rows)) {
			foreach ($items->rows as $row) {
				$meta = root()->db->get_where('objectmeta', ['meta_item'=>$row->id, 'meta_object'=>$this->_slug])->rows;
				$meta_array = [];
				if (count($meta)) {
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
	
	public function object_list($limit = null, $offset = null, $order = null) {
		$items = $this->get_objects($limit, $offset, $order);
		$columns = [
			'first_name'=>[
				'display'=>'Name',
				'callback'=>'user_full_name'
			],
			'email'=>[
				'display'=>'Email', 
				'no_sort'=>true,
			], 
			'user_name'=>[
				'display'=>'User Name'
			]
		];
		$table = new CP_Table('user_list', $items, $columns, ['class'=>'table'], $this);
		$table->display();
	}
	
	public function user_full_name($row) {
		return '<a href="'.$this->edit_link($row->id).'">'.$row->first_name . ' ' . $row->middle_name . ' ' . $row->last_name.'</a>';
	}
	
	public function admin() {
		if ($_GET['id'] === 0 || isset($_GET['id'])) {
			$this->state->user_to_save = $_GET['id'];
			$item = $this->get_item($_GET['id']);
			$header = new CP_Label('header_label', $item->first_name . ' ' . $item->middle_name . ' ' . $item->last_name, [], $this);
			$first_name = new CP_TextField('first_name', $item->first_name, ['class'=>'form-control'], $this);
			$middle_name = new CP_TextField('middle_name', $item->middle_name, ['class' => 'form-control'], $this);
			$last_name = new CP_TextField('last_name', $item->last_name, ['class'=>'form-control'], $this);
			$email = new CP_TextField('email_address', $item->email, ['class'=>'form-control', 'type'=>'email'], $this);
			$username = new CP_TextField('user_name', $item->user_name, ['class'=>'form-control'], $this);
			$password = new CP_TextField('password', '', ['class'=>'form-control', 'type'=>'password'], $this);
			$button = new CP_Button('save_user', 'Save', ['class'=>'btn btn-primary'], $this);
			$button->disabled = true;
			
			?>
				<h2><? $header->display() ?></h2>
				<h4>First Name</h4>
				<? $first_name->display() ?>
				<h4>Middle Name</h4>
				<? $middle_name->display() ?>
				<h4>Last Name</h4>
				<? $last_name->display() ?>
				<h4>Email Address</h4>
				<? $email->display() ?>
				<h4>User Name</h4>
				<? $username->display() ?>
				<h4>Password</h4>
				<? $password->display() ?>
				<br/>
				<? $button->display() ?>
			<?
		} else {
			parent::admin();
		}
	}
	
	public function first_name_keyup($sender) {
		$value = $this->controls->first_name->val();
		$middle = $this->controls->middle_name->val();
		$last = $this->controls->last_name->val();
		$this->controls->header_label->val($value . ' ' . $middle . ' ' . $last);
		$this->controls->save_user->enable();
	}
	
	public function last_name_keyup($sender) {
		$this->first_name_keyup($sender);
	}
	
	public function middle_name_keyup($sender) {
		$this->first_name_keyup($sender);
	}
	
	public function email_address_keyup($sender) {
		$this->first_name_keyup($sender);
	}
	
	public function user_name_keyup($sender) {
		$this->first_name_keyup($sender);
	}
	
	public function password_keyup($sender) {
		$this->first_name_keyup($sender);
	}
	
	public function save_user_click($sender) {
		$controls = $this->controls;
		$id = $this->state->user_to_save;
		root()->iface->console("Attempting to save user at ID ($id)");
		$data = [
			'id' => $id,
			'first_name' => $controls->first_name->val(),
			'last_name' => $controls->last_name->val(),
			'email' => $controls->email_address->val(),
			'user_name' => $controls->user_name->val(),
			'pass_word' => md5($controls->password->val()),
			'meta' => [
				'date_modified' => date('n/j/Y'),
				'middle_name' => $controls->middle_name->val()
			]
		];
		$data = root()->hooks->filter->apply('cp_user_save_data', $data);
		$result = $this->save($data);
		$fname = $data['first_name'];
		if ($result) {
			root()->iface->console('(' . $data['first_name'] . ') saved successfully.');
			root()->iface->alert("User '$fname' saved successfully.");
			$this->controls->save_user->disable();
		} else {
			root()->iface->alert("User '$fname' failed to save.");
		}
	}
	
}