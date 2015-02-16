<?php

/**
 * Create post types.
 */
function orbis_products_create_initial_post_types() {
	register_post_type(
		'orbis_product',
		array(
			'label'         => __( 'Products', 'orbis_products' ),
			'labels'        => array(
				'name'               => _x( 'Products', 'post type general name', 'orbis_products' ),
				'singular_name'      => _x( 'Product', 'post type singular name', 'orbis_products' ),
				'add_new'            => __( 'Add New', 'orbis_products' ),
				'add_new_item'       => __( 'Add New Product', 'orbis_products' ),
				'edit_item'          => __( 'Edit Product', 'orbis_products' ),
				'new_item'           => __( 'New Product', 'orbis_products' ),
				'view_item'          => __( 'View Product', 'orbis_products' ),
				'search_items'       => __( 'Search Products', 'orbis_products' ),
				'not_found'          => __( 'No products found', 'orbis_products' ),
				'not_found_in_trash' => __( 'No products found in Trash', 'orbis_products' ),
				'parent_item_colon'  => __( 'Parent Products:', 'orbis_products' ),
				'menu_name'          => __( 'Products', 'orbis_products' ),
			),
			'public'        => true,
			'menu_position' => 30,
			'menu_icon'     => 'dashicons-archive',
			'supports'      => array( 'title', 'editor', 'author', 'comments' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug' => _x( 'products', 'slug', 'orbis_products' ),
			)
		)
	);
}

add_action( 'init', 'orbis_products_create_initial_post_types', 0 ); // highest priority

/**
 * Add meta details meta box.
 */
function orbis_products_add_meta_boxes() {
	add_meta_box(
		'orbis_product_details',
		__( 'Product Details', 'orbis_products' ),
		'orbis_product_details_meta_box',
		'orbis_product',
		'normal',
		'high'
	);
}

add_action( 'add_meta_boxes', 'orbis_products_add_meta_boxes' );

/**
 * Product details meta box
*/
function orbis_product_details_meta_box() {
	global $orbis_products_plugin;

	$orbis_products_plugin->plugin_include( 'admin/meta-box-product-details.php' );
}

/**
 * Save product details
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
function orbis_save_product_details( $post_id, $post ) {
	// Doing autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Verify nonce
	$nonce = filter_input( INPUT_POST, 'orbis_product_details_meta_box_nonce', FILTER_SANITIZE_STRING );
	if ( ! wp_verify_nonce( $nonce, 'orbis_save_product_details' ) ) {
		return;
	}

	// Check permissions
	if ( ! ( $post->post_type == 'orbis_product' && current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	// OK
	$definition = array(
		'_orbis_product_price' => array(
			'filter'  => FILTER_VALIDATE_FLOAT,
			'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
			'options' => array( 'decimal' => ',' ),
		),
	);

	$data = filter_input_array( INPUT_POST, $definition );

	foreach ( $data as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}
}

add_action( 'save_post', 'orbis_save_product_details', 10, 2 );
