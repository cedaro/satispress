# SatisPress

Facilitate modern best practices for managing WordPress websites by automating Composer support for private plugins and themes.

## What is Composer?

When managing a WordPress site, multiple environments are usually needed for developing or testing code before deploying it to the live server. This requires being able to easily replicate the site and its dependencies between environments, which is where [Composer](https://getcomposer.org/) comes in.

Composer allows for defining a project's dependencies, where they come from, how to access them, and then installing them from their source.

For WordPress sites, dependencies are usually plugins and themes, and even WordPress itself. Essentially, a single file (`composer.json`) can be shared with another developer and they can rebuild the entire site structure from it.

Composer connects to repositories &mdash; directories that tell it where to find dependencies (packages) and how they should be handled.

[Packagist](https://packagist.org/) is the main Composer repository for PHP packages and [WordPress Packagist](https://wpackagist.org/) provides access to plugins and themes hosted in the directories on WordPress.org.

## What's the problem?

Most commercial plugins and themes (also known as packages) aren't publicly available, so they can't be installed with Composer.

Some common workarounds include:

* Checking the plugin or theme in alongside custom project code in your version control system
* Creating a separate private repository for each plugin or theme and manually updating it as new versions are released

Neither option is ideal and can be a hassle to maintain over time.

Furthermore, access is usually restricted with proprietary licensing schemes that make it difficult to download releases programmatically.

## How does SatisPress help?

SatisPress creates a dynamically updated Composer repository that provides access to private plugins and themes and makes new releases available automatically.

After installing SatisPress (it's a standard WordPress plugin):

1. Choose the plugins and themes that you want to manage
2. SatisPress zips the currently installed versions and stores them in a cache directory
3. When an update for a managed plugin or theme becomes available, SatisPress downloads and saves it alongside previously cached releases
4. A Composer repository is generated that can be included in your `composer.json` file to download any cached plugin or theme

There are several possible workflows, but SatisPress allows you to manage private plugins and themes in a standard WordPress installation, leveraging the built-in update process to handle the myriad licensing schemes that would be impossible to account for outside of WordPress.

It's the missing piece for managing WordPress websites with Composer.

## What if I don't use Composer?

SatisPress can still benefit you since it makes releases downloadable directly from your admin panel, so you don't need to log in to vendors' sites to download updates.

Oftentimes vendors only provide access to the latest release, so you're stuck if something breaks and you didn't save the previous version. With SatisPress, you can download previously cached releases to rollback if needed and compare the code to see what changed.

## Documentation

For installation notes, information about usage, security, and more, see the [documentation](docs/Index.md).

## Credits

Created by [Brady Vercher](https://www.blazersix.com/) and supported by [Gary Jones](https://gamajo.com).
