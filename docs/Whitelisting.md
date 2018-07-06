# Whitelisting Plugins and Themes

SatisPress supports standard plugins and themes. These must be whitelisted to be exposed as Composer packages.

Whitelisted plugins and themes are cached before being updated and new release are downloaded and saved as soon as WordPress is notified they're available.

All cached versions are exposed in `packages.json` so they can be required with Composer -- even versions that haven't yet been installed by WordPress!

## Plugins

Plugins can be whitelisted by visiting the __Plugins__ screen in your WordPress admin panel and toggling the checkbox for each plugin in the "SatisPress" column.

![Screenshot of the plugins page showing the whitelisting checkboxes](images/plugins.png)

## Themes
 
Themes can be toggled on the Settings screen at _Settings &rarr; SatisPress_.

![Screenshot of the SatisPress Settings page showing the whitelisting checkboxes for themes](images/themes.png)

[Back to Index](Index.md)
