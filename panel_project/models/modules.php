<?php

$plugin_pages = array();
$dash_panels = array();
$last_note = NULL;
GLOBAL $last_note;
define('NOTE_TYPE_ERROR',1);
define('NOTE_TYPE_SUCCESS',0);

$module_settings = array();
GLOBAL $module_settings;
$module_scripts = array();
GLOBAL $module_scripts;
$hooks = array();
GLOBAL $hooks;
$filters = array();
GLOBAL $filters;
$shortcodes = array();
GLOBAL $shortcodes;
$modal_boxes = array();
GLOBAL $modal_boxes;
$module_plugins = array();
GLOBAL $module_plugins;

setFirstPost();

//GLOBAL $plugin_pages;
//GLOBAL $dash_panels;

function register_permission($permission) {
	if (function_exists('permissionNameExists')) {
		if (!permissionNameExists($permission)) createPermission($permission);
		perform_actions('register_permission',array($permission));
	}
}

function load_theme() {
	$themename = get_setting('current_theme', 'ap_core');
	global $theme_path;
	$theme_path = 'themes/'.$themename;
	include($theme_path . '/functions.php');
	perform_actions('load_theme', $themename);
}

function get_dashboards() {
	$dashboards = apply_filter('dash_panels', $GLOBALS['dash_panels']);
	foreach ($dashboards as $panel) {
		$permission = $panel['permission'];
		$attribs = "";
		$atts = $panel['atts'];
		if ($atts) {
			foreach ($atts as $k=>$v) {
				$attribs .= $k . '="'.$v.'" ';
			}
		}
		if (!$permission || has_permission($permission)) {
			echo '<li class="col-sm-' . $panel['width'] . ' ' . $panel['classes'] . '" ' . $attribs . '><div class="panel panel-default">';
			echo '<div class="panel-heading"><h3 class="panel-title">' . $panel['name'] . '</h3></div>';
			echo '<div class="panel-body">';
			call_user_func_array($panel['slug'], array($panel));
			echo '</div></div>';
			echo '</li>';
		}
	}
}

function pluginPath( $file ) {
	return str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($file));
}

function add_page($menu_title, $title, $content, $permission = null, $parent = null) { //plugin method to add pages
	$page = array(
		"slug" => $content,
		"name" => $menu_title,
		"title" => $title,
		"permission" => $permission
	);
	if ($parent) $page['parent'] = $parent;
	$GLOBALS['plugin_pages'][$content] = $page;
	perform_actions('add_page', array($page));
}

function input_text_field($name, $label = '', $placeholder = '', $value = '', $password = false, $echo = false, $autocomplete = true) {
	$parms = array("name"=>$name,"label"=>$label,"placeholder"=>$placeholder,"value"=>$value);
	$type = $password ? 'password' : 'text';
	$output = "<div class=\"form-group\">
		<label class=\"col-md-4 control-label\">";
	if ($label != '') $output .= $label; else $output .= $name;
	$value = $value != '' ? $value : $_REQUEST[$name];
	$output .= "</label>
		<div class=\"col-md-8\"><input class=\"form-control\" type=\"$type\" name=\"$name\" value=\"$value\" placeholder=\"$placeholder\" ".($autocomplete ? '' : 'autocomplete="off"')." /></div></div>";
	$output = apply_filter('input_text_field',$output,$parms);
	$output = apply_filter('input_text_field_'.$name,$output,$parms);
	if ($echo) echo $output; else return $output;
}

function input_text_area($name, $label = '', $placeholder = '', $value = '', $echo = false, $vertical = false) {
	$parms = array("name"=>$name,"label"=>$label,"placeholder"=>$placeholder,"value"=>$value);
	$output = "<div class=\"form-group\">
		<label" . ($vertical ? '' : " class=\"col-md-4 control-label\"") . ">";
	$output .= $label != '' ? $label : $name;
	$output .= "</label>";
	$output .= $vertical ? '' : "<div class=\"col-md-8\">";
	$output .= "<textarea rows='3' class='form-control' name='$name' placeholder='$placeholder'>";
	$output .= $value != '' ? $value : $_REQUEST[$name];
	$output .= '</textarea>';
	$output .= $vertical ? '' : "</div>";
	$output .= "</div>";
	$output = apply_filter('input_text_area', $output, $parms);
	$output = apply_filter('input_text_area_'.$name, $output, $parms);
	if ($echo) echo $output; else return $output;
}

function input_file_field($name, $label, $echo = false, $vertical = false) {
	$parms = array("name"=>$name, "label"=>$label);
	$output = "<div class=\"form-group\">
		<label" . ($vertical ? '' : " class=\"col-md-4 control-label\"") . ">";
	$output .= $label . "</label>";
	$output .= $vertical ? '' : "<div class=\"col-md-8\">";
	$output .= "<input type=\"file\" class=\"form-control\" name=\"$name\" />";
	$output .= $vertical ? '' : "</div>";
	$output .= "</div>";
	$output = apply_filter('input_file_field', $output, $parms);
	$output = apply_filter('input_file_field_'.$name, $output, $parms);
	if ($echo) echo $output; else return $output;
}

function input_checkbox_field($name, $value = null, $label = null, $checked = false, $echo = false, $vertical = false, $toggle = false) {
	$parms = array("name"=>$name,"value"=>$value,"label"=>$label,"checked"=>$checked);
	$output = $vertical ? '' : "<div class=\"form-group\">";
	if ($toggle) $output .= '<input type="hidden" value="0" name="'.$name.'" />';
	$output .= $vertical ? '' : "<div class=\"" . apply_filter('input_checkbox_field_class',apply_filter('input_checkbox_field_class_'.$name,'col-md-12')) . "\">";
	$output .= "<div class=\"checkbox\">";
	$checked = $checked ? ' checked' : $_REQUEST[$name];
	$output .= "<label><input type=\"checkbox\" name=\"$name\" id=\"$name\" value=\"" . ($value ?: 1) . "\"" . $checked . "> " . ($label ? $label : $name) . "</label>";
	$output .= $vertical ? '' : "</div></div>";
	$output .= "</div>";
	$output = apply_filter('input_checkbox_field',$output,$parms);
	$output = apply_filter('input_text_field_'.$name,$output,$parms);
	if ($echo) echo $output; else return $output;
}

function input_select_dropdown($name, $options, $value = null, $label = null, $echo = false, $vertical = false, $update_on_change = false) {
	$params = array("name"=>$name,"value"=>$value,"label"=>$label,"options"=>$options);
	$text_options = '';
	$output = '<div class="form-group">';
	$output .= '<label' . (!$vertical ? ' class="col-md-4 control-label"' : '') . '>';
	$output .= $label . '</label>';
	$output .= !$vertical ? '<div class="col-md-8">' : '';
	$output .= '<select class="form-control' . ($update_on_change ? ' ajax-changed' : '') . '" name="' . $name . '">';
	foreach ($options as $option) {
		$text_options .= '<option value="'.(isAssoc($option) && is_array($option) ? $option['value'] : $option).'" ' . ($value && $value == $option['value'] ? 'selected' : '') . '>'.(isAssoc($option) && is_array($option) ? $option['text'] : $option).'</option>';
	}
	$text_options = apply_filter('input_select_dropdown_items',$text_options);
	$output .= $text_options;
	$output .= '</select>';
	$output .= !$vertical ? '</div>' : '';
	$output .= '</div>';
	$output = apply_filter('input_select_dropdown',$output,$params);
	$output = apply_filter('input_select_dropdown_'.$name,$output,$params);
	if ($echo) echo $output; else return $output;
}

function isAssoc($arr)
{
	if (!is_array($arr)) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function submit_button($name, $label, $class = null, $echo = false, $ajax = false, $modal = null) {
	$class = apply_filter('submit_button_class',$class ? $class : 'btn btn-primary');
	$output = '';
	if ($ajax) {
		$class .= ' ajax-save';
		$output = '<input type="hidden" value="'.$label.'" name="'.$name.'" />';
	}
	$output .= ($modal ? "<button" : "<input type=\"submit\"") . " value=\"$label\" style=\"margin-right: 10px;\" class=\"$class\" name=\"$name\"";
	$output .= ($modal ? " data-toggle=\"modal\" data-target=\"#$modal\"" : "") . ($modal ? ">$label</button>" : " />");
	$output = apply_filter('submit_button',$output);
	if ($echo) echo $output; else return $output;
}

function form_wrap($content, $action, $method, $echo = false) {
	$output = "<form action=\"$action\" method=\"$method\" class=\"form-horizontal\" enctype=\"multipart/form-data\">";
	$output .= apply_filter('form_wrap_content',$content);
	$output .= '</form>';
	$output = apply_filter('form_wrap',$output);
	if ($echo) echo $output; else return $output;
}

function static_field($value, $label = '', $name = null, $echo = false, $vertical = false) {
	$parms = array("value"=>$value,"label"=>$label,"name"=>$name);
	$output = "<div class=\"form-group\">";
	$output .= '<label class="control-label' . ($vertical ? '' : ' col-md-4') . '">'.$label.'</label>';
	$output .= $vertical ? '' : '<div class="col-md-8">';
	$output .= '<p class="form-control-static">'.$value.'</p>';
	$output .= $vertical ? '' : '</div>';
	$output .= '</div>';
	$output = apply_filter('static_field',$output,$parms);
	if ($name) $output = apply_filter('static_field_'.$name, $output,$parms);
	if ($echo) echo $output; else return $output;
}

function apply_filter($filter_name, $content, $arr = false, $num = 1) {
	global $filters;
	usort($filters, "cmp_by_optionNumber");
	$user_parms = array($content);
	if (is_array($arr)) $user_parms[] = $arr;
	if ($filters) {
		foreach ($filters as $filter) {
			if ($filter_name == $filter['filter']) {
				$ret = call_user_func_array($filter['action'], $user_parms);
				if (is_bool($arr) && $arr && $num == 1) $content = $ret[0];
				else $content = $ret;
			}
		}
	}
	return $content;
}

function perform_actions($hook_name, $parms = NULL) {
	global $hooks;
	$returnval = null;
	if ($hooks) {
		foreach ($hooks as $hook) {
			if ($hook_name == $hook['hook']) {
				if (isset($parms)) $returnval = call_user_func_array($hook['action'], $parms);
				else $returnval = call_user_func($hook['action']);
				
			}
		}
	}
	return $returnval;
}

function add_action($hook, $func) {
	global $hooks;
	$hook_details = array("hook"=>$hook, "action"=>$func);
	$hooks[] = $hook_details;
}

function add_filter($filter, $func, $priority = 0) {
	global $filters;
	$filter_details = array("filter"=>$filter, "action"=>$func, "priority"=>$priority);
	$filters[] = $filter_details;
}

function cmp_by_optionNumber($a, $b) {
	return $a["priority"] - $b["priority"];
}

function perform_shortcodes($content, $echo = false) {
	global $shortcodes;
	if ($shortcodes) {
		foreach ($shortcodes as $code) {
			if (strpos($content, '['.$code.']')) {
				$mod_content = call_user_func($shortcodes['action']);
				$content = str_replace('['.$code.']', $mod_content, $content);
			}
		}
	}
	if ($echo) echo $content; else return $content;
}

function user_gravatar( $email = NULL ) {
	global $loggedInUser;
	if ($loggedInUser->profile_image) {
		$gstring = "profileimage";
		if ($email) $gstring .= '?email='.$email;
	} else {
		if (!$email) $email = $loggedInUser->email;
		$hash = md5($email);
		$gstring = str_replace(' ','',trim("//www.gravatar.com/avatar/$hash"));
	}
	return $gstring;
}

function add_shortcode($code, $func) {
	global $hooks;
	$hook_details = array("hook"=>$hook, "action"=>$func);
	$hooks[] = $hook_details;
}

function submitbutton($text = 'Save') {
	echo '<button type="submit" class="btn btn-primary">'.$text.'</button>';
}

function alertUser($type, $message) {
	if ($type == 1) {
		echo '<p class="bg-danger alerts" style="padding: 15px;">'.$message.'</p>';
	} else {
		echo '<p class="bg-success alerts" style="padding: 15px;">'.$message.'</p>';
	}
	//$_SESSION['post_returned'] = true;
}

function isPostBack() {
	$pb = $_SESSION['post_returned'];
	if (!isset($pb)) $pb = !empty($_POST);
	return $pb;
}

function isFirstPost() {
	$pb = $_SESSION['post_set'];
	return $pb;
}

function setFirstPost() {
	if ((!empty($_POST) || !empty($_GET)) && !$_SESSION['post_set']) $_SESSION['post_set'] = true;
	else $_SESSION['post_set'] = false;
}

function clearIsPostBack() {
	$_SESSION['post_returned'] = false;
}

function ajax($func, $message = null, $parms = null) {
	if (!$message) $message = 'Please Wait...';
	if ($parms) {
		foreach ($parms as $pk => $pv) {
			if (is_string($pv)) $pv = '\'' . $pv . '\'';
			$func = str_replace('$'.$pk, $pv, $func);
		}
	}
	echo '<div class="ajax-place" data-func="' . htmlentities($func) . '"><p><img src="wait20.gif"/>'.$message.'</p></div>';
}

function add_dash_panel($title, $content, $width = 12, $permission = null, $classes = null, $atts = null) {
	$panel = array(
		"name"=>$title,
		"slug"=>$content,
		"width"=>$width,
		"permission"=>$permission,
		"classes"=>$classes,
		"atts"=>$atts
	);
	$GLOBALS['dash_panels'][] = $panel;
	perform_actions('add_dash_panel', array($panel));
}

function get_user($email=NULL, $username=NULL) {
	global $loggedInUser;
	if ($email) {
		return fetchUserDetails(NULL,NULL,NULL,$email);
	}
	if ($username) {
		return fetchUserDetails($username);
	}
	return $loggedInUser;
}

function save_setting($name, $value, $group) {
	$value = apply_filter('save_setting_' . $name, $value);
	$value = apply_filter('save_setting', $value);
	if (is_object($value)) {
		$value = $value->jsonString();
	}
	perform_actions('pre_save_setting', array('name'=>$name, 'value'=>$value, 'group'=>$group));
	global $mysqli, $module_settings;
	$registered = in_array(array("name"=>$name,"group"=>$group), $module_settings);
	$exists = get_setting($name, $group);
	if ($exists && !$registered) {
		die("The setting could not be saved.");
	} elseif ($registered) {
		$stmt = $mysqli->prepare(
			"INSERT into ap_settings (setting_name, setting_value, setting_group) values('$name', '$value', '$group')
			ON DUPLICATE KEY
			UPDATE setting_value='$value'"
		);
		$stmt->execute();
	} else {
		die("Setting '$name' is not registered. (saving)");
	}
	$stmt->close();
	perform_actions('save_setting', array('name'=>$name, 'value'=>$value, 'group'=>$group));
}

function do_settings($group) {
	perform_actions('pre_do_settings', array($group));
	if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
		global $module_settings;
		if ($module_settings) {
			foreach ($module_settings as $setting) {
				if (isset($_POST[$setting['name']])) {
					$value = $_POST[$setting['name']];
					save_setting($setting['name'], $value, $group);
					$settings_saved = true;
				}
			}
			if ($settings_saved) alertUser(NOTE_TYPE_SUCCESS, 'Settings were saved.');
		}
	}
	$_POST = array();
	perform_actions('do_settings', array($group));
}

function register_setting($name, $group) {
	perform_actions('pre_setting');
	global $module_settings;
	$setting = array('name'=>$name, 'group'=>$group);
	if (in_array($setting, $module_settings)) {
		die("The setting '$name' has already been registered.");
	} else {
		$module_settings[] = $setting;
	}
	perform_actions('setting');
}

function register_modal($name, $title, $function) {
	perform_actions('pre_register_modal');
	global $modal_boxes;
	$modal = array('name' => $name, 'function' => $function, 'title' => $title);
	$modal_boxes[] = $modal;
	perform_actions('register_modal');
}

function registerScript($file) {
	perform_actions('pre_register_script');
	global $module_scripts;
	if (!in_array($file, $module_scripts)) $module_scripts[] = $file;
	perform_actions('register_script');
}

function get_setting($name, $group) {
	global $mysqli, $module_settings;
	$registered = in_array(array("name"=>$name,"group"=>$group), $module_settings);
	if ($mysqli) {
		$stmt = $mysqli->prepare(
			"SELECT setting_value from ap_settings where setting_name='$name' and setting_group='$group'"
		);
		$stmt->execute();
		$stmt->bind_result($setting_value);
		if ($registered) {
			while($stmt->fetch()) {
				$value = $setting_value;
			}
		} else {
			die("Setting '$name' is not registered. (getting)");
		}
		$stmt->close();
		return apply_filter('get_setting_' . $name, $value);
	} else {
		return false;
	}
}

function list_plugin_dirs() {
	$directory = getcwd() . '/modules';
	$dirs = scandir ( $directory );
	return $dirs;
}

function modules_items($echo = false) { 
	$output = '';
	$pages = $GLOBALS['plugin_pages'];
	foreach ($pages as $plugin) {
		if (!$plugin['parent']) $output .= sidebar_item($plugin['title'], 'index.php?page=' . $plugin['slug'], $plugin['permission']);
	}
	$output = apply_filter('module_items',$output);
	perform_actions('module_pages');
	if ($echo) echo $output; else return $output;
}


function fnEncrypt($sValue, $sSecretKey)
{
    return rtrim(
        base64_encode(
            mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                $sSecretKey, $sValue, 
                MCRYPT_MODE_ECB, 
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256, 
                        MCRYPT_MODE_ECB
                    ), 
                    MCRYPT_RAND)
                )
            ), "\0"
        );
}

function fnDecrypt($sValue, $sSecretKey)
{
    return rtrim(
        mcrypt_decrypt(
            MCRYPT_RIJNDAEL_256, 
            $sSecretKey, 
            base64_decode($sValue), 
            MCRYPT_MODE_ECB,
            mcrypt_create_iv(
                mcrypt_get_iv_size(
                    MCRYPT_RIJNDAEL_256,
                    MCRYPT_MODE_ECB
                ), 
                MCRYPT_RAND
            )
        ), "\0"
    );
}

function insert_plugin($plugin) {
	global $mysqli;
	$stmt = $mysqli->prepare("insert ignore into ap_plugins set plugin_name='{$plugin->slug}';");
	$stmt->execute();
	$stmt->close();
}

function plugin_active($plugin) {
	global $mysqli;
	$stmt = $mysqli->prepare("select plugin_active from ap_plugins where plugin_name='{$plugin->slug}';");
	$stmt->execute();
	$active = false;
	$stmt->bind_result($active);
	$stmt->fetch();
	$stmt->close();
	return $active;
}

function get_data_table($table, $fields = null, $row_controls = null, $echo = false) {
	$table_obj = new Table($table, 1);
	$output = '<table class="table">';
	$output .= '<tr>';
	if ($fields && $fields != 'custom') {
		foreach ($fields as $field) {
			$output .= '<th>' . $field['display_name'] . '</th>';
		}
	} elseif ($fields != 'custom') {
		foreach ($table_obj->table_def as $table_field) {
			$output .= '<th>' . $table_field['field'] . '</th>';
		}
	}
	if ($row_controls) {
		foreach ($row_controls as $control) {
			$output .= '<th>' . $control['title'] . '</th>';
		}
	}
	$output .= '</tr>';
	foreach ($table_obj->rows as $row) {
		$output .= '<tr>';
		if ($fields && $fields != 'custom') {
			foreach ($fields as $field) {
				$output .= '<td>' . $row->cells[$field['name']]->value . '</td>';
			}
		} elseif ($fields != 'custom') {
			foreach ($row->cells as $cell) {
				$output .= '<td>' . $cell->value . '</td>';
			}
		}
		if ($row_controls) {
			foreach ($row_controls as $control) {
				$vals = array();
				foreach ($control['params'] as $v) {
					$vals[] = $row->cells[$v]->value;
				}
				$output .= '<td>' . call_user_func_array($control['ui'], $vals) . '</td>';
			}
		}
		$output .= '</tr>';
	}
	$output .= '</table>';
	if ($echo) echo $output;
	else return $output;
}

function activate_plugin($plugin) {
	global $mysqli;
	$stmt = $mysqli->prepare("update ap_plugins set plugin_active=1 where plugin_name='$plugin'");
	$stmt->execute();
	$stmt->close();
}

function deactivate_plugin($plugin) {
	global $mysqli;
	$stmt = $mysqli->prepare("update ap_plugins set plugin_active=0 where plugin_name='$plugin'");
	$stmt->execute();
	$stmt->close();
}

function load_plugins() {
	global $module_plugins;
	perform_actions('pre_plugins_loaded');
	$plugin_dirs = list_plugin_dirs();
	$plugins = array();
	foreach ($plugin_dirs as $dir) {
		if ($dir != '..' && $dir != '.') {
			$dir = '/modules/' . $dir;
			$path = getcwd() . $dir;
			if (file_exists("$path/plugin.json")) {
				$props = file_get_contents("$path/plugin.json");
				$props = json_decode($props);
				$plugins[] = $props;
				$module_plugins[$props->slug] = $props;
				insert_plugin($props);
				$active = plugin_active($props);
				if (file_exists("$path/" . $props->main_file) && $active) {
					require_once("$path/" . $props->main_file);
				}
			}
		}
	}
	define('PLUGINS', serialize($plugins));
	perform_actions('plugins_loaded');
}

function modal($body, $title, $slug, $submit = 'Save Changes', $cancel = 'Cancel') { ?>
	<div class="modal fade" id="<?php echo $slug; ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo $slug; ?>Label" aria-hidden="true">
	<form id="<?php echo $slug; ?>" method="POST" class="form-horizontal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title" id="<?php echo $slug; ?>Label"><?php echo $title; ?></h4>
				</div>
				<div class="modal-body">
					<?php call_user_func($body); ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $cancel; ?></button>
					<input type="submit" class="btn btn-primary" value="<?php echo $submit; ?>" />
				</div>
			</div>
		</div>
	</form>
	</div>
<?php }

function load_modals() {
	perform_actions('pre_load_modals');
	global $modal_boxes;
	foreach ($modal_boxes as $modal) {
		modal($modal['function'], $modal['title'], $modal['name']);
	}
	perform_actions('load_modals');
}

function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

class DataObjects {
	public $collection = array();
	
	public function __construct($details) {
		if ($details == '' || !$details) return $this;
		$this->collection = json_decode($details);
		return $this;
	}
	
	public function jsonString() {
		return json_encode($this->collection);
	}
	
	public function addObject(DataObject $obj) {
		$this->collection[] = $obj;
	}
}

class DataObject {
	
}