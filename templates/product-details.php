<div class="card">
	<div class="card-body">
		<h3 class="card-title"><?php esc_html_e( 'Product Details', 'orbis_products' ); ?></h3>

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
