<?php
/**
 * Abilities controller
 *
 * @author    Pronamic
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

declare(strict_types=1);

namespace Pronamic\Orbis\Products;

use WP_Error;

/**
 * Abilities controller class
 *
 * @link https://developer.wordpress.org/news/2025/11/introducing-the-wordpress-abilities-api/
 *
 * @phpstan-type ProductRow object{id: string, post_id: string|null, name: string, price: string|null, cost_price: string|null, interval: string, auto_renew: string, deprecated: string, twinfield_article: string, time_per_year: string|null, notes: string|null}
 */
final readonly class AbilitiesController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup(): void {
		\add_action( 'wp_abilities_api_categories_init', $this->register_ability_categories( ... ) );
		\add_action( 'wp_abilities_api_init', $this->register_abilities( ... ) );

		\add_filter( 'orbis_mcp_server_tools', $this->mcp_server_tools( ... ) );
	}

	/**
	 * Add abilities to the Orbis MCP server tools.
	 *
	 * @link https://github.com/pronamic/orbis-mcp-server
	 * @param string[] $tools Ability names.
	 * @return string[]
	 */
	private function mcp_server_tools( $tools ) {
		$tools[] = 'orbis-products/search-products';
		$tools[] = 'orbis-products/get-product';

		return $tools;
	}

	/**
	 * Register ability categories.
	 *
	 * @return void
	 */
	private function register_ability_categories(): void {
		\wp_register_ability_category(
			'orbis-products',
			[
				'label'       => \__( 'Orbis Products', 'orbis-products' ),
				'description' => \__( 'Abilities for working with the products in Orbis, such as hosting packages and licenses that are sold as subscriptions.', 'orbis-products' ),
			]
		);
	}

	/**
	 * Register abilities.
	 *
	 * @return void
	 */
	private function register_abilities(): void {
		$link = [
			'type'       => 'object',
			'properties' => [
				'href'  => [ 'type' => 'string' ],
				'title' => [ 'type' => 'string' ],
			],
		];

		$intervals = \array_keys( AdminController::get_intervals() );

		$product = [
			'type'       => 'object',
			'properties' => [
				'_links'            => [
					'type'        => 'object',
					'description' => \__( 'HAL links of the product. Each link has an href and a title that describes what the link is for.', 'orbis-products' ),
					'properties'  => [
						'self'    => $link,
						'product' => $link,
					],
				],
				'id'                => [
					'type'        => 'integer',
					'description' => \__( 'Orbis product ID, the same ID as product.id in the orbis-subscriptions/search ability.', 'orbis-products' ),
				],
				'post_id'           => [
					'type'        => [ 'integer', 'null' ],
					'description' => \__( 'WordPress post ID of the product.', 'orbis-products' ),
				],
				'name'              => [
					'type'        => 'string',
					'description' => \__( 'Name of the product.', 'orbis-products' ),
				],
				'currency'          => [
					'type'        => 'string',
					'description' => \__( 'Currency of the price and the cost price.', 'orbis-products' ),
				],
				'price'             => [
					'type'        => [ 'number', 'null' ],
					'description' => \__( 'Selling price per interval, the price the customer pays.', 'orbis-products' ),
				],
				'cost_price'        => [
					'type'        => [ 'number', 'null' ],
					'description' => \__( 'Cost price per interval, the purchase price Pronamic pays to the supplier. Null if not set.', 'orbis-products' ),
				],
				'interval'          => [
					'type'        => 'string',
					'description' => \__( 'Billing interval code: Y (yearly), 2Y (two yearly), 3Y (three yearly), Q (quarterly) or M (monthly).', 'orbis-products' ),
				],
				'interval_label'    => [
					'type'        => 'string',
					'description' => \__( 'Human readable billing interval.', 'orbis-products' ),
				],
				'auto_renew'        => [
					'type'        => 'boolean',
					'description' => \__( 'Whether subscriptions of this product renew automatically.', 'orbis-products' ),
				],
				'deprecated'        => [
					'type'        => 'boolean',
					'description' => \__( 'Whether the product is deprecated and no longer sold.', 'orbis-products' ),
				],
				'twinfield_article' => [
					'type'        => 'string',
					'description' => \__( 'Twinfield article code, empty if not set.', 'orbis-products' ),
				],
				'time_per_year'     => [
					'type'        => [ 'integer', 'null' ],
					'description' => \__( 'Time per year included in the product, in seconds. Null if not set.', 'orbis-products' ),
				],
				'description'       => [
					'type'        => 'string',
					'description' => \__( 'Description of the product.', 'orbis-products' ),
				],
				'cancel_note'       => [
					'type'        => 'string',
					'description' => \__( 'Note about cancelling subscriptions of this product, for example at the supplier.', 'orbis-products' ),
				],
				'notes'             => [
					'type'        => 'string',
					'description' => \__( 'Internal notes about the product.', 'orbis-products' ),
				],
			],
		];

		$meta = [
			'show_in_rest' => true,
			'annotations'  => [
				'readonly'    => true,
				'destructive' => false,
				'idempotent'  => true,
			],
		];

		\wp_register_ability(
			'orbis-products/search-products',
			[
				'label'               => \__( 'Search products', 'orbis-products' ),
				'description'         => \__( 'Searches the Orbis products and returns their details, including the selling price (price) and the purchase price (cost_price) per billing interval, so margins can be compared. Without a search term all products are returned, ordered by name. Subscriptions from orbis-subscriptions/search refer to these products by product.id.', 'orbis-products' ),
				'category'            => 'orbis-products',
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'search'     => [
							'type'        => 'string',
							'description' => \__( 'Search term, matched against the product name.', 'orbis-products' ),
						],
						'deprecated' => [
							'type'        => 'boolean',
							'description' => \__( 'Limit the results to deprecated (true) or not deprecated (false) products. Omit to return both.', 'orbis-products' ),
						],
						'interval'   => [
							'type'        => 'string',
							'description' => \__( 'Limit the results to products with this billing interval.', 'orbis-products' ),
							'enum'        => $intervals,
						],
						'per_page'   => [
							'type'        => 'integer',
							'description' => \__( 'Maximum number of products to return.', 'orbis-products' ),
							'minimum'     => 1,
							'maximum'     => 100,
							'default'     => 20,
						],
						'page'       => [
							'type'        => 'integer',
							'description' => \__( 'Page of results to return.', 'orbis-products' ),
							'minimum'     => 1,
							'default'     => 1,
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'total'    => [ 'type' => 'integer' ],
						'page'     => [ 'type' => 'integer' ],
						'per_page' => [ 'type' => 'integer' ],
						'products' => [
							'type'  => 'array',
							'items' => $product,
						],
					],
				],
				'execute_callback'    => $this->search_products( ... ),
				'permission_callback' => fn() => \current_user_can( 'edit_posts' ),
				'meta'                => $meta,
			]
		);

		\wp_register_ability(
			'orbis-products/get-product',
			[
				'label'               => \__( 'Get product', 'orbis-products' ),
				'description'         => \__( 'Returns the details of a single Orbis product by its Orbis product ID (id of the search-products ability, or product.id of the orbis-subscriptions/search ability), including the selling price (price), the purchase price (cost_price), the billing interval, the description and the cancel note.', 'orbis-products' ),
				'category'            => 'orbis-products',
				'input_schema'        => [
					'type'                 => 'object',
					'properties'           => [
						'id' => [
							'type'        => 'integer',
							'description' => \__( 'Orbis product ID.', 'orbis-products' ),
							'minimum'     => 1,
						],
					],
					'required'             => [ 'id' ],
					'additionalProperties' => false,
				],
				'output_schema'       => $product,
				'execute_callback'    => $this->get_product( ... ),
				'permission_callback' => fn() => \current_user_can( 'edit_posts' ),
				'meta'                => $meta,
			]
		);
	}

	/**
	 * Search products.
	 *
	 * @param array<string, mixed>|null $input Input.
	 * @return array<string, mixed>
	 */
	private function search_products( $input = [] ): array {
		global $wpdb;

		/**
		 * WordPress database abstraction object.
		 *
		 * @var \wpdb $wpdb
		 */

		$input = (array) $input;

		$search     = $input['search'] ?? '';
		$deprecated = $input['deprecated'] ?? null;
		$interval   = $input['interval'] ?? '';
		$per_page   = $input['per_page'] ?? 20;
		$page       = $input['page'] ?? 1;

		$per_page = \is_int( $per_page ) ? \min( 100, \max( 1, $per_page ) ) : 20;
		$page     = \is_int( $page ) ? \max( 1, $page ) : 1;

		$search = \is_string( $search ) ? \trim( $search ) : '';
		$like   = '' === $search ? '' : '%' . $wpdb->esc_like( $search ) . '%';

		$deprecated = \is_bool( $deprecated ) ? (int) $deprecated : -1;

		$interval = \is_string( $interval ) && \array_key_exists( $interval, AdminController::get_intervals() ) ? $interval : '';

		$table = $wpdb->prefix . 'orbis_products';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				<<<'SQL'
				SELECT
					COUNT( product.id )
				FROM
					%i AS product
						LEFT JOIN
					%i AS post
							ON product.post_id = post.ID
				WHERE
					( post.ID IS NULL OR post.post_status != 'trash' )
						AND
					( %s = '' OR product.name LIKE %s OR post.post_title LIKE %s )
						AND
					( %d = -1 OR product.deprecated = %d )
						AND
					( %s = '' OR product.`interval` = %s );
				SQL,
				$table,
				$wpdb->posts,
				$like,
				$like,
				$like,
				$deprecated,
				$deprecated,
				$interval,
				$interval
			)
		);

		/**
		 * Product rows.
		 *
		 * @var ProductRow[]|null $rows
		 */
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				<<<'SQL'
				SELECT
					product.*
				FROM
					%i AS product
						LEFT JOIN
					%i AS post
							ON product.post_id = post.ID
				WHERE
					( post.ID IS NULL OR post.post_status != 'trash' )
						AND
					( %s = '' OR product.name LIKE %s OR post.post_title LIKE %s )
						AND
					( %d = -1 OR product.deprecated = %d )
						AND
					( %s = '' OR product.`interval` = %s )
				ORDER BY
					product.name ASC
				LIMIT
					%d
				OFFSET
					%d;
				SQL,
				$table,
				$wpdb->posts,
				$like,
				$like,
				$like,
				$deprecated,
				$deprecated,
				$interval,
				$interval,
				$per_page,
				( $page - 1 ) * $per_page
			)
		);

		$rows ??= [];

		$post_ids = \array_filter( \array_map( fn( $row ) => (int) $row->post_id, $rows ) );

		\_prime_post_caches( $post_ids, false, true );

		$rows = \array_filter( $rows, $this->can_read( ... ) );

		return [
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
			'products' => \array_values( \array_map( $this->format_product( ... ), $rows ) ),
		];
	}

	/**
	 * Get product.
	 *
	 * @param array<string, mixed>|null $input Input.
	 * @return array<string, mixed>|WP_Error
	 */
	private function get_product( $input = [] ): array|WP_Error {
		global $wpdb;

		/**
		 * WordPress database abstraction object.
		 *
		 * @var \wpdb $wpdb
		 */

		$id = ( (array) $input )['id'] ?? 0;
		$id = \is_int( $id ) ? $id : 0;

		/**
		 * Product row.
		 *
		 * @var ProductRow|null $row
		 */
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				<<<'SQL'
				SELECT
					product.*
				FROM
					%i AS product
						LEFT JOIN
					%i AS post
							ON product.post_id = post.ID
				WHERE
					product.id = %d
						AND
					( post.ID IS NULL OR post.post_status != 'trash' );
				SQL,
				$wpdb->prefix . 'orbis_products',
				$wpdb->posts,
				$id
			)
		);

		if ( null === $row || ! $this->can_read( $row ) ) {
			return new WP_Error(
				'orbis_product_not_found',
				/* translators: %d: product ID */
				\sprintf( \__( 'Product %d not found.', 'orbis-products' ), $id ),
				[ 'status' => 404 ]
			);
		}

		return $this->format_product( $row );
	}

	/**
	 * Check if the current user can read the product.
	 *
	 * Products without a post are covered by the `edit_posts` capability of the abilities.
	 *
	 * @param object $row Product row.
	 * @phpstan-param ProductRow $row
	 * @return bool
	 */
	private function can_read( object $row ): bool {
		$post_id = (int) $row->post_id;

		return 0 === $post_id || \current_user_can( 'read_post', $post_id );
	}

	/**
	 * Format product row for ability output.
	 *
	 * @param object $row Product row.
	 * @phpstan-param ProductRow $row
	 * @return array<string, mixed>
	 */
	private function format_product( object $row ): array {
		$post_id = null === $row->post_id ? null : (int) $row->post_id;

		$get_meta = static function ( string $key ) use ( $post_id ): string {
			if ( null === $post_id ) {
				return '';
			}

			$value = \get_post_meta( $post_id, $key, true );

			return \is_string( $value ) ? $value : '';
		};

		$links = [];

		if ( null !== $post_id ) {
			$permalink = \get_permalink( $post_id );

			if ( false !== $permalink ) {
				$links['self'] = [
					'href'  => $permalink,
					'title' => \__( 'Orbis page of this product.', 'orbis-products' ),
				];
			}
		}

		$product_link = $get_meta( '_orbis_product_link' );

		if ( '' !== $product_link ) {
			$links['product'] = [
				'href'  => $product_link,
				'title' => \__( 'External page with information about this product, for example at the supplier.', 'orbis-products' ),
			];
		}

		$interval  = (string) $row->interval;
		$intervals = AdminController::get_intervals();

		return [
			'_links'            => (object) $links,
			'id'                => (int) $row->id,
			'post_id'           => $post_id,
			'name'              => (string) $row->name,
			'currency'          => 'EUR',
			'price'             => null === $row->price ? null : (float) $row->price,
			'cost_price'        => null === $row->cost_price ? null : (float) $row->cost_price,
			'interval'          => $interval,
			'interval_label'    => $intervals[ $interval ] ?? $interval,
			'auto_renew'        => (bool) $row->auto_renew,
			'deprecated'        => (bool) $row->deprecated,
			'twinfield_article' => (string) $row->twinfield_article,
			'time_per_year'     => null === $row->time_per_year ? null : (int) $row->time_per_year,
			'description'       => $get_meta( '_orbis_product_description' ),
			'cancel_note'       => $get_meta( '_orbis_product_cancel_note' ),
			'notes'             => $row->notes ?? '',
		];
	}
}
