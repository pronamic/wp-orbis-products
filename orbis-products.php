<?php
/**
 * Orbis Products
 *
 * @package   Pronamic\Orbis\Products
 * @author    Pronamic
 * @copyright 2024 Pronamic
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Orbis Products
 * Plugin URI:        https://wp.pronamic.directory/plugins/orbis-subscriptions/
 * Description:       The Orbis Products plugin extends your Orbis environment with the option to add products.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Pronamic
 * Author URI:        https://www.pronamic.eu/
 * Text Domain:       orbis-products
 * Domain Path:       /languages/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://wp.pronamic.directory/plugins/orbis-products/
 * GitHub URI:        https://github.com/pronamic/wp-orbis-products
 */

namespace Pronamic\Orbis\Products;

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

/**
 * Bootstrap.
 */
add_action(
	'plugins_loaded',
	function () {
		\load_plugin_textdomain( 'orbis-products', false, \dirname( \plugin_basename( __FILE__ ) ) . '/languages' );

		Plugin::instance()->setup();
	}
);
