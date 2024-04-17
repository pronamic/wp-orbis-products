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

use Pronamic\WordPress\Money\Money;

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

		\add_filter( 'manage_edit-orbis_product_columns', [ $this, 'manage_edit_columns' ] );

		\add_action( 'manage_posts_custom_column', [ $this, 'manage_posts_custom_column' ], 10, 2 );
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
			'_orbis_product_price'       => FILTER_SANITIZE_STRING,
			'_orbis_product_cost_price'  => FILTER_SANITIZE_STRING,
			'_orbis_product_auto_renew'  => FILTER_VALIDATE_BOOLEAN,
			'_orbis_product_deprecated'  => FILTER_VALIDATE_BOOLEAN,
			'_orbis_product_interval'    => FILTER_SANITIZE_STRING,
			'_orbis_product_description' => FILTER_SANITIZE_STRING,
			'_orbis_product_link'        => FILTER_SANITIZE_STRING,
			'_orbis_product_cancel_note' => FILTER_SANITIZE_STRING,
		];

		$data = filter_input_array( INPUT_POST, $definition );

		foreach ( $data as $key => $value ) {
			if ( '' === $value || null === $value ) {
				\delete_post_meta( $post_id, $key );
			} else {
				\update_post_meta( $post_id, $key, $value );
			}
		}

		$this->sync_to_custom_table( $post_id );
	}

	/**
	 * Sync to custom table.
	 * 
	 * @return void
	 */
	public function sync_to_custom_table( $post_id ) {
		global $wpdb;

		$orbis_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_products WHERE post_id = %d;", $post_id ) );

		$price      = get_post_meta( $post_id, '_orbis_product_price', true );
		$cost_price = get_post_meta( $post_id, '_orbis_product_cost_price', true );
		$auto_renew = get_post_meta( $post_id, '_orbis_product_auto_renew', true );
		$deprecated = get_post_meta( $post_id, '_orbis_product_deprecated', true );
		$interval   = get_post_meta( $post_id, '_orbis_product_interval', true );

		$data = [];
		$form = [];

		$data['name'] = get_the_title( $post_id );
		$form['name'] = '%s';

		if ( ! empty( $price ) ) {
			$data['price'] = $price;
			$form['price'] = '%s';
		}

		if ( ! empty( $cost_price ) ) {
			$data['cost_price'] = $cost_price;
			$form['cost_price'] = '%s';
		}

		$data['auto_renew'] = $auto_renew;
		$form['auto_renew'] = '%d';

		$data['deprecated'] = $deprecated;
		$form['deprecated'] = '%d';

		$data['interval'] = $interval;
		$form['interval'] = '%s';

		if ( empty( $orbis_id ) ) {
			$data['post_id'] = $post_id;
			$form['post_id'] = '%d';

			$result = $wpdb->insert( $wpdb->orbis_products, $data, $form );

			if ( false !== $result ) {
				$orbis_id = $wpdb->insert_id;
			}
		} else {
			$result = $wpdb->update(
				$wpdb->orbis_products,
				$data,
				[ 'id' => $orbis_id ],
				$form,
				[ '%d' ]
			);
		}

		update_post_meta( $post_id, '_orbis_product_id', $orbis_id );
	}

	/**
	 * Manage edit columns.
	 * 
	 * @param array $columns Columns.
	 * @return array
	 */
	public function manage_edit_columns( $columns ) {
		$columns['orbis_product_price']      = __( 'Price', 'orbis-products' );
		$columns['orbis_product_cost_price'] = __( 'Cost Price', 'orbis-products' );
		$columns['orbis_product_deprecated'] = __( 'Deprecated', 'orbis-products' );
		$columns['orbis_product_id']         = __( 'Orbis ID', 'orbis-products' );

		$new_columns = [];

		foreach ( $columns as $name => $label ) {
			if ( 'author' === $name ) {
				$new_columns['orbis_product_price']      = $columns['orbis_product_price'];
				$new_columns['orbis_product_cost_price'] = $columns['orbis_product_cost_price'];
				$new_columns['orbis_product_deprecated'] = $columns['orbis_product_deprecated'];
				$new_columns['orbis_product_id']         = $columns['orbis_product_id'];
			}

			$new_columns[ $name ] = $label;
		}

		$columns = $new_columns;

		return $columns;
	}

	/**
	 * Manage posts custom column.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public function manage_posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'orbis_product_id':
				$id = get_post_meta( $post_id, '_orbis_product_id', true );

				if ( empty( $id ) ) {
					echo '—';
				} else {
					$url = sprintf( 'http://orbis.pronamic.nl/projecten/details/%s/', $id );

					printf( '<a href="%s" target="_blank">%s</a>', esc_attr( $url ), esc_html( $id ) );
				}

				break;
			case 'orbis_product_price':
				$price = get_post_meta( $post_id, '_orbis_product_price', true );

				if ( empty( $price ) ) {
					echo '—';
				} else {
					$price = new Money( $price, 'EUR' );
					echo esc_html( $price->format_i18n() );
				}

				break;
			case 'orbis_product_cost_price':
				$price = get_post_meta( $post_id, '_orbis_product_cost_price', true );

				if ( empty( $price ) ) {
					echo '—';
				} else {
					$price = new Money( $price, 'EUR' );
					echo esc_html( $price->format_i18n() );
				}

				break;
			case 'orbis_product_deprecated':
				$deprecated = get_post_meta( $post_id, '_orbis_product_deprecated', true );

				if ( '' === $deprecated ) {
					echo '—';
				} else {
					echo esc_html( $deprecated ? __( 'Yes', 'orbis-products' ) : __( 'No', 'orbis-products' ) );
				}

				break;
		}
	}
}
