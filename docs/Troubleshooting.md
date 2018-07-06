# Troubleshooting

## Basic Auth not working

Certain server arrangements don't allow the Basic Auth credentials to be made available under PHP. To get around this, you'll need to set an environment variable in the site root .htaccess file.

See https://github.com/blazersix/satispress/wiki/Basic-Auth for more info.

## `packages.json` Transient

The generated `packages.json` is cached for 12 hours via the transients API. It will be flushed whenever WordPress checks for new plugin versions or after any theme, plugin, or core is updated. Be sure to flush the `satispress_packages` transient if you need to regenerate it otherwise.

## Rewrite Rules

If you're having trouble accessing `packages.json` or any of the packages, flush the rewrite rules (visit the Settings -> Permalinks page) and make sure the `satispress` rule exists . [Rewrite Rules Inspector](https://wordpress.org/plugins/rewrite-rules-inspector/) is a handy plugin for viewing or flushing rewrite rules.

[Back to Index](Index.md)
