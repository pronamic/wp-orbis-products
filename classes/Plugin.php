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
}
