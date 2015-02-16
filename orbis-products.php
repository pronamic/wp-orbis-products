<?php
/*
Plugin Name: Orbis Products
Plugin URI: http://www.pronamic.eu/plugins/orbis-products/
Description: The Orbis Products plugin extends your Orbis environment with the option to add products.

Version: 1.0.0
Requires at least: 3.5

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: orbis_products
Domain Path: /languages/

License: Copyright (c) Pronamic

GitHub URI: https://github.com/wp-orbis/wp-orbis-products
*/

function orbis_products_bootstrap() {
	// Classes
	require_once 'classes/orbis-products-plugin.php';

	// Initialize
	global $orbis_products_plugin;
	
	$orbis_products_plugin = new Orbis_Products_Plugin( __FILE__ );
}

add_action( 'orbis_bootstrap', 'orbis_products_bootstrap' );
