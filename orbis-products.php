<?php
/**
 * Orbis Products
 *
 * @author    Pronamic
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Products
 *
 * @wordpress-plugin
 * Plugin Name:       Orbis Products
 * Plugin URI:        https://wp.pronamic.directory/plugins/orbis-products/
 * Description:       The Orbis Products plugin extends your Orbis environment with the option to add products.
 * Version:           1.0.0
 * Requires at least: 7.1
 * Requires PHP:      8.3
 * Author:            Pronamic
 * Author URI:        https://www.pronamic.eu/
 * Text Domain:       orbis-products
 * Domain Path:       /languages/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://wp.pronamic.directory/plugins/orbis-products/
 * GitHub URI:        https://github.com/pronamic/wp-orbis-products
 */

declare(strict_types=1);

namespace Pronamic\Orbis\Products;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

( static function (): void {
	$autoload_path = __DIR__ . '/vendor/autoload_packages.php';

	if ( \file_exists( $autoload_path ) ) {
		require_once $autoload_path;
	}

	Plugin::instance( __FILE__ );
} )();
