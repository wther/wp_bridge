<?php
/**
 * Plugin Name: Bridge Hand Editor
 * Plugin URI: http://webther.net/
 * Description: TinyMCE plugin providing a Hand editor feature
 * Version: 1.13.10.13
 * Author: Ther <bszirmay@gmail.com>
 * Author URI: http://webthernet
 * License: GNU All Permissive License
 */

function bridge_buttonhooks() {
   // Only add hooks when the current user has permissions AND is in Rich Text editor mode
   if ( get_user_option('rich_editing') ) {
     add_filter("mce_external_plugins", "bridge_register_tinymce_javascript");
     add_filter('mce_buttons', 'bridge_register_buttons');
   }
}
 
function bridge_register_buttons($buttons) {
   array_push($buttons, "separator", "edit_hand");
   array_push($buttons, "separator", "insert_spades");
   array_push($buttons, "separator", "insert_hearts");
   array_push($buttons, "separator", "insert_diamonds");
   array_push($buttons, "separator", "insert_clubs");
   return $buttons;
}
 
// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function bridge_register_tinymce_javascript($plugin_array) {
   $plugin_array['bridge'] = plugins_url('/bridge_plugin.js',__file__);
   return $plugin_array;
}
 
/**
 * Replaces the !X tags with <span style="....">&XXXX;</span> HTML code
 */
function replace_suits_with_spans( $content ){
	$replacements = array(
		'!S' => '&spades;',
		'!H' => '<span style="color:red;">&hearts;</span>',
		'!D' => '<span style="color:red;">&diams;</span>',
		'!C' => '&clubs;'
	);
	
	foreach($replacements as $key => $to){
		$content = str_replace($key, $to, $content);
	}
	
	return $content;
}
 
// init process for button control
add_action('init', 'bridge_buttonhooks');
add_filter( 'content_save_pre', 'replace_suits_with_spans', 10, 1 );

?>
