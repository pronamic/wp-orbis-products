<div class="card">
	<div class="card-header"><?php esc_html_e( 'Product Details', 'orbis_products' ); ?></div>
	<div class="card-body">

		<div class="content">
			<dl>
				<dt><?php esc_html_e( 'Price', 'orbis_products' ); ?></dt>
				<dd>
					<?php orbis_product_the_price(); ?>
				</dd>
			</dl>
		</div>
	</div>
	
</div>
