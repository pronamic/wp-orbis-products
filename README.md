# Orbis Products

The Orbis Products plugin extends your Orbis environment with the option to add products.

## Requirements

- PHP 8.3+
- WordPress 7.1+
- Composer
- Node.js / npm

## Installation

```sh
composer install
npm install
```

## Development

This plugin follows the [SolveBeam WordPress Plugin Boilerplate](https://github.com/solvebeam/solvebeam-wordpress-plugin-boilerplate) conventions.

### Local environment (wp-env)

```sh
npx wp-env start
npx wp-env stop
```

The `.wp-env.json` maps the plugin twice into the WordPress environment:

| Mount path | Source | Purpose |
|---|---|---|
| `wp-content/plugins/orbis-products-dev` | `./` | Live development (including dev files) |
| `wp-content/plugins/orbis-products` | `./build/orbis-products/` | Built distribution version |

### Build

```sh
composer run build
```

### Deploy

```sh
composer run deploy
```

The hosts are defined in a shared Deployer file, imported through the `DEPLOYER_IMPORT` environment variable (default `~/deployer.php`).

### Translations

```sh
composer run make-pot
```

### Linting & analysis

```sh
composer run phpcs
composer run phpstan
composer run rector
composer run qa
```

## Data model

| Type | Key |
|---|---|
| Post types | `orbis_product`, `orbis_product_change` |
| Table | `{$wpdb->prefix}orbis_products`, available as `$wpdb->orbis_products` |
| Post meta | `_orbis_product_id`, `_orbis_product_price`, `_orbis_product_cost_price`, `_orbis_product_auto_renew`, `_orbis_product_deprecated`, `_orbis_product_interval`, `_orbis_product_description`, `_orbis_product_link`, `_orbis_product_cancel_note` |

Saving a product syncs the name, price, cost price, auto renew, deprecated and interval to the `orbis_products` table, which is used by [Orbis Subscriptions](https://github.com/pronamic/wp-orbis-subscriptions).

## Templates

`templates/product-details.php` shows the price, cost price and auto renew on a single product, through the `orbis_before_side_content` action of the Orbis theme.

## License

GPL-2.0-or-later
