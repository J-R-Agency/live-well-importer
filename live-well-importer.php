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

	    live_well_importer_handle_post();

        echo "<h1>Live Well Importer</h1>
        <h2>Import data from Live Well API (please use Live Well API URL)</h2>
        <!-- Form to handle the upload - The enctype value here is very important -->
        <form  method=\"post\" enctype=\"multipart/form-data\">
                <input type=\"text\" id=\"api_xml\" name=\"api_xml\"></input>";
        		submit_button('Import');
        echo "</form>";
}


function live_well_importer_handle_post(){
        // First check if the file appears on the _FILES array
        if(isset($_POST['api_xml'])){
                $api_xml = $_POST['api_xml'];
 
                echo $api_xml;

				// Disable a time limit
				set_time_limit(0);

				// Require some Wordpress core files for processing images
				require_once(ABSPATH . 'wp-admin/includes/media.php');
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				// Download and parse the xml
				$xml = simplexml_load_file( file_get_contents("$api_xml") );


				echo " XML Response: " . $xml ;

				print_r( file_get_contents("$api_xml") );

				// Succesfully loaded?
				if($xml !== FALSE){
					echo " Is XML ";
				}else{
					echo " Not XML ";
				}
 
 
        }
}

?>