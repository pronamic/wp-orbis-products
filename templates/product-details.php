<?php
/**
 * Product details
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

namespace Pronamic\Orbis\Products;

use Pronamic\WordPress\Money\Money;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$price_string = \get_post_meta( get_the_ID(), '_orbis_product_price', true );

?>
<div class="card mb-4">
	<div class="card-header"><?php esc_html_e( 'Product details', 'orbis-products' ); ?></div>
	<div class="card-body">

		<div class="content">
			<dl>
				<dt><?php esc_html_e( 'Price', 'orbis-products' ); ?></dt>
				<dd>
					<?php

					if ( \is_numeric( $price_string ) ) {
						$price = new Money( $price_string, 'EUR' );

						echo \esc_html( $price->format_i18n() );
					}

					if ( ! \is_numeric( $price_string ) ) {
						echo '—';
					}

					?>
				</dd>
			</dl>
		</div>
	</div>
	
</div>
