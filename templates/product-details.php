<?php
/**
 * Product details
 *
 * @author    Pronamic
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

declare(strict_types=1);

namespace Pronamic\Orbis\Products;

use Pronamic\WordPress\Money\Money;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = (int) \get_the_ID();

$format_price = static function ( string $key ) use ( $product_id ): string {
	$value = \get_post_meta( $product_id, $key, true );

	if ( ! \is_numeric( $value ) ) {
		return '—';
	}

	return ( new Money( $value, 'EUR' ) )->format_i18n();
};

$auto_renew = \get_post_meta( $product_id, '_orbis_product_auto_renew', true );

?>
<div class="card mb-4">
	<div class="card-header"><?php \esc_html_e( 'Product details', 'orbis-products' ); ?></div>
	<div class="card-body">
		<div class="content">
			<dl>
				<dt><?php \esc_html_e( 'Price', 'orbis-products' ); ?></dt>
				<dd><?php echo \esc_html( $format_price( '_orbis_product_price' ) ); ?></dd>

				<dt><?php \esc_html_e( 'Cost Price', 'orbis-products' ); ?></dt>
				<dd><?php echo \esc_html( $format_price( '_orbis_product_cost_price' ) ); ?></dd>

				<dt><?php \esc_html_e( 'Auto Renew', 'orbis-products' ); ?></dt>
				<dd><?php echo \esc_html( '' === $auto_renew || false === $auto_renew ? \__( 'No', 'orbis-products' ) : \__( 'Yes', 'orbis-products' ) ); ?></dd>
			</dl>
		</div>
	</div>
</div>
