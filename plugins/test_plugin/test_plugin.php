<?

function test_plugin_right_menu() {
	$item = new CP_Menu_Item('tp_right_top', 'Login', ['class'=>'dropdown'], root()->objects->get_object('test_plugin_menus'));
	return $item->control();
}
root()->hooks->filter->add('navbar_right_menu_items', 'test_plugin_right_menu');

function test_plugin_right_menu_dropdown() {
	$menu = new CP_Menu('tp_dropdown', ['class'=>'dropdown-menu'], root()->objects->get_object('test_plugin_menus'));
	return $menu->control();
}
root()->hooks->filter->add('tp_right_top_submenu', 'test_plugin_right_menu_dropdown');

function test_plugin_right_menu_items() {
	$object = root()->objects->get_object();
	if ($object) {
		$item = new CP_Menu_Item('tp_item', 'Click', [], $object);
		return $item->control();
	}
}
root()->hooks->filter->add('tp_dropdown_menu_items', 'test_plugin_right_menu_items');

root()->objects->add('test_plugin_menus');

class test_plugin_menus extends CP_Object {
	
	
	
}