<?php
/*
Plugin Name: Display All Image Sizes
Description: Displays all sizes of each image, including name, dimensions, and permalink for each size. A major time-saver if you frequently use custom-generated image sizes.
Author: Press Up
Version: 1.0.6
Author URI: http://pressupinc.com/
Text Domain: display-all-image-sizes
*/

add_action( 'admin_enqueue_scripts', 'wpshout_enqueue_display_all_image_sizes_stylesheet' );
function wpshout_enqueue_display_all_image_sizes_stylesheet() {
	wp_enqueue_style('display-all-image-sizes', plugin_dir_url( __FILE__ ) . 'display-all-image-sizes.css');
}

add_filter( 'attachment_fields_to_edit', 'wpshout_display_all_image_sizes', 10, 2 );
function wpshout_display_all_image_sizes( $form_fields, $post ) {
	$size_names = get_intermediate_image_sizes( $post->ID );

	// Returns array of sizes, each containing array of name, link, length, height
	$sizes = wpshout_return_sizes( $size_names, $post->ID );

	if( ! is_array($sizes) ) {
		return $form_fields;
	}

	$size_data_html = wpshout_build_size_data_html($sizes);

	if( ! isset($size_data_html) || $size_data_html == '') {
		return $form_fields;
	}

    $form_fields['sizes'] = array (
        'label' => __( 'Sizes', 'display-all-image-sizes' ),
        'input' => 'html',
		'html' => $size_data_html,
    );

    return $form_fields;
}

/* Return array of size data
* Thanks to Justin Tadlock:
http://justintadlock.com/archives/2011/01/28/linking-to-all-image-sizes-in-wordpress */
function wpshout_return_sizes( $size_names, $post_id ) {

	/* Get the intermediate image sizes and add the full size to the array. */
	if( ! is_array($size_names) ) {
		return false;
	}

	$size_names[] = 'full';

	/* Loop through each of the image sizes. */
	foreach ( $size_names as $size_name ) {

		/* Get the image source, width, height, and whether it's intermediate. */
		$image = wp_get_attachment_image_src( $post_id, $size_name );

		/* Return name, link, width, height */
		if ( !empty( $image ) && ( true == $image[3] || 'full' == $size_name ) ) {
			$size_data[] = array ( 
				'name' => $size_name, 
				'link' => $image[0],
				'width' => $image[1],
				'height' => $image[2]
			);
		}
	}

	/* Return if array */
	if (is_array($size_data) && count($size_data) > 0) {
		return $size_data;
	}
}

/* Build HTML size data string based on array of size data */
function wpshout_build_size_data_html($sizes) {
	if( ! is_array($sizes)) {
		return;
	}

	foreach($sizes as $key => $value) {
		$size_data_html .= $value['name'] . ': ' .
		$value['width'] . 'x' . $value['height'] . '<br>' .
		'<input type="text" value="' . $value['link'] . '" readonly>' .
		'<br><br>';
	}

	return $size_data_html;
}