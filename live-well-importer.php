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

        echo "<h1>Hello World!</h1>
        <h2>Upload a File</h2>
        <!-- Form to handle the upload - The enctype value here is very important -->
        <form  method=\"post\" enctype=\"multipart/form-data\">
                <input type=\"text\" id=\"api_xml\" name=\"api_xml\"></input>";
        		submit_button('Import');
        echo "</form>";
}


function live_well_importer_handle_post(){
        // First check if the file appears on the _FILES array
        if(isset($_FILES['api_xml'])){
                $pdf = $_FILES['api_xml'];
 
                // Use the wordpress function to upload
                // test_upload_pdf corresponds to the position in the $_FILES array
                // 0 means the content is not associated with any other posts
                $uploaded=media_handle_upload('api_xml', 0);
                // Error checking using WP functions
                if(is_wp_error($uploaded)){
                        echo "Error uploading file: " . $uploaded->get_error_message();
                }else{
                        echo "File upload successful!";
                        //test_convert($uploaded);
                }
 
 
        }
}

?>