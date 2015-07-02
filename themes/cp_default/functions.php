<?php
	
root()->objects->add('default_theme');
	
function cp_def_head() {
	root()->components->jquery();
	root()->components->state_script();
	echo '<link rel="stylesheet" href="'.root()->themes->get_theme_url().'/bootstrap/css/bootstrap.min.css" type="text/css" />';
	echo '<link rel="stylesheet" href="'.root()->themes->get_theme_url().'/bootstrap/css/bootstrap-theme.min.css" type="text/css" />';
	echo '<style type="text/css">.login .notice { margin-top: 35% } </style>';
}
root()->hooks->action->add('cp_head', 'cp_def_head');

function cp_theme_require_login($value) {
	$value = root()->settings->get('theme_require_login') === 1;
	return $value;
}
root()->hooks->filter->add('cp_require_login', 'cp_theme_require_login');

/*function cp_notice_template($content, $type) {
	$class = '';
	if ($type == 'error') $class = 'alert-danger';
	return '<div class="alert '.$class.'" role="alert">'.$content.'</div>';
}
root()->hooks->action->add('cp_notice', 'cp_notice_template', 10, 2);*/

class default_theme extends CP_Object {
	
	public $menus = ['site'];
	
	public function __construct() {
		parent::__construct('default_theme');
	}
	
	public function title() {
		return 'Theme Settings';
	}
	
	public function index_button_click($sender) {
		root()->iface->alert('WHY DID YOU CLICK ME?');
	}
	
	public function require_login_change($sender) {
		$checked = $this->controls->require_login->checked();
		root()->settings->set('theme_require_login', $checked);
	}
	
	public function admin() {
		$options = [];
		$require_login_setting = root()->settings->get('theme_require_login');
		if ($require_login_setting == 1) $options['checked'] = 'checked';
		$require_login_box = new CP_Checkbox('require_login', 'Require Login across all front end pages', $options, $this);
		$item = new CP_Select_Option('option');
		$item_2 = new CP_Select_Option('option 2');
		$test_dropdown = new CP_Select('test_dropdown', [$item, $item_2], false, ['class'=>'form-control'], $this);
		$test_radio_1 = new CP_Radio('test_radio_1', 'test_radio_group', 'My Radio 1', ['checked'=>'checked'], $this);
		$test_radio_2 = new CP_Radio('test_radio_2', 'test_radio_group', 'My Radio 2', [], $this);
		$file = new CP_FileUpload('file', 'Upload a file here', '', [], $this);
		?>
			<div class="row">
				<div class="col-sm-6">
					<div class="checkbox">
						<? $require_login_box->display() ?>
					</div>
					<? $test_dropdown->display() ?>
					<? $test_radio_1->display() ?>
					<? $test_radio_2->display() ?>
					<br/>
					<? $file->display() ?>
				</div>
			</div>
		<?
	}
	
}