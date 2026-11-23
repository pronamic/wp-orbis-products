<?php
/**
 * Plugin
 *
 * @author    Pronamic
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

declare(strict_types=1);

namespace Pronamic\Orbis\Products;

/**
 * Plugin class
 */
final class Plugin {
	/**
	 * Instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Return instance of this class.
	 *
	 * @param string $plugin_file The plugin file.
	 * @return self A single instance of this class.
	 */
	public static function instance( string $plugin_file ): self {
		self::$instance ??= new self( $plugin_file );

		return self::$instance;
	}

	/**
	 * Construct.
	 *
	 * @param string $plugin_file The plugin file.
	 */
	private function __construct(
		/**
		 * Plugin file.
		 */
		private readonly string $plugin_file
	) {
		\register_activation_hook( $this->plugin_file, $this->activate( ... ) );

		\add_action( 'init', $this->init( ... ), 0 );

		new TemplateController();

		( new AbilitiesController() )->setup();

		if ( \is_admin() ) {
			new AdminController();
		}
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	private function init(): void {
		global $wpdb;

		/**
		 * WordPress database abstraction object.
		 *
		 * @var \wpdb $wpdb
		 */

		$wpdb->orbis_products = $wpdb->prefix . 'orbis_products';

		$version = '1.0.0';

		if ( \get_option( 'orbis_products_db_version' ) !== $version ) {
			$this->install();

			\update_option( 'orbis_products_db_version', $version );
		}

		\register_post_type(
			'orbis_product',
			[
				'label'         => \__( 'Products', 'orbis-products' ),
				'labels'        => [
					'name'               => \_x( 'Products', 'post type general name', 'orbis-products' ),
					'singular_name'      => \_x( 'Product', 'post type singular name', 'orbis-products' ),
					'add_new'            => \__( 'Add New', 'orbis-products' ),
					'add_new_item'       => \__( 'Add New Product', 'orbis-products' ),
					'edit_item'          => \__( 'Edit Product', 'orbis-products' ),
					'new_item'           => \__( 'New Product', 'orbis-products' ),
					'view_item'          => \__( 'View Product', 'orbis-products' ),
					'search_items'       => \__( 'Search Products', 'orbis-products' ),
					'not_found'          => \__( 'No products found', 'orbis-products' ),
					'not_found_in_trash' => \__( 'No products found in Trash', 'orbis-products' ),
					'parent_item_colon'  => \__( 'Parent Products:', 'orbis-products' ),
					'menu_name'          => \__( 'Products', 'orbis-products' ),
				],
				'public'        => true,
				'menu_position' => 30,
				'menu_icon'     => 'dashicons-archive',
				'supports'      => [ 'title', 'editor', 'author', 'comments' ],
				'has_archive'   => true,
				'rewrite'       => [
					'slug' => \_x( 'products', 'slug', 'orbis-products' ),
				],
			]
		);

		\register_post_type(
			'orbis_product_change',
			[
				'label'         => \__( 'Product Changes', 'orbis-products' ),
				'labels'        => [
					'name'               => \_x( 'Product Changes', 'post type general name', 'orbis-products' ),
					'singular_name'      => \_x( 'Product Change', 'post type singular name', 'orbis-products' ),
					'add_new'            => \__( 'Add New', 'orbis-products' ),
					'add_new_item'       => \__( 'Add New Product Change', 'orbis-products' ),
					'edit_item'          => \__( 'Edit Product Change', 'orbis-products' ),
					'new_item'           => \__( 'New Product Change', 'orbis-products' ),
					'view_item'          => \__( 'View Product Change', 'orbis-products' ),
					'search_items'       => \__( 'Search Product Changes', 'orbis-products' ),
					'not_found'          => \__( 'No product changes found', 'orbis-products' ),
					'not_found_in_trash' => \__( 'No product changes found in Trash', 'orbis-products' ),
					'parent_item_colon'  => \__( 'Parent Product Changes:', 'orbis-products' ),
					'menu_name'          => \__( 'Product Changes', 'orbis-products' ),
				],
				'public'        => true,
				'menu_position' => 30,
				'menu_icon'     => 'dashicons-admin-settings',
				'supports'      => [ 'title', 'editor', 'author', 'comments' ],
				'has_archive'   => true,
				'rewrite'       => [
					'slug' => \_x( 'product-changes', 'slug', 'orbis-products' ),
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
	private function install(): void {
		global $wpdb;

		/**
		 * WordPress database abstraction object.
		 *
		 * @var \wpdb $wpdb
		 */

		$table = $wpdb->prefix . 'orbis_products';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = <<<SQL
			CREATE TABLE $table (
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
			SQL;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		\dbDelta( $sql );

		\maybe_convert_table_to_utf8mb4( $table );
	}

	/**
	 * Activate.
	 *
	 * @return void
	 */
	private function activate(): void {
		\delete_option( 'rewrite_rules' );
	}
}
