# SatisPress

Generate a Composer repository from a list of installed WordPress plugins and themes.

## Why a WordPress Installation?

Many plugins and themes don't have public repositories, so managing them with Composer can be a hassle. Instead, SatisPress allows you to manage them in a standard WordPress installation, leveraging the built-in update process to handle the myriad licensing schemes that would be impossible to account for outside of WordPress.

The whitelisted packages are exposed via an automatically generated `packages.json` for inclusion as a Composer repository in a project's `composer.json` or even your own `satis.json`.

## Whitelisting Plugins

Plugins must be whitelisted to be exposed as Composer packages.

Create a custom WordPress plugin and hook into the `satispress_plugins` filter. The plugin basename should be added to the array returned by the filter. The basename is the main plugin file's relative path from the root plugin directory.

```php
<?php
/**
 * Plugin Name: SatisPress Plugins
 */

/**
 * Whitelist plugins to expose as Composer packages.
 *
 * @param array $plugins Whitelisted plugins.
 * @return array
 */
add_filter( 'satispress_plugins', function( $plugins ) {
	$plugins[] = 'better-internal-link-search/better-internal-link-search.php';
	$plugins[] = 'premium-plugin/main-plugin-file.php';

	return $plugins;
} );
```

## Whitelisting Themes

Similar to plugins, whitelisting themes requires hooking into the `satispress_themes` filter and adding the theme directory to the returned array.

```php
<?php
/**
 * Whitelist themes to expose as Composer packages.
 *
 * @param array $themes Whitelisted themes.
 * @return array
 */
add_filter( 'satispress_themes', function( $themes ) {
	$themes[] = 'genesis';

	return $themes;
} );
```

## Requiring SatisPress Packages

Add the SatisPress repository to the list of repositories in `composer.json` or `satis.json`, then require the packages using "satispress" as the vendor:

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
		"satispress/premium-plugin": "*",
		"satispress/genesis": "*"
    }
}
```

The vendor can be changed by hooking into the `satispress_vendor` filter in your custom plugin:

```php
<?php
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

## Cached Packages

Plugins and themes are automatically cached before being updated by WordPress and all known versions are exposed in `packages.json`.

Essentially, WordPress could be set up so that simply fetching packages actually triggers automatic updates for core and plugins. The only time you would need to log in is to install and set up new plugins or themes! (Automatic updates may not work with premium plugins or themes with a custom update process).

```php
<?php
// @link http://codex.wordpress.org/Configuring_Automatic_Background_Updates
add_filter( 'allow_dev_auto_core_updates', '__return_true' );
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_theme', '__return_true' );
```

## Security

**Be aware that the Composer repository and packages are public by default.**

Securing the repository should be possible using the same methods outlined in the [Satis documentation](http://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#security).

### HTTP Basic Authentication

To provide a simple solution, SatisPress ships with an add-on called "SatisPress Basic Authentication" that protects packages with HTTP Basic Authentication. Only users registered in WordPress will have access to the packages. After activating, make sure an `.htaccess` file exists in `wp-content/uploads/satispress/` to prevent direct access.

The [Limit Login Attempts](http://wordpress.org/plugins/limit-login-attempts/) plugin is supported to prevent brute force login attempts.

## Debugging

### `packages.json` Transient

The generated `packages.json` is cached for 12 hours via the transients API. It will be flushed whenever WordPress checks for new plugin versions or after any theme, plugin, or core is updated. Be sure to flush the `satispress_packages_json` transient if you need to regenerate it otherwise.

### Rewrite Rules

Flush rewrite rules and make sure the `satispress` rule exists if you're having trouble accessing `packages.json` or any of the packages. [Rewrite Rules Inspector](http://wordpress.org/plugins/rewrite-rules-inspector/) is a handy plugin for viewing or flushing rewrite rules.

## Premium Themes

Themes with custom upgrade routines must be active in order to determine whether updates are available, so upgrading them will probably be a manual process.
