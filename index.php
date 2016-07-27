<?php
	
require_once 'cp-config.php';
require_once 'core/init.php';

root()->authentication->secure();

$part = root()->objects->get_object()->template();

root()->themes->get_theme_part($part ?: 'index');