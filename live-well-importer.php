<?php
/*
Plugin Name: Live Well Importer plugin for Wellbeing Liverpool
Plugin URI: https://www.jnragency.co.uk/
Description: Live Well Importer plugin for Wellbeing Liverpool
Version: 0.1
Author: Greg Macoy
Author URI: https://www.jnragency.co.uk/
*/


add_action('admin_menu', 'live_well_importer_setup_menu');
 
function live_well_importer_setup_menu(){
        add_menu_page( 'Live Well Importer', 'Live Well Importer', 'manage_options', 'live-well-importer', 'live_well_importer_init' );
}
 
function live_well_importer_init(){
        echo "<h1>Hello World!</h1>";
}
 
?>