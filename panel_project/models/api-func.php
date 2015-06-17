<?php

function api_response( $obj ) {
	if (is_string($obj)) $obj = array($obj);
	header("Content-type: text/json");
	die(json_encode($obj));
}