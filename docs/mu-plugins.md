# Must-use (MU) Plugins

SatisPress doesn't provide support for mu-plugins since they usually require manual installation and aren't managed by the WordPress update process.

However, if you can install an mu-plugin as a regular plugin in your SatisPress instance, you can [force Composer to install it as an mu-plugin](https://getcomposer.org/doc/faqs/how-do-i-install-a-package-to-a-custom-path-for-my-framework.md) in your project.

As an example, the following configuration in `composer.json` would install SatisPress as an mu-plugin:


```json
{
  "extra": {
    "installer-paths": {
      "wp-content/mu-plugins/{$name}/": [
        "type:wordpress-muplugin",
        "cedaro/satispress"
      ]
    }
  }
}
```
