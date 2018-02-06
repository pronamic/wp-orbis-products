<?php

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
	echo esc_html( orbis_price( orbis_product_get_the_price() ) );
}
