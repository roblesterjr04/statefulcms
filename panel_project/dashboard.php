<?php

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
} 
//require_once("models/header.php");


include 'themes/default/header.php';
echo '<div class="col-xs-12"><ul class="dboard">';
include 'themes/default/dashboard.php';
echo '</ul></div>';
include 'themes/default/footer.php';