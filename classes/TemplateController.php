<?php
/**
 * Template controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

namespace Pronamic\Orbis\Products;

/**
 * Template controller class
 */
class TemplateController {
	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'orbis_before_side_content', [ $this, 'maybe_include_product_details' ] );
	}

	/**
	 * Maybe include product details.
	 * 
	 * @return void
	 */
	public function maybe_include_product_details() {
		if ( ! \is_singular( 'orbis_product' ) ) {
			return;
		}

		include __DIR__ . '/../templates/product-details.php';
	}
}
