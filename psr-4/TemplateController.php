<?php
/**
 * Template controller
 *
 * @author    Pronamic
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

declare(strict_types=1);

namespace Pronamic\Orbis\Products;

/**
 * Template controller class
 */
final class TemplateController {
	/**
	 * Construct.
	 */
	public function __construct() {
		\add_action( 'orbis_before_side_content', $this->maybe_include_product_details( ... ) );
	}

	/**
	 * Maybe include product details.
	 *
	 * @return void
	 */
	private function maybe_include_product_details(): void {
		if ( ! \is_singular( 'orbis_product' ) ) {
			return;
		}

		include __DIR__ . '/../templates/product-details.php';
	}
}
