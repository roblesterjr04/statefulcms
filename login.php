<?php
	
require_once 'cp-config.php';
require_once 'core/init.php';


root()->authentication->wait();

root()->themes->get_theme_part('login');

