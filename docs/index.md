# SatisPress Documentation

Generate a Composer repository from installed WordPress plugins and themes.

## Why a WordPress Installation?

Many plugins and themes don't have public repositories, making managing them with Composer a hassle. SatisPress allows you to manage them in a standard WordPress installation, leveraging core's built-in update process to handle the myriad licensing schemes that would be impossible to account for outside of WordPress.

Packages are exposed via a `packages.json` file for inclusion as a Composer repository in a project's `composer.json` or even your own `satis.json`.

## Table of Contents

1. [Installation](installation.md)
1. Managing SatisPress
	1. [Getting Started](setup.md)
	1. [Security](security.md)
	1. [Settings](settings.md)
1. [Using Composer](composer.md)
1. Workflows
	1. [Running SatisPress in Production](workflows/production.md)
	1. [Central Package Server](workflows/central-server.md)
	1. Continous Integration
	1. Commercial Vendors
1. [MU Plugins](mu-plugins.md)
1. [Logging](logging.md)
1. [Integrations](integrations.md)
1. [Troubleshooting](troubleshooting.md)
1. [Alternatives](alternatives.md)
