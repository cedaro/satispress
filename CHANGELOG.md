# Changelog

## [Unreleased]

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

[Unreleased]: https://github.com/cedaro/satispress/compare/v0.3.1...HEAD
[0.3.1]: https://github.com/cedaro/satispress/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/cedaro/satispress/compare/v0.2.3...v0.3.0
