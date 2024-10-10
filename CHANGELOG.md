# Changelog

## [Unreleased]

## [2.0.0] - 2024-10-10

* SatisPress now requires PHP 8+.
* Updated Composer dependencies. The latest versions of PSR packages and the Pimple container may prevent conflicts with other plugins.
* Added return type hints for classes implementing `ArrayAccess`. This prevents errors in PHP 8+.
* Fixed a fatal error in the Envato integration. See [#182]. Props [@BjornKraft](https://github.com/BjornKraft)
* Added `satispress_debug_mode` filter to prevent exceptions from being thrown in the request handler. See [#211].
* Fixed output of `code` tags in the admin.
* Prevented errors when header names aren't strings. See [#192].
* Fixed formatting of the authors array in `package.json`. See [#185]. Props [@NielsdeBlaauw](https://github.com/NielsdeBlaauw)
* Added the package name to the information displayed on the Repository tab in the admin. See [#191]. Props [@tyrann0us](https://github.com/tyrann0us)
* Fixed numerous WPCS warnings and errors after updating to WPCS 3.

## [1.0.4] - 2022-05-02

* Allowed SatisPress dependencies to require `composer/installers` version 1 or 2. Props [@LucasDemea](https://github.com/LucasDemea)
* Removed `node_modules` from the list of directories to be automatically excluded for cases where a package depends on it existing. Props [@tyrann0us](https://github.com/tyrann0us)

## [1.0.3] - 2022-01-07

* Added a search field in the package selector sidebar. Props [@DavidSingh3](https://github.com/DavidSingh3)
* Added support for composer/installers 2.0+ for packages served by SatisPress. Props [@tyrann0us](https://github.com/tyrann0us)
* Added support for PHP 8.

## [1.0.2] - 2021-07-27

* Fixed a parse error in the Envato Market integration.
* Skipped releases without a SemVer compliant version string in the Composer repository transformer to prevent fatal errors. [See #160](https://github.com/cedaro/satispress/issues/160). Props [@DavidSingh3](https://github.com/DavidSingh3)
* Removed the trace from `\SatisPress\Logger\format_exception()` to prevent fatal errors.

## [1.0.1] - 2021-04-12

* Fixed the validation logic in the hidden directory validator and updated the test. See [#142](https://github.com/cedaro/satispress/issues/142). Props [@patrick-leb](https://github.com/patrick-leb) for troubleshooting and help in resolving this issue.
* Fixed a fatal error in the Envato Market adapter. See [#153](https://github.com/cedaro/satispress/issues/153)

## [1.0.0] - 2021-03-03

* Updated the admin interface to make it easier to manage the repository from a single screen.
* Introduced REST endpoints for managing SatisPress resources:
	* `/satispress/v1/packages`
	* `/satispress/v1/plugins`
	* `/satispress/v1/themes`
* Removed the meta capability check from the Composer repository transformer. This allowed packages or individual releases to be filtered from `packages.json`, but prevented the transformer from being used in other scenarios.
* Removed a type hint from the `upgrader_post_install` to prevent fatal errors when a `WP_Error` object is passed as a parameter. See [#152](https://github.com/cedaro/satispress/issues/152)

## [0.7.2] - 2021-02-11

* Fixed an incorrect variable name in the `HiddenDirectoryValidator` that caused a fatal error.
* Added a Health Check feature to display admin notices for common configuration issues.
* Fixed authentication integration tests to check for the correct exception type after preventing `AuthenticationException`s from being thrown in [a2415c7](https://github.com/cedaro/satispress/commit/a2415c7eaf2f3b7f4bb81baf7bed22cb19aad26e).
* Moved validator tests to the integration test suite.
* Removed the `PclZip` development dependency from `composer.json`. WordPress uses a patched version and in cases where it was inadvertently installed, it could cause issues. See [#149](https://github.com/cedaro/satispress/issues/149).

## [0.7.1] - 2021-02-04

* There weren't any changes in this release. The version number was bumped to allow Composer to install updates from the previously botched release process.

## [0.7.0] - 2021-01-28

* Introduced validators to prevent invalid artifacts from being cached when downloaded from the vendor.
* Introduced adapters for downloading artifacts from vendors that use a non-standard update process.
* Added a field to the SatisPress package list screen to copy the CLI command for requiring a package in `composer.json`.
* Fixed a bug with the logger not logging messages above the specified log level.

## [0.6.0] - 2021-01-11

* Added the `satispress_package_download_url` filter. This can be used to ignore update requests from vendors with custom update routines that cause invalid artifacts to be downloaded.
* Created missing artifacts from source for the currently installed version of a package when viewing the `packages.json` endpoint. This allows updates made through FTP, git, or the admin UI to be archived automatically and included in `packages.json` without having to perform any additional manual steps. See [#131](https://github.com/cedaro/satispress/issues/131)
* Archived packages automatically when they're upgraded in the WordPress admin panel.
* Coerced package download URLs retrieved from the `update_plugins` and `update_themes` transients to strings before using them to prevent fatal errors caused by packages that inject unexpected data. See [#106](https://github.com/cedaro/satispress/issues/106)
* Updated `dealerdirect/phpcodesniffer-composer-installer` for Composer 2 compatibility. Props [@aaronware](https://github.com/aaronware) 
* Updated `composer/semver` dependency to version 3.2.

## [0.5.2] - 2020-12-01

* Cast meta keys to strings in `SatisPress\Authentication\ApiKey\ApiKeyRepository::find_for_user()` to prevent fatal errors in some situations [#133](https://github.com/cedaro/satispress/issues/133).
* Fixed a fatal error when `wp_parse_url()` returned `null` [#135](https://github.com/cedaro/satispress/pull/135). Props [@danielbachhuber](https://github.com/danielbachhuber)
* Moved functionality for determining excluded files to `SatisPress\Archiver::get_excluded_files()`.

## [0.5.1] - 2020-03-03

* Stopped throwing authentication exceptions when `WP_DEBUG` is enabled so HTTP challenge headers could be sent and allow clients to display a login prompt. [#105](https://github.com/cedaro/satispress/issues/105).
* Prevented a fatal error in the authentication provider when URLs couldn't be parsed [#122](https://github.com/cedaro/satispress/pull/122). Props [@BrianHenryIE](https://github.com/BrianHenryIE)
* Handled cases when the server doesn't define the request method to prevent a notice [#122](https://github.com/cedaro/satispress/pull/122). Props [@BrianHenryIE](https://github.com/BrianHenryIE)
* Allowed settings to be saved when SatisPress is used in multisite mode [#119](https://github.com/cedaro/satispress/issues/119).
* Removed a couple of dead or superfluous lines of code.

## [0.5.0] - 2019-09-25

* Added the `.` (dot) character to the list of allowed characters for package slugs. See [#108](https://github.com/cedaro/satispress/issues/108)
* Removed the `/coverage`, `/docs`, `/dist`, and `/tests` directories from the default excludes. This is a breaking change in the sense that files in those directories will now be included unless they're configured to be excluded via a filter or `.distignore`. See [#103](https://github.com/cedaro/satispress/issues/103)
* Added a `.distignore` to exclude development files if SatisPress is added to the repository.
* Delegated the event handler for toggling plugins on the *Manage Plugins* screen to fix a bug preventing plugins from being toggled if the list of plugins had been filtered. See [#107](https://github.com/cedaro/satispress/issues/107)
* Removed internal array keys from the package repository classes to prevent an error when a theme and plugin have the same slug. See [#109](https://github.com/cedaro/satispress/issues/109)

## [0.4.1] - 2019-06-20

* Added support for `.distignore` files to customize which files are excluded from generated artifacts ([#100](https://github.com/cedaro/satispress/issues/100)). Props [@TimothyBJacobs](https://github.com/TimothyBJacobs)
* Prevented a fatal error when the request path is `null` ([#98](https://github.com/cedaro/satispress/issues/98)). Props [@danielbachhuber](https://github.com/danielbachhuber)

## [0.4.0] - 2019-04-02

* Packages names have been lowercased and invalid characters will be removed to prevent errors when Composer 2.0 is released ([#90](https://github.com/cedaro/satispress/issues/90)). This may require updates to `composer.json` if your project requires packages with uppercase characters.
* Refactored authentication servers to prevent conflicts with plugins that call `determine_current_user` earlier than expected ([#94](https://github.com/cedaro/satispress/issues/94)). This changes the server interface and will require code updates if you're using custom authentication server.
* Introduced a default logger implementation to surface issues in `debug.log` when debug mode is enabled. By default, only messages with a `warning` level or higher will be logged ([#86](https://github.com/cedaro/satispress/issues/86)). 
* Updated the sanitization rules for custom vendor names to align more closely with Composer's rules.
* Introduced a testing suite and tests.
* Fixed the documented method for disabling authentication ([#93](https://github.com/cedaro/satispress/pull/93)). Props [@rickard-berg](https://github.com/rickard-berg)
* Sorted releases just before building a package to ensure they're always in the expected order.
* Fixed the permalink for `packages.json` when rewrites aren't enabled.

## [0.3.2] - 2019-02-01

* Improved the method for discovering package updates to make caching more reliable.

## [0.3.1] - 2018-12-19

* Displayed an admin notice and prevented SatisPress from loading if required dependencies were missing ([#76](https://github.com/cedaro/satispress/issues/76)).
* Fixed a bug causing downloads to fail for plugins that have slugs with uppercase characters ([#83](https://github.com/cedaro/satispress/issues/83)).
* Assigned the injected logger to a local property in the Upgrade provider to prevent a fatal error when upgrades failed ([#85](https://github.com/cedaro/satispress/issues/85)).

## [0.3.0] - 2018-08-31

This is a major rewrite that helps shift SatisPress from an experimental concept to a working solution for managing WordPress plugins and themes as Composer packages.

Major changes include:

* PHP 7.0 or later is required.
* Packages and endpoints require [authentication](docs/security.md) by default.
* A random suffix is applied to the cache directory to prevent visitors from guessing its location.
* Earlier versions cached packages from source just before updating a plugin or theme. Plugins and themes are now immediately cached from source when they're whitelisted. If you're upgrading from an older version of SatisPress, artifacts should be automatically created for any uncached packages.
* Pending theme and plugin updates are downloaded directly from the vendor and exposed in `packages.json`, so updating is no longer required to expose new releases to Composer.
* [Capabilities](docs/security.md#capabilities) were added for viewing and downloading packages, as well as managing SatisPress options. Only administrators have access by default.
* The storage layer was abstracted to make it swappable.

[Unreleased]: https://github.com/cedaro/satispress/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/cedaro/satispress/compare/v1.0.4...v2.0.0
[1.0.4]: https://github.com/cedaro/satispress/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/cedaro/satispress/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/cedaro/satispress/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/cedaro/satispress/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/cedaro/satispress/compare/v0.7.2...v1.0.0
[0.7.2]: https://github.com/cedaro/satispress/compare/v0.7.1...v0.7.2
[0.7.1]: https://github.com/cedaro/satispress/compare/v0.7.0...v0.7.1
[0.7.0]: https://github.com/cedaro/satispress/compare/v0.6.0...v0.7.0
[0.6.0]: https://github.com/cedaro/satispress/compare/v0.5.2...v0.6.0
[0.5.2]: https://github.com/cedaro/satispress/compare/v0.5.1...v0.5.2
[0.5.1]: https://github.com/cedaro/satispress/compare/v0.5.0...v0.5.1
[0.5.0]: https://github.com/cedaro/satispress/compare/v0.4.1...v0.5.0
[0.4.1]: https://github.com/cedaro/satispress/compare/v0.4.0...v0.4.1
[0.4.0]: https://github.com/cedaro/satispress/compare/v0.3.2...v0.4.0
[0.3.2]: https://github.com/cedaro/satispress/compare/v0.3.1...v0.3.2
[0.3.1]: https://github.com/cedaro/satispress/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/cedaro/satispress/compare/v0.2.3...v0.3.0
