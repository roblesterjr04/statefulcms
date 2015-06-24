<?php
	
function cp_def_head() {
	root()->components->jquery();
	root()->components->state_script();
	echo '<link rel="stylesheet" href="'.root()->themes->get_theme_url().'/bootstrap/css/bootstrap.min.css" type="text/css" />';
	echo '<link rel="stylesheet" href="'.root()->themes->get_theme_url().'/bootstrap/css/bootstrap-theme.min.css" type="text/css" />';
	echo '<style type="text/css">.login .notice { margin-top: 35% } </style>';
}
root()->hooks->action->add('cp_head', 'cp_def_head');

function cp_notice_template($content, $type) {
	$class = '';
	if ($type == 'error') $class = 'alert-danger';
	return '<div class="alert '.$class.'" role="alert">'.$content.'</div>';
}
root()->hooks->action->add('cp_notice', 'cp_notice_template', 10, 2);
