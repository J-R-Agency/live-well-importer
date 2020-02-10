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
        if(isset($_POST['api_url'])){
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

				//print_r( file_get_contents("$api_url") );

				// Succesfully loaded?
				if( $data !== FALSE ){
					echo " Is DATA ";
/*				    echo "<pre>";
					print_r( $data["Services"] );
				    echo "<pre>";*/


					// First remove all previous imported posts
					$currentPosts = get_posts( array( 
						'post_type' 		=> 'activities', // Or "page" or some custom post type
						'post_status' 		=> 'publish',
						'meta_key'			=> 'imported', // Our post options to determined
						'posts_per_page'   	=> 1000 // Just to make sure we've got all our posts, the default is just 5
					) );

					// Loop through them
					foreach($currentPosts as $post){

						// Get the featured image id
						if($thumbId = get_post_meta($post->ID,'_thumbnail_id',true)){

							// Remove the featured image
							wp_delete_attachment($thumbId,true);
						}

						// Remove the post
						wp_delete_post( $post->ID, true);
					}

					// Loop through some items in the xml
					$service = $data["Services"] ;
					foreach( $service as $item ){

						print_r( $item["Name"] . " // " . $item["WebsiteUrl"] . " // " . $item["Organisation"] . " <br> " );

						// Let's start with creating the post itself
						$postCreated = array(
							'post_title' 	=> $item["Name"],
							'post_content' 	=> $item["Description"],
							'post_excerpt' 	=> $item["Organisation"],
							'post_status' 	=> 'publish',
							'post_type' 	=> 'activities', // Or "page" or some custom post type
						);

						// Get the increment id from the inserted post
						$postInsertId = wp_insert_post( $postCreated );

						// Our custom post options, for now only some meta's for the
						// Yoast SEO plugin and a "flag" to determined if a
						// post was imported or not
						$postOptions = array(
							'imported'				=> true
						);

						// Loop through the post options
						foreach($postOptions as $key=>$value){

							// Add the post options
							update_post_meta($postInsertId,$key,$value);
						}


						/* UPDATE CUSTOM FIELDS */
						// WARNING FIELD NEEDS TO EXIST AND HAVE DATA BEFORE WE CAN ADD TO IT
						// AND WE NEED TO USE THE FIELD KEY FROM POST META TABLE

						$field_key = get_post_meta( $postInsertId, "_" . strtolower("WebsiteUrl"), true );

						$acf_posts = get_posts( array('post_title' => 'WebsiteUrl') ) ;


						$acf_post = get_page_by_title( "WebsiteUrl", "OBJECT", "acf-field" )

						$field_key = $acf_post["post_name"];
						echo " FIELD KEY: " . $field_key ;
	
						// update_field('field_5e418f9203cbd', $item["WebsiteUrl"], $postInsertId);
						update_field( "$field_key", $item["WebsiteUrl"], $postInsertId);


						// This is a little trick to "catch" the image id
						// Attach/upload the "sideloaded" image
						// And remove the little trick
						// add_action('add_attachment','featuredImageTrick');
						// media_sideload_image($item->image, $postInsertId, $item->title);
						// remove_action('add_attachment','featuredImageTrick');
					}

				} else {
					echo " Not DATA ";
				}
 
 
        }
}

?>