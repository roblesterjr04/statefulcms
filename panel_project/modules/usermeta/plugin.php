<?php

$meta_table = new Table('ap_usermeta');

$meta_table->createWithFields(array(
	array("name"=>"userid", "type"=>"bigint", "attr"=>"NOT NULL"),
	array("name"=>"metakey", "type"=>"char(50)", "attr"=>"NOT NULL"),
	array("name"=>"metavalue", "type"=>"text", "attr"=>", PRIMARY KEY(userid, metakey)")
));

function include_user_meta( $user ) {
	global $mysqli;
	if ($mysqli) {
		$stmt = $mysqli->prepare("SELECT metakey, metavalue from ap_usermeta where userid=?");
		$stmt->bind_param("i",$user['id']);
		$stmt->execute();
		$stmt->bind_result($key, $value);
		while($stmt->fetch()) {
			$user[$key] = $value;
		}
		$stmt->close();
	}
	return $user;
}
add_filter('fetchUserDetails', 'include_user_meta');

function meta_admin_page( $content ) {
	$keys = get_user_meta($_GET['id']);
	foreach ($keys as $key=>$value) {
		$content .= static_field($value, $key);
	}
	return $content;
}
add_filter('user_admin_fields', 'meta_admin_page');

function get_user_meta( $id ) {
	global $mysqli;
	$keys = array();
	if ($mysqli) {
		$stmt = $mysqli->prepare("SELECT metakey, metavalue from ap_usermeta where userid=?");
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->bind_result($key, $value);
		while($stmt->fetch()) {
			$keys[$key] = $value;
		}
		$stmt->close();
	}
	if (count($keys) > 0) return $keys;
	else return false;
}
add_action('get_user_meta', 'get_user_meta');

function save_meta( $args ) {
	$key = $args['key'];
	$value = $args['value'];
	$id = $args['id'];
	global $mysqli;
	if ($mysqli) {
		$stmt = $mysqli->prepare("REPLACE INTO ap_usermeta (userid, metakey, metavalue) values(?,?,?)");
		$stmt->bind_param("iss",$id,$key,$value);
		$stmt->execute();
		$stmt->close();
	}
}
add_action('save_meta', 'save_meta');