# Requiring SatisPress Packages

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

The `satispress` vendor name can be changed on the [Settings page](Settings.md).

[Back to Index](Index.md)
