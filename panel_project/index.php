<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/


require_once("models/config.php");
//echo basename(__FILE__);
if (!securePage(__FILE__)){die('No permissions.');}
global $theme_path;
global $page;
if (empty($_GET['page'])) {
	include($theme_path . '/index.php');
} else {
	$page = $_GET['page'];
	include($theme_path . '/page.php');
}

