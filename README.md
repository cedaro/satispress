# SatisPress

A WordPress plugin that can generate a Composer repository from installed plugins and themes.

## What is it?

In the PHP world, open source packages can be registered on [Packagist](https://packagist.org/), a dynamic repository which reads from GitHub and other sources, and allows packages to be retrieved via Composer.

However, authors of premium packages, such as some WordPress plugins and themes, don't want to make their code available like this, so customers can't pull them down with Composer like they would with other packages.

There are typically two options for developers who want to streamline their site initialisation workflow:
 - a [Private Packgist](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#private-packagist) service, a commercial package hosting product offering professional support and web based management of private and public packages, and granular access permissions. This has a financial cost.
 - A [Satis](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#satis) install. Satis is open source, but is only a static `composer` repository generator. It is a bit like an ultra-lightweight, static file-based version of packagist and can be used to host the metadata of your company's private packages, or your own. Managing this has a time cost. 
 
 SatisPress combines the two - a self-hosted Satis-like dynamic generation repository generator, based on WordPress.    

## Why a WordPress Installation?

Many plugins and themes don't have public repositories, so managing them with Composer can be a hassle. Instead, SatisPress allows you to manage them in a standard WordPress installation, leveraging the built-in update process to handle the myriad licensing schemes that would be impossible to account for outside of WordPress.

The whitelisted packages are exposed via an automatically generated `packages.json` for inclusion as a Composer repository in a project's `composer.json` or even your own `satis.json`.

For those familiar with WordPress, creating a new WordPress install on a subdomain or unused domain is a relatively light time investment. Once set up, the only administration is to add new plugins or themes when needed. 

## Documentation

For installation notes, and information about usage, security and more, see the [Documentation](docs/Index.md).

## Credits

Created by [Brady Vercher](https://www.blazersix.com/) and supported by [Gary Jones](https://gamajo.com).
