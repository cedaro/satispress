# Changelog

## [Unreleased]

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

[Unreleased]: https://github.com/cedaro/satispress/compare/v0.3.2...HEAD
[0.3.2]: https://github.com/cedaro/satispress/compare/v0.3.1...v0.3.2
[0.3.1]: https://github.com/cedaro/satispress/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/cedaro/satispress/compare/v0.2.3...v0.3.0
