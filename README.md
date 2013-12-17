# SatisPress

Generate a Composer repository from a list of installed WordPress plugins.

## Why a WordPress Installation?

Many plugins don't haven't publicly accessible repositories, so managing them with Composer is a hassle. Instead, SatisPress allows the plugins to be managed in a standard WordPress installation, leveraging the built-in update process. The whitelisted plugins are exposed via an automatically generated `packages.json` for inclusion as a `composer` repository type in `composer.json` or `satis.json`.

## Whitelisting Plugins

Create a custom plugin and hook into the `satispress_plugins` filter to whitelist plugins that should be exposed as packages. The plugin basename should be added to the filtered array. The basename is the main plugin file's relative path from the plugin directory.

```php
<?php
/**
 * Plugin Name: SatisPress Plugins
 */

/**
 * Whitelist plugins as Composer packages.
 *
 * @param array $plugins Whitelisted plugins.
 * @return array
 */
add_filter( 'satispress_plugins', function( $plugins ) {
	$plugins[] = 'better-internal-link-search/better-internal-link-search.php';
	$plugins[] = 'premium-plugin/main-plugin-file.php';

	return $plugins;
} );

/**
 * Update the default vendor.
 *
 * @param string $vendor Default vendor.
 * @return string
 */
add_filter( 'satispress_vendor', function( $vendor ) {
	return 'satispress';
} );
```

## Requiring SatisPress Packages

Add the SatisPress repository to list of repositories in `composer.json` or `satis.json`, then require the packages using `satispress` as the vendor:

```json
{
	"repositories": [
		{
			"type": "composer",
			"url": "http://example.com/satispress/"
		}
    ],
	"require": {
		"composer/installers": "~1.0",
        "satispress/better-internal-link-search": "*",
		"satispress/premium-plugin": "*"
    }
}
```

The vendor can be changed by hooking into the `satispress_vendor` filter.

## Things of Note

* Only the most recent version of each plugin is currently exposed.
* A security method hasn't been implemented, so packages will be public.
* The generated `packages.json` is cached for 12 hours via the transients API. Be sure to flush the transient if you need to have it regenerated.
* Plugin archives are generated when requested for the first time.
* Flush rewrite rules and make sure the `satispress` rule exists.