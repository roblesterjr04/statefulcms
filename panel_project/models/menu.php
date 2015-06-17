<?php
require_once('modules.php');
function get_navigation($echo = true) {
global $loggedInUser;
$output = '<div style="height: 65px;"><a class="navbar-brand" href="dashboard.php" style="padding: 5px;">';
      $output .= apply_filter('site_logo','<img src="' . apply_filter('site_logo_path','logo.png') . '" style="height: 100%;" />');
      $output .= '</a></div>';
	$output .= '<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      ';
     $output .= get_sidebar('nav navbar-nav visible-xs', false);
     $output .= '
      <ul class="nav navbar-nav navbar-right">
      	<li class="dropdown">
      	<a href="#" class="dropdown-toggle" data-toggle="dropdown"><img style="width: 30px; height: 30px; margin-right: 10px;" src="' . user_gravatar() . '"/>' . $loggedInUser->displayname . ' <b class="caret"></b></a>
      	<ul class="dropdown-menu">
            <li><a href="logout">Logout</a></li>
            <li><a href="user_settings">Edit Profile</a></li>
         
          </ul>
      	</li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>';
if ($echo) echo $output; else return $output;
}

function sub_page_nav($slug, $main_page, $echo = true) {
	$output = '';
	$output .= '<ul class="nav nav-tabs">';
	$tabs = '';
	$pages = child_pages($slug);
	if (count($pages) > 0) $tabs = '<li class="active"><a href="#' . $slug . '" data-toggle="tab">' . $main_page['name'] . '</a></li>';
	foreach ($pages as $page) {
		$tabs .= '<li><a href="#' . $page['slug'] . '" data-toggle="tab">' . $page['name'] . '</a></li>';
	}
	$output .= $tabs . '</ul>';
	$output = apply_filter('sub_page_nav', $output);
	if ($tabs != '') {
		if ($echo) echo $output; else return $output;
	}
}

function get_sidebar($classes = 'nav nav-pills nav-stacked', $echo = true) {
	$output = '<ul class="' . $classes . '">';
	//$output .= sidebar_item("Menu");
	//$output .= sidebar_item("Dashboard", "dashboard");
	$output .= modules_items();
	//$output .= sidebar_item("Plugins", "plugins", apply_filter('plugins_security', 'Administrator'));
	//$output .= sidebar_item("Users", "admin_users", apply_filter('admin_users_security','CustomerAdmin'));
	//$output .= sidebar_item("Admin", "options", apply_filter('admin_options_security', 'Administrator'));
	$output .= '</ul>';
	$output = apply_filter('get_sidebar',$output);
	perform_actions('get_sidebar',array($output));
	if ($echo) echo $output; else return $output;
}

function sidebar_item($title, $link = '', $permission = null, $echo = false) {
	$output = '';
	$current = basename($_SERVER['PHP_SELF']);
	if ($_SERVER['QUERY_STRING']) $current .= '?' . $_SERVER['QUERY_STRING'];
	if ($link && (!$permission || has_permission($permission))) {
		$output .= '<li ';
		if (strstr($link, $current)) $output .= 'class="active"';
		$output .= '><a href="' . $link . '">' . $title . '</a></li>';
	} elseif (!$permission || has_permission($permission)) {
		$output .= '<li class="hidden-xs"><h4>' . $title . '</h4></li>';
	}
	$output = apply_filter('sidebar_item',$output);
	if ($echo) echo $output; else return $output;
}




