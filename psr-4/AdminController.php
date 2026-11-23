<?php
/**
 * Admin controller
 *
 * @author    Pronamic
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

declare(strict_types=1);

namespace Pronamic\Orbis\Products;

use Pronamic\WordPress\Money\Money;

/**
 * Admin controller class
 */
final class AdminController {
	/**
	 * Construct.
	 */
	public function __construct() {
		\add_action( 'add_meta_boxes', $this->add_meta_boxes( ... ) );

		\add_action( 'save_post_orbis_product', $this->save_product( ... ) );

		\add_filter( 'manage_edit-orbis_product_columns', $this->edit_columns( ... ) );

		\add_action( 'manage_orbis_product_posts_custom_column', $this->custom_columns( ... ), 10, 2 );
	}

	/**
	 * Intervals.
	 *
	 * @return array<string, string>
	 */
	public static function get_intervals(): array {
		return [
			'Y'  => \__( 'Yearly', 'orbis-products' ),
			'2Y' => \__( 'Two Yearly', 'orbis-products' ),
			'3Y' => \__( 'Three Yearly', 'orbis-products' ),
			'M'  => \__( 'Monthly', 'orbis-products' ),
			'Q'  => \__( 'Quarterly', 'orbis-products' ),
		];
	}

	/**
	 * Add meta boxes.
	 *
	 * @return void
	 */
	private function add_meta_boxes(): void {
		\add_meta_box(
			'orbis_product_details',
			\__( 'Product Details', 'orbis-products' ),
			$this->meta_box_product_details( ... ),
			'orbis_product',
			'normal',
			'high'
		);
	}

	/**
	 * Meta box product details.
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	private function meta_box_product_details( \WP_Post $post ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- The post is used in the meta box template.
		include __DIR__ . '/../admin/meta-box-product-details.php';
	}

	/**
	 * Save product.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function save_product( int $post_id ): void {
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = \sanitize_text_field( $this->get_input( 'orbis_product_details_meta_box_nonce' ) );

		if ( false === \wp_verify_nonce( $nonce, 'orbis_save_product_details' ) ) {
			return;
		}

		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$price      = \sanitize_text_field( $this->get_input( '_orbis_product_price' ) );
		$cost_price = \sanitize_text_field( $this->get_input( '_orbis_product_cost_price' ) );
		$interval   = \sanitize_text_field( $this->get_input( '_orbis_product_interval' ) );

		$data = [
			'_orbis_product_price'       => \is_numeric( $price ) ? $price : '',
			'_orbis_product_cost_price'  => \is_numeric( $cost_price ) ? $cost_price : '',
			'_orbis_product_auto_renew'  => '' === $this->get_input( '_orbis_product_auto_renew' ) ? '' : '1',
			'_orbis_product_deprecated'  => '' === $this->get_input( '_orbis_product_deprecated' ) ? '' : '1',
			'_orbis_product_interval'    => \array_key_exists( $interval, self::get_intervals() ) ? $interval : '',
			'_orbis_product_description' => \sanitize_textarea_field( $this->get_input( '_orbis_product_description' ) ),
			'_orbis_product_link'        => \sanitize_url( $this->get_input( '_orbis_product_link' ) ),
			'_orbis_product_cancel_note' => \sanitize_textarea_field( $this->get_input( '_orbis_product_cancel_note' ) ),
		];

		foreach ( $data as $key => $value ) {
			if ( '' === $value ) {
				\delete_post_meta( $post_id, $key );
			} else {
				\update_post_meta( $post_id, $key, $value );
			}
		}

		$this->sync_to_custom_table( $post_id );
	}

	/**
	 * Get input.
	 *
	 * Returns the unslashed `$_POST` value, the caller sanitizes it.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	private function get_input( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The nonce is verified and the value is sanitized by the caller.
		$value = $_POST[ $key ] ?? '';

		return \is_string( $value ) ? \wp_unslash( $value ) : '';
	}

	/**
	 * Get meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return string
	 */
	private function get_meta( int $post_id, string $key ): string {
		$value = \get_post_meta( $post_id, $key, true );

		return \is_string( $value ) ? $value : '';
	}

	/**
	 * Sync to custom table.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function sync_to_custom_table( int $post_id ): void {
		global $wpdb;

		/**
		 * WordPress database abstraction object.
		 *
		 * @var \wpdb $wpdb
		 */

		$table = $wpdb->prefix . 'orbis_products';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		$orbis_id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM %i WHERE post_id = %d;', $table, $post_id ) );

		$price      = $this->get_meta( $post_id, '_orbis_product_price' );
		$cost_price = $this->get_meta( $post_id, '_orbis_product_cost_price' );

		$data = [
			'name'       => \get_post_field( 'post_title', $post_id, 'db' ),
			'auto_renew' => '' === $this->get_meta( $post_id, '_orbis_product_auto_renew' ) ? 0 : 1,
			'deprecated' => '' === $this->get_meta( $post_id, '_orbis_product_deprecated' ) ? 0 : 1,
			'interval'   => $this->get_meta( $post_id, '_orbis_product_interval' ),
		];

		$format = [
			'name'       => '%s',
			'auto_renew' => '%d',
			'deprecated' => '%d',
			'interval'   => '%s',
		];

		if ( '' !== $price ) {
			$data['price']   = $price;
			$format['price'] = '%s';
		}

		if ( '' !== $cost_price ) {
			$data['cost_price']   = $cost_price;
			$format['cost_price'] = '%s';
		}

		if ( null === $orbis_id ) {
			$data['post_id']   = $post_id;
			$format['post_id'] = '%d';

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table.
			$result = $wpdb->insert( $table, $data, $format );

			if ( false !== $result ) {
				$orbis_id = (string) $wpdb->insert_id;
			}
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
			$wpdb->update( $table, $data, [ 'id' => $orbis_id ], $format, [ '%d' ] );
		}

		if ( null !== $orbis_id ) {
			\update_post_meta( $post_id, '_orbis_product_id', $orbis_id );
		}
	}

	/**
	 * Edit columns.
	 *
	 * Adds the product columns before the author column.
	 *
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	private function edit_columns( array $columns ): array {
		$product_columns = [
			'orbis_product_price'      => \__( 'Price', 'orbis-products' ),
			'orbis_product_cost_price' => \__( 'Cost Price', 'orbis-products' ),
			'orbis_product_deprecated' => \__( 'Deprecated', 'orbis-products' ),
			'orbis_product_id'         => \__( 'Orbis ID', 'orbis-products' ),
		];

		$position = \array_search( 'author', \array_keys( $columns ), true );

		if ( false === $position ) {
			return $columns + $product_columns;
		}

		return \array_slice( $columns, 0, $position, true ) + $product_columns + \array_slice( $columns, $position, null, true );
	}

	/**
	 * Custom columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	private function custom_columns( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'orbis_product_id':
				$id = $this->get_meta( $post_id, '_orbis_product_id' );

				echo \esc_html( '' === $id ? '—' : $id );

				break;
			case 'orbis_product_price':
				echo \esc_html( $this->format_price( $post_id, '_orbis_product_price' ) );

				break;
			case 'orbis_product_cost_price':
				echo \esc_html( $this->format_price( $post_id, '_orbis_product_cost_price' ) );

				break;
			case 'orbis_product_deprecated':
				$deprecated = $this->get_meta( $post_id, '_orbis_product_deprecated' );

				echo \esc_html( '' === $deprecated ? \__( 'No', 'orbis-products' ) : \__( 'Yes', 'orbis-products' ) );

				break;
		}
	}

	/**
	 * Format price.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return string
	 */
	private function format_price( int $post_id, string $key ): string {
		$price = $this->get_meta( $post_id, $key );

		if ( ! \is_numeric( $price ) ) {
			return '—';
		}

		return ( new Money( $price, 'EUR' ) )->format_i18n();
	}
}
