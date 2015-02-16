<?php

/**
 * Render product details.
 */
function orbis_products_render_details() {
	if ( is_singular( 'orbis_product' ) ) {
		global $orbis_products_plugin;

		$orbis_products_plugin->plugin_include( 'templates/product-details.php' );
	}
}

add_action( 'orbis_before_side_content', 'orbis_products_render_details' );
