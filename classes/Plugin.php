<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

namespace Pronamic\Orbis\Products;

/**
 * Plugin class
 */
class Plugin {
	/**
	 * Controllers.
	 * 
	 * @var array
	 */
	private $controllers;

	/**
	 * Instance.
	 * 
	 * @var null|self
	 */
	private static $instance = null;

	/**
	 * Instance.
	 * 
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
 
		return self::$instance;
	}

	/**
	 * Construct.
	 */
	private function __construct() {
		$this->controllers = [
			new AdminController(),
			new TemplateController(),
		];
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		foreach ( $this->controllers as $controller ) {
			$controller->setup();
		}
	}

	/**
	 * Initialize.
	 * 
	 * @return void
	 */
	public function init() {
		global $wpdb;

		$wpdb->orbis_products = $wpdb->prefix . 'orbis_products';

		$version = '1.0.0';

		if ( \get_option( 'orbis_products_db_version' ) !== $version ) {
			$this->install();

			\update_option( 'orbis_products_db_version', $version );
		}

		\register_post_type(
			'orbis_product',
			[
				'label'         => __( 'Products', 'orbis-products' ),
				'labels'        => [
					'name'               => _x( 'Products', 'post type general name', 'orbis-products' ),
					'singular_name'      => _x( 'Product', 'post type singular name', 'orbis-products' ),
					'add_new'            => __( 'Add New', 'orbis-products' ),
					'add_new_item'       => __( 'Add New Product', 'orbis-products' ),
					'edit_item'          => __( 'Edit Product', 'orbis-products' ),
					'new_item'           => __( 'New Product', 'orbis-products' ),
					'view_item'          => __( 'View Product', 'orbis-products' ),
					'search_items'       => __( 'Search Products', 'orbis-products' ),
					'not_found'          => __( 'No products found', 'orbis-products' ),
					'not_found_in_trash' => __( 'No products found in Trash', 'orbis-products' ),
					'parent_item_colon'  => __( 'Parent Products:', 'orbis-products' ),
					'menu_name'          => __( 'Products', 'orbis-products' ),
				],
				'public'        => true,
				'menu_position' => 30,
				'menu_icon'     => 'dashicons-archive',
				'supports'      => [ 'title', 'editor', 'author', 'comments' ],
				'has_archive'   => true,
				'rewrite'       => [
					'slug' => _x( 'products', 'slug', 'orbis-products' ),
				],
			]
		);
	}

	/**
	 * Install.
	 * 
	 * @link https://codex.wordpress.org/Creating_Tables_with_Plugins
	 * @return void
	 */
	public function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE $wpdb->orbis_products (
				id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED DEFAULT NULL,
				name VARCHAR(64) NOT NULL,
				price FLOAT NOT NULL,
				cost_price FLOAT NULL,
				notes TEXT NULL,
				legacy_id BIGINT(16) UNSIGNED NULL,
				`type_default` BOOLEAN NOT NULL DEFAULT FALSE,
				twinfield_article VARCHAR(8) NOT NULL,
				auto_renew BOOLEAN NOT NULL DEFAULT TRUE,
				deprecated BOOLEAN NOT NULL DEFAULT FALSE,
				`interval` VARCHAR(2) NOT NULL DEFAULT 'Y',
				time_per_year INT(16) UNSIGNED DEFAULT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_products );
	}
}
