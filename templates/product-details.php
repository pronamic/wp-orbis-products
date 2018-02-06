<div class="panel">
	<header>
		<h3><?php esc_html_e( 'Product Details', 'orbis_products' ); ?></h3>
	</header>

	<div class="content">
		<dl>
			<dt><?php esc_html_e( 'Price', 'orbis_products' ); ?></dt>
			<dd>
				<?php orbis_product_the_price(); ?>
			</dd>
		</dl>
	</div>
</div>
