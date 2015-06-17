<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
require_once("db-settings.php"); //Require DB connection

//Retrieve settings
$stmt = $mysqli->prepare("SELECT id, name, value
	FROM ".$db_table_prefix."configuration");	
$stmt->execute();
$stmt->bind_result($id, $name, $value);

while ($stmt->fetch()){
	$settings[$name] = array('id' => $id, 'name' => $name, 'value' => $value);
}
$stmt->close();

//Set Settings
$emailActivation = $settings['activation']['value'];
$mail_templates_dir = "models/mail-templates/";
$websiteName = $settings['website_name']['value'];
$websiteUrl = $settings['website_url']['value'];
$emailAddress = $settings['email']['value'];
$resend_activation_threshold = $settings['resend_activation_threshold']['value'];
$emailDate = date('dmy');
$language = $settings['language']['value'];
$template = $settings['template']['value'];

$master_account = -1;

$default_hooks = array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
$default_replace = array($websiteName,$websiteUrl,$emailDate);

if (!file_exists($language)) {
	$language = "models/languages/en.php";
}

if(!isset($language)) $language = "models/languages/en.php";

//Pages to require
require_once($language);
require_once("class.mail.php");
require_once("class.user.php");
require_once("class.newuser.php");
require_once("funcs.php");
require_once("themes.php");
require_once("menu.php");
require_once("scripts.php");
require_once("tables.php");
require_once("modules.php");
require_once("cron.php");
require_once("api-func.php");
require_once("options.php");
require_once("admin_users.php");
require_once("plugins.php");

//Register core settings
register_setting('cron_task_run', 'ap_core');
register_setting('cron_php_path', 'ap_core');
register_setting('cron_freq', 'ap_core');
register_setting('site_url', 'ap_core');
register_setting('admin_email', 'ap_core');
register_setting('current_theme', 'ap_core');

define('BASE_URL', get_setting('site_url','ap_core'));
$c = $_SERVER['REQUEST_URI'];
$c = explode('?', $c);
define('CURRENT', $c[0]);
define('VERSION', 1.0);

//session_save_path('./');
session_start();

load_plugins();

perform_actions('init');

load_theme(); //load selected theme

//Global User Object Var
//loggedInUser can be used globally if constructed
if(isset($_SESSION["userCakeUser"]) && is_object($_SESSION["userCakeUser"]))
{
	$loggedInUser = $_SESSION["userCakeUser"];
}

?>
