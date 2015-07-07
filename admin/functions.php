<?
	
$menu_object = root()->objects->get_object('CP_Index');
	
function index_left_menu_submenu() {
	global $menu_object;
	$menu = new CP_Menu('top_item_submenu', ['class'=>'dropdown-menu'], $menu_object);
	return $menu->control();
}
root()->hooks->filter->add('top_item_submenu', 'index_left_menu_submenu');

function index_left_menu_items() {
	$object = root()->objects->get_object('Update_Control');
	return $object->menu();
}
filter('top_item_submenu_menu_items', 'index_left_menu_items');

function index_toolbar_menu() {
	global $menu_object;
	$item = new CP_Menu_Item('site_menu', 'Site', ['class'=>'dropdown'], $menu_object);
	$top_item = new CP_Menu_Item('top_item', 'Tools', ['class'=>'dropdown'], $menu_object);
	$top_item->unbind('click');
	return $item->control() . $top_item->control();
}
filter('navbar_left_menu_items', 'index_toolbar_menu');

function index_toolbar_menu_items() {
	return root()->components->admin_menu('site', 'dropdown-menu', false);
}
filter('site_menu_submenu', 'index_toolbar_menu_items');

class CP_Index extends CP_Object {
	
}