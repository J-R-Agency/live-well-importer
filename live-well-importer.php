<?php
/*
Plugin Name: Live Well Importer plugin for Wellbeing Liverpool
Plugin URI: https://www.jnragency.co.uk/
Description: Live Well Importer plugin for Wellbeing Liverpool
Version: 0.4
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
                <input type=\"text\" id=\"api_url\" name=\"api_url\" value=\"https://www.thelivewelldirectory.com/api/search?apikey=X59WU602uf&Keywords=WLActive\" size=\"100\"></input>";
        		submit_button('Import');
        echo "</form>";
}

function wl_api_create_taxonomies($postInsertId){

	echo " POST ID: $postInsertId " ;

	$terms = array ( 'Active', 'Creative', 'Useful', 'Social', 'Calm' ) ;

	$term_taxonomy_ids = wp_set_post_terms( $postInsertId, $terms, 'themes' );

	if ( is_wp_error( $term_taxonomy_ids ) ) {
		$error_string = $term_taxonomy_ids -> get_error_message();
	    echo "There was an error somewhere and the terms couldn't be set. $error_string" ;
	} else {
	    echo "Success! These categories were added to the post.";
	}

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
						echo "<pre>";
						// print_r( $item["Locations"] );
						// print_r( $item["Logo"] );
						// print_r( $item["AdditionalInformationFields"] );

						foreach ( $item["AdditionalInformationFields"] as $additionalfield ){
							// echo " AI Field Name: ";
							// print_r( $additionalfield["Name"] );
							// echo " AI Field Values: ";
							// print_r( $additionalfield["Values"] );
							// echo implode(",", $additionalfield["Values"]);

							foreach ( $additionalfield["Values"] as $additionalfield_values ){
								//print_r( $additionalfield_values["string"] );
								//echo implode(",", $additionalfield_values["string"]);
							}
							if ( $additionalfield["Name"] == "Wellbeing-API-Cost-bracket" ){
								$wellbeing_api_cost_bracket = implode(",", $additionalfield["Values"]) ;
							}
							if ( $additionalfield["Name"] == "Wellbeing-API-theme" ){
								$wellbeing_api_theme = implode(",", $additionalfield["Values"]) ;
							}
							if ( $additionalfield["Name"] == "Wellbeing-API-days-of-the-week" ){
								$wellbeing_api_days_of_the_week = implode(",", $additionalfield["Values"]) ;
							}
						}												
						echo " Cost: $wellbeing_api_cost_bracket Theme: $wellbeing_api_theme Days: $wellbeing_api_days_of_the_week " ;
						echo "</pre>";


						// API themes
						$wl_api_theme = explode(",", $wellbeing_api_theme);
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


						add_action( 'save_post', 'wl_api_create_taxonomies', 20, 1 );


						//wl_api_create_taxonomies($postInsertId);

						/* UPDATE CUSTOM FIELDS */
						// WARNING FIELD NEEDS TO EXIST AND HAVE DATA BEFORE WE CAN ADD TO IT
						// AND WE NEED TO USE THE FIELD KEY FROM POST META TABLE

						// WebsiteURL
						$field_key = get_post_meta( $postInsertId, "_" . strtolower("WebsiteUrl"), true );
						$acf_posts = get_posts( array('post_title' => 'WebsiteUrl') ) ;
						$acf_post = get_page_by_title( 'WebsiteUrl', OBJECT, 'acf-field' ) ;
						$field_key = $acf_post->post_name;
						echo " FIELD KEY: " . $field_key ;
						// update_field('field_5e418f9203cbd', $item["WebsiteUrl"], $postInsertId);
						update_field( "$field_key", $item["WebsiteUrl"], $postInsertId);

						// Wellbeing-API-Cost-bracket
						$field_key = get_post_meta( $postInsertId, "_" . strtolower("Wellbeing-API-Cost-bracket"), true );
						$acf_posts = get_posts( array('post_title' => 'Wellbeing-API-Cost-bracket') ) ;
						$acf_post = get_page_by_title( 'Wellbeing-API-Cost-bracket', OBJECT, 'acf-field' ) ;
						$field_key = $acf_post->post_name;
						echo " FIELD KEY: " . $field_key ;
						// update_field('field_5e418f9203cbd', $item["Wellbeing-API-Cost-bracket"], $postInsertId);
						$dummy = get_field('$field_key');
						update_field( "$field_key", $wellbeing_api_cost_bracket, $postInsertId);

						// Wellbeing-API-theme
						$field_key = get_post_meta( $postInsertId, "_" . strtolower("Wellbeing-API-theme"), true );
						$acf_posts = get_posts( array('post_title' => 'Wellbeing-API-theme') ) ;
						$acf_post = get_page_by_title( 'Wellbeing-API-theme', OBJECT, 'acf-field' ) ;
						$field_key = $acf_post->post_name;
						echo " FIELD KEY: " . $field_key ;
						// update_field('field_5e418f9203cbd', $item["Wellbeing-API-theme"], $postInsertId);
						update_field( "$field_key", $wellbeing_api_theme, $postInsertId);

						// Wellbeing-API-days-of-the-week
						$field_key = get_post_meta( $postInsertId, "_" . strtolower("Wellbeing-API-days-of-the-week"), true );
						$acf_posts = get_posts( array('post_title' => 'Wellbeing-API-days-of-the-week') ) ;
						$acf_post = get_page_by_title( 'Wellbeing-API-days-of-the-week', OBJECT, 'acf-field' ) ;
						$field_key = $acf_post->post_name;
						echo " FIELD KEY: " . $field_key ;
						// update_field('field_5e418f9203cbd', $item["Wellbeing-API-days-of-the-week"], $postInsertId);
						update_field( "$field_key", $wellbeing_api_days_of_the_week, $postInsertId);

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