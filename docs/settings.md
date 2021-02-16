# Settings

At _Settings &rarr; SatisPress &rarr; Settings_, you'll find the settings page:

![Screenshot of the SatisPress Settings page](images/settings.png)

## Vendor

When requiring a package from SatisPress, the default would be a package name like `satispress/genesis`.

The **Vendor** field allows this to be changed; a value of `mypremiumcode` would mean the `require` package name would be `mypremiumcode/genesis`.

Once you've started using a vendor name in your projects' `composer.json` manifests, it's a good idea to leave this setting alone. Otherwise you'll need to update every reference to the old vendor name and you may not be able to install dependencies if you need to check out an older version of a project.

[Back to Index](index.md)
