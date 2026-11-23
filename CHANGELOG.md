# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Deployer configuration (`deploy.php`) and `composer deploy` script.
- PHPStan (level max) and Rector.

### Changed

- Requires PHP 8.3 and WordPress 7.1.
- Follows the SolveBeam WordPress Plugin Boilerplate conventions: strict types, final classes, first-class callable hooks and fully qualified function calls.
- The build is created in `build/orbis-products/`.
- The Orbis ID column shows the ID without a link to the old Orbis environment.

### Fixed

- Saving product details no longer uses the `FILTER_SANITIZE_STRING` filter, which is deprecated since PHP 8.1.

### Removed

- `load_plugin_textdomain()`, translations are loaded just in time by WordPress.

## [1.0.0]

### Added

- Initial release.

[unreleased]: https://github.com/pronamic/wp-orbis-products/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/pronamic/wp-orbis-products/releases/tag/v1.0.0
