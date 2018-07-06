# Security

Securing the repository should be possible using the same methods outlined in the [Satis documentation](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#security).

## Authentication

To provide a simple solution, SatisPress ships with support for HTTP Basic Authentication to protect packages and the `package.json`. Only users registered in WordPress will have access to the packages i.e. the credentials for the Basic Auth are any valid WordPress user (even a Subscriber role) credentials. 

## Third-party Authentication Providers

Third-party authentication providers that hook into the `determine_current_user` filter and take care to account for multiple authentication schemes should work with SatisPress.

## Capabilities

SatisPress introduces two new primitive capabilities:

 - `satispress_download_packages`
 - `satispress_view_packages`
 
 And two new meta capabilities:
 
 - `satispress_download_package`
 - `satispress_view_package`
  
 The primitive capabilities are added to the administrator role by default during plugin activation and upgrade.
 
 The meta capabilities are the ones that are checked at runtime. There's a mapping between the primitive and meta capabilities in the Capabilities provider.

[Back to Index](Index.md)
