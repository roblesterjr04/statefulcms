<?php

function add_styles() {
	echo '<link rel="stylesheet" href="' . str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) . '/styles.css" />';
}
add_action('ap_head', 'add_styles');