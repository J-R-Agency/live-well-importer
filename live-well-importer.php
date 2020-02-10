<?php
/*
Plugin Name: Live Well Importer plugin for Wellbeing Liverpool
Plugin URI: https://www.jnragency.co.uk/
Description: Live Well Importer plugin for Wellbeing Liverpool
Version: 0.1
Author: GM
Author URI: https://www.jnragency.co.uk/
*/


add_action('admin_menu', 'test_plugin_setup_menu');
 
function test_plugin_setup_menu(){
        add_menu_page( 'Test Plugin Page', 'Test Plugin', 'manage_options', 'test-plugin', 'test_init' );
}
 
function test_init(){
        echo "<h1>Hello World!</h1>";
}
 
?>