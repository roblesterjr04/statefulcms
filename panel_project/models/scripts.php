<?php

function ap_head() {
	$b = BASE_URL;
	$output = '<base href="'.$b.'" />';
	$output .= '<meta name="viewport" content="width=device-width, user-scalable=no">';
	$output .= '<title>Account Manager</title>';
	$output .= '<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" media="screen" />';
	$output .= '<link rel="stylesheet" href="css/override.css" media="screen" />';
	$output .= apply_filter( 'favicon', '<link rel="icon" href="favicon.ico" type="image/x-icon" />' );
	$output .= '<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>';
	$output .= '<script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>';
	$output .= '<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.js" type="text/javascript"></script>';
	$output .= '<script src="js/ajax.js" type="text/javascript"></script>';
	echo apply_filter( 'get_header_scripts', $output );
	perform_actions('ap_head');
}