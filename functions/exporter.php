<?php
	
	    

function ccsve_export(){
	$ccsve_export_check = isset($_REQUEST['export']) ? $_REQUEST['export'] : '';
	if ($ccsve_export_check == 'yes') {          
		echo ccsve_generate();
	exit;
	}
}

function ccsve_generate(){

	// Get the custom post type that is being exported
	$ccsve_generate_post_type = get_option('ccsve_post_type');
	// Get the custom fields (for the custom post type) that are being exported
	$ccsve_generate_custom_fields = get_option('ccsve_custom_fields');
        $ccsve_generate_std_fields = get_option('ccsve_std_fields');
        $ccsve_generate_tax_terms = get_option('ccsve_tax_terms');
	// Query the DB for all instances of the custom post type
	$ccsve_generate_query = get_posts(array('post_type' => $ccsve_generate_post_type, 'post_status' => 'publish', 'posts_per_page' => -1));
	// Count the number of instances of the custom post type
	$ccsve_count_posts = count($ccsve_generate_query);	
	
	// Build an array of the custom field values
	$ccsve_generate_value_arr = array();
	$i = 0; 
	foreach ($ccsve_generate_query as $post): setup_postdata($post);	
                  // get the standard wordpress fields for each instance of the custom post type 
                  foreach($post as $key => $value) {
                      if(in_array($key, $ccsve_generate_std_fields['selectinput'])) {
                          $ccsve_generate_value_arr[$key][$i] = $post->$key;
                      }
                  }
                  // get the custom field values for each instance of the custom post type 
		  $ccsve_generate_post_values = get_post_custom($post->ID);		  
		  foreach ($ccsve_generate_custom_fields['selectinput'] as $key) {
		  	 // check if each custom field value matches a custom field that is being exported
			  if (array_key_exists($key, $ccsve_generate_post_values)) {
			  	// if the the custom fields match, save them to the array of custom field values
				 $ccsve_generate_value_arr[$key][$i] = $ccsve_generate_post_values[$key]['0'];
				 
			  } 
		  
		  }
		 // get custom taxonomy information
                 foreach($ccsve_generate_tax_terms['selectinput'] as $tax) {
                     $names = array();
                     $terms = wp_get_object_terms($post->ID, $tax);
                     foreach($terms as $t) {
                        $names[] = $t->name;
                     }
                     $ccsve_generate_value_arr[$tax][$i] = implode(',', $names);
                 }
		$i++;
		 
	endforeach;	
	// create a new array of values that reorganizes them in a new multidimensional array where each sub-array contains all of the values for one custom post instance
	$ccsve_generate_value_arr_new = array();
	
	foreach($ccsve_generate_value_arr as $value) {
		   $i = 0;
		   while ($i <= ($ccsve_count_posts-1)) {
			 $ccsve_generate_value_arr_new[$i][] = $value[$i];
			$i++;
		}
	}

	// build a filename based on the post type and the data/time
	$ccsve_generate_csv_filename = $ccsve_generate_post_type.'-'.date('Ymd_His').'-export.csv';
	
	//output the headers for the CSV file
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header('Content-Description: File Transfer');
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename={$ccsve_generate_csv_filename}");
	header("Expires: 0");
	header("Pragma: public");
 
	//open the file stream
	$fh = @fopen( 'php://output', 'w' );
	
	$headerDisplayed = false;
 
	foreach ( $ccsve_generate_value_arr_new as $data ) {
    // Add a header row if it hasn't been added yet -- using custom field keys from first array
    if ( !$headerDisplayed ) {
        fputcsv($fh, array_keys($ccsve_generate_value_arr));
        $headerDisplayed = true;
    }
 
    // Put the data from the new multi-dimensional array into the stream
    fputcsv($fh, $data);
}
// Close the file stream
fclose($fh);
// Make sure nothing else is sent, our file is done
exit;
	}
