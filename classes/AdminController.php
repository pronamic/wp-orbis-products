<?php
/**
 * Admin controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

namespace Pronamic\Orbis\Products;

/**
 * Admin controller class
 */
class AdminController {
	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		\add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
	}

	/**
	 * Add meta boxes.
	 * 
	 * @return void
	 */
	public function add_meta_boxes() {
		\add_meta_box(
			'orbis_product_details',
			\__( 'Product Details', 'orbis-products' ),
			function () {
				include __DIR__ . '/../admin/meta-box-product-details.php';
			},
			'orbis_product',
			'normal',
			'high'
		);
	}

	/**
	 * Save product details.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = filter_input( INPUT_POST, 'orbis_product_details_meta_box_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'orbis_save_product_details' ) ) {
			return;
		}

		if ( ! ( 'orbis_product' === $post->post_type && current_user_can( 'edit_post', $post_id ) ) ) {
			return;
		}

		$definition = [
			'_orbis_product_price' => [
				'filter'  => FILTER_VALIDATE_FLOAT,
				'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
				'options' => [ 'decimal' => ',' ],
			],
		];

		$data = filter_input_array( INPUT_POST, $definition );

		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
