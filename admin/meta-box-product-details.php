<?php
/**
 * Meta box product details
 *
 * @author    Pronamic
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 */

declare(strict_types=1);

namespace Pronamic\Orbis\Products;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post.
 *
 * @var \WP_Post $post
 */

$get_meta = static function ( string $key ) use ( $post ): string {
	$value = \get_post_meta( $post->ID, $key, true );

	return \is_string( $value ) ? $value : '';
};

$format_price = static fn ( string $value ): string => \is_numeric( $value ) ? \number_format( (float) $value, 2, '.', '' ) : '';

$orbis_id     = $get_meta( '_orbis_product_id' );
$price        = $get_meta( '_orbis_product_price' );
$cost_price   = $get_meta( '_orbis_product_cost_price' );
$auto_renew   = $get_meta( '_orbis_product_auto_renew' );
$deprecated   = $get_meta( '_orbis_product_deprecated' );
$interval     = $get_meta( '_orbis_product_interval' );
$description  = $get_meta( '_orbis_product_description' );
$product_link = $get_meta( '_orbis_product_link' );
$cancel_note  = $get_meta( '_orbis_product_cancel_note' );

\wp_nonce_field( 'orbis_save_product_details', 'orbis_product_details_meta_box_nonce' );

?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_product_id"><?php \esc_html_e( 'Orbis ID', 'orbis-products' ); ?></label>
			</th>
			<td>
				<input id="orbis_product_id" value="<?php echo \esc_attr( $orbis_id ); ?>" type="text" class="regular-text" readonly="readonly" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_product_price"><?php \esc_html_e( 'Price', 'orbis-products' ); ?></label>
			</th>
			<td>
				<input type="number" step="0.01" id="orbis_product_price" name="_orbis_product_price" value="<?php echo \esc_attr( $format_price( $price ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_product_cost_price"><?php \esc_html_e( 'Cost Price', 'orbis-products' ); ?></label>
			</th>
			<td>
				<input type="number" step="0.01" id="orbis_product_cost_price" name="_orbis_product_cost_price" value="<?php echo \esc_attr( $format_price( $cost_price ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php \esc_html_e( 'Auto Renew', 'orbis-products' ); ?>
			</th>
			<td>
				<label for="orbis_product_auto_renew">
					<input type="checkbox" value="yes" id="orbis_product_auto_renew" name="_orbis_product_auto_renew" <?php \checked( '' !== $auto_renew ); ?> />
					<?php \esc_html_e( 'Auto renew subscription product', 'orbis-products' ); ?>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php \esc_html_e( 'Deprecated', 'orbis-products' ); ?>
			</th>
			<td>
				<label for="orbis_product_deprecated">
					<input type="checkbox" value="yes" id="orbis_product_deprecated" name="_orbis_product_deprecated" <?php \checked( '' !== $deprecated ); ?> />
					<?php \esc_html_e( 'Deprecated subscription product', 'orbis-products' ); ?>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_product_interval"><?php \esc_html_e( 'Interval', 'orbis-products' ); ?></label>
			</th>
			<td>
				<select id="orbis_product_interval" name="_orbis_product_interval">
					<?php

					foreach ( [ '' => '' ] + AdminController::get_intervals() as $value => $label ) {
						\printf(
							'<option value="%s" %s>%s</option>',
							\esc_attr( $value ),
							\selected( $interval, $value, false ),
							\esc_html( $label )
						);
					}

					?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_product_description"><?php \esc_html_e( 'Description', 'orbis-products' ); ?></label>
			</th>
			<td>
				<textarea id="orbis_product_description" name="_orbis_product_description" rows="3" cols="60"><?php echo \esc_textarea( $description ); ?></textarea>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_product_link"><?php \esc_html_e( 'Link', 'orbis-products' ); ?></label>
			</th>
			<td>
				<input id="orbis_product_link" name="_orbis_product_link" value="<?php echo \esc_attr( $product_link ); ?>" type="url" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_product_cancel_note"><?php \esc_html_e( 'Cancel Note', 'orbis-products' ); ?></label>
			</th>
			<td>
				<textarea id="orbis_product_cancel_note" name="_orbis_product_cancel_note" rows="3" cols="60"><?php echo \esc_textarea( $cancel_note ); ?></textarea>
			</td>
		</tr>
	</tbody>
</table>
