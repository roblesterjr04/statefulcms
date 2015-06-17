<?php

global $loggedInUser;
register_setting('dash_board_order','dash_'.$loggedInUser->user_name);

function gridster_panels($panels) {
	if (isset($_POST['gridster_order'])) {
		
		exit();
	}
	$i = 0;
	foreach ($panels as $panel) {
		$gridster = array("id"=>"panel_".$i, "data-slug"=>$panel['slug']);
		$panel['atts'] = $gridster;
		$panels[$i] = $panel;
		$i++;
	}
	return $panels;
}
add_filter('dash_panels', 'gridster_panels');


function gridster_scripts() {
	echo "<script>
		$(function(){ //DOM Ready
		
			/*$('ul.dboard').gridster({
				min_rows: 1,
				max_cols: 12,
				max_size_x: 12,
				max_size_y: 2,
				autogenerate_stylesheet: false
			});*/
			
			$('ul.dboard').sortable({
				stop: function( event, ui ) {
					endSort();
				}
			});
			
			function endSort() {
				var sorted = $( 'ul.dboard' ).sortable( 'serialize' );
				console.log(sorted);
			}
			
		
		});
	</script>";
}
add_action('ap_head', 'gridster_scripts');