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
                <input type=\"text\" id=\"api_url\" name=\"api_url\"></input>";
        		submit_button('Import');
        echo "</form>";
}


function live_well_importer_handle_post(){
        // First check if the file appears on the _FILES array
        if( isset($_POST['api_xml']) ){
                $api_url = $_POST['api_url'];
 
                echo $api_url;

				// Disable a time limit
				set_time_limit(0);

				// Require some Wordpress core files for processing images
				require_once(ABSPATH . 'wp-admin/includes/media.php');
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				$json_data = file_get_contents($api_url);  
				//convert json object to php associative array
				$data = json_decode($json_data, true);

				// echo "<pre>";
				// print_r(str_replace(chr(10),"<br />",$data));
				// echo "</pre>";

				// Succesfully loaded?
				if( $data !== FALSE ){
					echo " Is DATA ";

					$service = $data["Services"];    

					foreach( $service as $field_name => $field_value ) {
						
						print_r ( $service ) ;

						// if ( is_array( $sanitised_value) ) {
						// 	$sanitised_value = json_encode( $sanitised_value );
						// } else {
						// 	$sanitised_value = "(not array) ".$sanitised_value;
						// }


					}

				} else {
					echo " Not DATA ";
				}
 
 
        }
}

?>