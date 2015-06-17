<?php


$slug = $_REQUEST['mod'];
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
} 
//require_once("models/header.php");
require_once("includes/includes.php");

$page = page_details($slug);

include 'themes/default/header.php';
$children = child_pages($slug);
if (count($children) == 0) call_user_func_array($slug, array($page));
else {
	echo '<div class="col-xs-12">';
	sub_page_nav($slug, $page);
	echo '<div class="tab-content">';
	echo '<div class="tab-pane active" id="' . $slug . '"><div class="row">';
	call_user_func_array($slug, array($page));
	echo '</div></div>';
	foreach ($children as $child) {
		echo '<div class="tab-pane" id="' . $child['slug'] . '"><div class="row">';
		call_user_func_array($child['slug'], array($child));
		echo '</div></div>';
	}
	echo '</div>';
	echo '</div>';
}
include 'themes/default/footer.php';