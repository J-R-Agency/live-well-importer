<?php
/*
Plugin Name: Live Well Importer plugin for Wellbeing Liverpool
Plugin URI: https://www.jnragency.co.uk/
Description: Live Well Importer plugin for Wellbeing Liverpool (match theme version)
Version: v1.42
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
                <input type=\"text\" id=\"api_url\" name=\"api_url\" value=\"https://www.thelivewelldirectory.com/api/search?apikey=X59WU602uf&Keywords=WLActive\" size=\"100\"></input>
                <input type=\"checkbox\" id=\"reset_data\" name=\"reset_data\" value=\"1\"> <label for=\"reset_data\">Reset Data?</label>";
        		submit_button('Import');
        echo "</form>";
}

function wl_api_create_taxonomies($postInsertId, $wl_api_terms, $wl_api_taxonomy){

	$term_taxonomy_ids = wp_set_post_terms( $postInsertId, $wl_api_terms, $wl_api_taxonomy );

	if ( is_wp_error( $term_taxonomy_ids ) ) {
		// $error_string = $term_taxonomy_ids -> get_error_message();
	 //    echo "There was an error somewhere and the terms couldn't be set. $error_string" ;
	} else {
	    // echo "Success! These categories were added to the post.";
	}

}


function live_well_importer_handle_post(){
        // First check if the file appears on the _FILES array
        if(isset($_POST['api_url'])){

            $api_url = $_POST['api_url'];
	 
	    	$api_data[] = "https://www.thelivewelldirectory.com/api/search?apikey=X59WU602uf&Keywords=WLActive";
	    	$api_data[] = "https://www.thelivewelldirectory.com/api/search?apikey=X59WU602uf&Keywords=WLCalm";
	    	$api_data[] = "https://www.thelivewelldirectory.com/api/search?apikey=X59WU602uf&Keywords=WLCreative";
	    	$api_data[] = "https://www.thelivewelldirectory.com/api/search?apikey=X59WU602uf&Keywords=WLSocial";
	    	$api_data[] = "https://www.thelivewelldirectory.com/api/search?apikey=X59WU602uf&Keywords=WLUseful";


	    	$reset_data = $_POST['reset_data']; 

	    	if ( $reset_data ){
	    		$live_well_importer_start = 1 ;
	    	}

			// Disable a time limit
			set_time_limit(0);

			// Require some Wordpress core files for processing images
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');


			foreach ($api_data as $api_url) {
				# code...

				echo "<h2>" . $api_url . "</h2>" ;

			    $json_data = file_get_contents($api_url);  
			    //convert json object to php associative array
			    $data = json_decode($json_data, true);

				//print_r( file_get_contents("$api_url") );

				// Succesfully loaded?
				if( $data !== FALSE ){
					echo "<br> Is DATA ";
/*				    echo "<pre>";
					print_r( $data["Services"] );
				    echo "<pre>";*/

				    if ( $live_well_importer_start ) {

				    echo "<br> RESET DATA ";

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
				} else {

					echo "<br> DO NOT RESET DATA ";

				}
					// Loop through some items in the xml 
					$service = $data["Services"] ;
					foreach( $service as $item ){ 

						// Initialise
						$wl_api_main_address = "" ;
						$wl_api_postcode = "" ;

						print_r( "<br>" . $item["Name"] . " // " . $item["WebsiteUrl"] . " // " . $item["Organisation"] . " <br> " );
						// echo "<pre>";
						// print_r( $item["Locations"] );
						// print_r( $item["Logo"] );
						// print_r( $item["AdditionalInformationFields"] );

						$serialised_documents = serialize($item["Documents"]);
						
						$serialised_images = serialize($item["Images"]);

						$serialised_contacts = serialize($item["Contacts"]);

						// Handle Additional Information (AU) Fields
						$new_ai_row = "<dl>" ;
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
							}elseif ( $additionalfield["Name"] == "Wellbeing-API-theme" ){
								$wellbeing_api_theme = implode(",", $additionalfield["Values"]) ;
							}
							elseif ( $additionalfield["Name"] == "Wellbeing-API-days-of-the-week" ){
								$wellbeing_api_days_of_the_week = implode(",", $additionalfield["Values"]) ;
							}else{
								$new_ai_row .= "<dt>" . $additionalfield["Name"] . "</dt>" ;
								$new_ai_row .= "<dd>" . implode( "&nbsp;",  $additionalfield["Values"] ) . "</dd>" ;
							}
						}		
						$new_ai_row .= "</dl>" ;
/*
						echo "<pre>";
						print_r( $new_ai_row );
						echo "</pre>";
*/
						// echo " Cost: $wellbeing_api_cost_bracket Theme: $wellbeing_api_theme Days: $wellbeing_api_days_of_the_week " ;
						// echo "</pre>";


						// Handle all location, address and postcode fields
						foreach ( $item["Locations"] as $location ){
							// echo " Locations: ";
							// print_r( $location );
							// echo " AI Field Values: ";
							// print_r( $additionalfield["Values"] );
							// echo implode(",", $additionalfield["Values"]);
							 if ( $wl_api_main_address == "" ) {
							 	$wl_api_main_address = $item["Name"] . ", " . $location["AddressLine1"] . ", " . $location["AddressLine2"] . ", " . $location["Postcode"] ;
							 	if ( $location["Postcode"] != "" ) {
							 		$wl_api_postcode_parts = explode(" ", $location["Postcode"] ) ;
							 		if ( $wl_api_postcode_parts[0] != "" ) {
										$wl_api_postcode = $wl_api_postcode_parts[0] ; // Get first half of postcode, e.g. L3
										$wl_api_postcode_expanded = "inc. " . $wl_api_postcode_parts[0] ; // Get first half of postcode, e.g. L3 and prepend with '+'
							 		}
							 	}

							 } else {
							 	// Main address is already set for this entry
							 	// echo " Main Address: " . $wl_api_main_address . " | " ;
							 }

						}	


						// Handle all logo fields
						unset( $wl_api_logo );
						
						foreach ( $item["Logo"] as $logo ){
							$wl_api_logo[] = $logo;
						}
						
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

						if ( get_page_by_title( $postCreated["post_title"], OBJECT, "activities" ) == null ) {

							echo "<br> INSERT ACTIVITY ";




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


								// Create and import filter fields as taxonomies
								//add_action( 'save_post', 'wl_api_create_taxonomies', 20, 2 );
								//Reformat values
								$wl_api_theme = str_replace ( "WL", "", $wellbeing_api_theme ) ;
								wl_api_create_taxonomies ( $postInsertId, $wl_api_theme, "themes" ) ;


								$wl_api_cost_bracket = str_replace ( "WLFREE", "Free", $wellbeing_api_cost_bracket ) ;
								$wl_api_cost_bracket = str_replace ( "WLLowCost", "£ (up to £5)", $wl_api_cost_bracket ) ;
								$wl_api_cost_bracket = str_replace ( "WLMidCost", "££ (£6-£9)", $wl_api_cost_bracket ) ;
								$wl_api_cost_bracket = str_replace ( "WLHighCost", "£££ (£10+)", $wl_api_cost_bracket ) ;
								wl_api_create_taxonomies ( $postInsertId, $wl_api_cost_bracket, "costs" ) ;

								$wl_api_days_of_the_week = str_replace( "WLMonday", "Monday", $wellbeing_api_days_of_the_week ) ;
								$wl_api_days_of_the_week = str_replace( "WLTuesday", "Tuesday", $wl_api_days_of_the_week ) ;
								$wl_api_days_of_the_week = str_replace( "WLWednesday", "Wednesday", $wl_api_days_of_the_week ) ;
								$wl_api_days_of_the_week = str_replace( "WLThursday", "Thursday", $wl_api_days_of_the_week ) ;
								$wl_api_days_of_the_week = str_replace( "WLFriday", "Friday", $wl_api_days_of_the_week ) ;
								$wl_api_days_of_the_week = str_replace( "WLSaturday", "Saturday", $wl_api_days_of_the_week ) ;
								$wl_api_days_of_the_week = str_replace( "WLSunday", "Sunday", $wl_api_days_of_the_week ) ;
								wl_api_create_taxonomies ( $postInsertId, $wl_api_days_of_the_week, "days" ) ;

								wl_api_create_taxonomies ( $postInsertId, $wl_api_postcode, "postcodes" ) ;

								wl_api_create_taxonomies ( $postInsertId, $wl_api_postcode_expanded, "postcodes_expanded" ) ;

								/* UPDATE CUSTOM FIELDS */
								// WARNING FIELD NEEDS TO EXIST AND HAVE DATA BEFORE WE CAN ADD TO IT
								// AND WE NEED TO USE THE FIELD KEY FROM POST META TABLE

								// WebsiteURL
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("WebsiteUrl"), true );
								$acf_posts = get_posts( array('post_title' => 'WebsiteUrl') ) ;
								$acf_post = get_page_by_title( 'WebsiteUrl', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								// update_field('field_5e418f9203cbd', $item["WebsiteUrl"], $postInsertId);
								update_field( "$field_key", $item["WebsiteUrl"], $postInsertId);

								// Wellbeing-API-Cost-bracket
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("Wellbeing-API-Cost-bracket"), true );
								$acf_posts = get_posts( array('post_title' => 'Wellbeing-API-Cost-bracket') ) ;
								$acf_post = get_page_by_title( 'Wellbeing-API-Cost-bracket', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								// update_field('field_5e418f9203cbd', $item["Wellbeing-API-Cost-bracket"], $postInsertId);
								$dummy = get_field('$field_key');
								update_field( "$field_key", $wellbeing_api_cost_bracket, $postInsertId);

								// Wellbeing-API-theme
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("Wellbeing-API-theme"), true );
								$acf_posts = get_posts( array('post_title' => 'Wellbeing-API-theme') ) ;
								$acf_post = get_page_by_title( 'Wellbeing-API-theme', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								// update_field('field_5e418f9203cbd', $item["Wellbeing-API-theme"], $postInsertId);
								update_field( "$field_key", $wellbeing_api_theme, $postInsertId);

								// Wellbeing-API-days-of-the-week
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("Wellbeing-API-days-of-the-week"), true );
								$acf_posts = get_posts( array('post_title' => 'Wellbeing-API-days-of-the-week') ) ;
								$acf_post = get_page_by_title( 'Wellbeing-API-days-of-the-week', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								// update_field('field_5e418f9203cbd', $item["Wellbeing-API-days-of-the-week"], $postInsertId);
								update_field( "$field_key", $wellbeing_api_days_of_the_week, $postInsertId);

								// Additional Information Fields
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("additional_information"), true );
								$acf_posts = get_posts( array('post_title' => 'Additional Information') ) ;
								$acf_post = get_page_by_title( 'Additional Information', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								// update_field('field_5e418f9203cbd', $item["Wellbeing-API-theme"], $postInsertId);
								update_field( "$field_key", $new_ai_row, $postInsertId);


								// Main Address custom field aggregated for Maps API etc.
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("Main_Address"), true );
								$acf_posts = get_posts( array('post_title' => 'Main Address') ) ;
								$acf_post = get_page_by_title( 'Main Address', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								update_field( "$field_key", $wl_api_main_address, $postInsertId);

								// Logo fields
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("Logo_Description"), true );
								$acf_posts = get_posts( array('post_title' => 'Logo Description') ) ;
								$acf_post = get_page_by_title( 'Logo Description', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								update_field( "$field_key", $wl_api_logo[0], $postInsertId);

								$field_key = get_post_meta( $postInsertId, "_" . strtolower("Logo_Url"), true );
								$acf_posts = get_posts( array('post_title' => 'Logo URL') ) ;
								$acf_post = get_page_by_title( 'Logo URL', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								update_field( "$field_key", $wl_api_logo[1], $postInsertId);

								// Activity Documents custom field aggregated for Maps API etc.
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("activity_documents"), true );
								$acf_posts = get_posts( array('post_title' => 'Activity Documents') ) ;
								$acf_post = get_page_by_title( 'Activity Documents', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								update_field( "$field_key", $serialised_documents, $postInsertId);

								// Activity Images custom field aggregated for Maps API etc.
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("activity_images"), true );
								$acf_posts = get_posts( array('post_title' => 'Activity Images') ) ;
								$acf_post = get_page_by_title( 'Activity Images', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								update_field( "$field_key", $serialised_images, $postInsertId);

								// Activity Images custom field aggregated for Maps API etc.
								$field_key = get_post_meta( $postInsertId, "_" . strtolower("contacts"), true );
								$acf_posts = get_posts( array('post_title' => 'Contacts') ) ;
								$acf_post = get_page_by_title( 'Contacts', OBJECT, 'acf-field' ) ;
								$field_key = $acf_post->post_name;
								// echo " FIELD KEY: " . $field_key ;
								update_field( "$field_key", $serialised_contacts, $postInsertId);

								// This is a little trick to "catch" the image id
								// Attach/upload the "sideloaded" image
								// And remove the little trick
								// add_action('add_attachment','featuredImageTrick');
								// media_sideload_image($item->image, $postInsertId, $item->title);
								// remove_action('add_attachment','featuredImageTrick');


					    		// Only reset data on the first run-through
					    		$live_well_importer_start = 0 ;



						} else {

							echo "<br> POST TITLE: " . $postCreated["post_title"] . " ALREADY EXISTS " ;
						}


					}

				} else {
					echo "<br> Not DATA ";
				}
 
 			}
 
        }
        echo "<h2>FINISHED</h2>";
}

?>