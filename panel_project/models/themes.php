<?php

function load_header() {
	global $theme_path;
	include($theme_path.'/header.php');
}

function load_footer() {
	global $theme_path;
	include($theme_path.'/footer.php');
}

function page_content($slug = false) {
	global $page;
	if ($slug) $page = page_details($slug);
	$slug = $page;
	$children = child_pages($slug);
	if (count($children) == 0) call_user_func_array($slug, array($page));	
}

function child_pages( $slug ) {
	$pages = $GLOBALS['plugin_pages'];
	$output_pages = array();
	foreach ($pages as $page) {
		if ($page['parent'] == $slug && has_permission($page['permission'])) $output_pages[] = $page;
	}
	return $output_pages;
}

function page_details( $slug ) {
	$pages = $GLOBALS['plugin_pages'];
	$output_pages = array();
	foreach ($pages as $page) {
		if ($page['slug'] == $slug) return $page;
	}
	return false;
}
