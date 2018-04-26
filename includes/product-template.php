<?php
use Pronamic\WordPress\Money\Money;

/**
 * Return the price.
 *
 * @return null|string $price
 */
function orbis_product_get_the_price() {
	global $post;

	$price = get_post_meta( $post->ID, '_orbis_product_price', true );

	return $price;
}

/**
 * Echo the price.
 */
function orbis_product_the_price() {
	$price = new Money( orbis_product_get_the_price(), 'EUR' );
	echo esc_html( $price->format_i18n() );
}
