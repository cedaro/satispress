# SatisPress

Generate a Composer repository from installed WordPress plugins and themes.

## Why a WordPress Installation?

Many plugins and themes don't have public repositories, so managing them with Composer can be a hassle. Instead, SatisPress allows you to manage them in a standard WordPress installation, leveraging the built-in update process to handle the myriad licensing schemes that would be impossible to account for outside of WordPress.

The whitelisted packages are exposed via an automatically generated `packages.json` for inclusion as a Composer repository in a project's `composer.json` or even your own `satis.json`.

## Installation

Requires PHP 7.0

1. Download the [latest release](https://github.com/blazersix/satispress/archive/master.zip) from GitHub.
2. Go to the _Plugins &rarr; Add New_ screen in your WordPress admin panel and click the __Upload__ tab at the top.
3. Upload the zipped archive.
4. Click the __Activate Plugin__ link after installation completes.

## Setup

### Whitelisting Packages

Plugins and themes must be whitelisted to be exposed as Composer packages.

Plugins can be whitelisted by visiting the __Plugins__ screen in your WordPress admin panel and toggling the checkbox for each plugin in the "SatisPress" column.

Themes can be toggled on the Settings screen at _Settings &rarr; SatisPress_.

Whitelisted plugins and themes are cached before being updated and new release are downloaded and saved as soon as WordPress is notified they're available.

All cached versions are exposed in `packages.json` so they can be required with Composer -- even versions that haven't yet been installed by WordPress!

### Security

Securing the repository should be possible using the same methods outlined in the [Satis documentation](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#security).

#### Authentication

To provide a simple solution, SatisPress ships with HTTP Basic Authentication to protect packages. Only users registered in WordPress will have access to the packages.

#### Third-party Authentication Providers

Third-party authentication providers that hook into the `determine_current_user` filter and take care to account for multiple authentication schemes should work with SatisPress.

### Debugging

#### `packages.json` Transient

The generated `packages.json` is cached for 12 hours via the transients API. It will be flushed whenever WordPress checks for new plugin versions or after any theme, plugin, or core is updated. Be sure to flush the `satispress_packages` transient if you need to regenerate it otherwise.

#### Rewrite Rules

Flush rewrite rules and make sure the `satispress` rule exists if you're having trouble accessing `packages.json` or any of the packages. [Rewrite Rules Inspector](https://wordpress.org/plugins/rewrite-rules-inspector/) is a handy plugin for viewing or flushing rewrite rules.

### Premium Themes

Themes with custom upgrade routines must be active in order to determine whether updates are available, so upgrading them will probably be a manual process.

## Requiring SatisPress Packages

Once SatisPress is installed and configured you can include the SatisPress repository in the list of repositories in your `composer.json` or `satis.json`, then require the packages using "satispress" (or your custom setting) as the vendor:

```json
{
	"repositories": [
		{
			"type": "composer",
			"url": "https://example.com/satispress/"
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

## Troubleshooting

### Basic Auth not working

Certain server arrangements don't allow the Basic Auth credentials to be made available under PHP. To get around this, you'll need to set an environment variable in the site root .htaccess file.

See https://github.com/blazersix/satispress/wiki/Basic-Auth for more info.

## Credits

Created by [Brady Vercher](https://www.blazersix.com/) and supported by [Gary Jones](https://gamajo.com).
