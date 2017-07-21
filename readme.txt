=== DF_FileConf ===
Contributors: hovenko
Donate link: http://www.hoven.ws
Tags: configuration, options
Requires at least: 2.1
Tested up to: 2.1.3
Stable tag: 1.0

With this plugin you can override options from the database by setting them
in a configuration file.

== Description ==

This plugin can be useful if you are to move your blog to a different site.
There are many options that needs to be changed if the URL of your blog changes,
at least 'siteurl' and 'home'. This is normally done manually updating the
values in the database table wp_options, since you can not reach the
WordPress administration sites if these URLs are wrong. If you use a plugin
that makes use of Google Maps you need to update the API key as well.

This plugin has only been tested with the WordPress 2.1 branch, but might
work with older versions. Please report on compatiblity with other versions.

== Installation ==

This section describes how to install the plugin and get it working.

1. Place the `df_fileconf` directory inside the `/wp-content/plugins/` directory
2. Inside the plugin directory copy the sample file `file-config.sample.php`
   to `file-config.php` and make appropriate changes according to the
   comments at the top of the file.
3. Activate the plugin through the 'Plugins' menu in WordPress


Your configuration variable $fileconf must be an array/map that should look
something like what is described at the top of the file
`file-config.sample.php`.

The array must be of key/value pairs where the key is the name of the option
from the database where the value is the value you wish to override the option
with. Beaware that options where the value is an array in both the database
and this configuration they will be merged together.

To completly replace an array option you need to first remove it completly by
adding the option name to the array with the key '__unset'.
Se the example below.


There are three options that control the plugin:

* `df_fileconf_debug` controls if changes should be printed and then the
  script dies.
* `df_fileconf_update` controls if changes should be wrote back to the database.
* `df_fileconf_active` controls if options from the configuration file should
  override options from the database.


Basicly the configuration should look something like this:

`
  global $fileconf;
  $fileconf = array(
      // Settings for this plugin
      'df_fileconf_debug'     => false,
      'df_fileconf_update'    => false,
      'df_fileconf_active'    => true,

      'siteurl'       =>  'http://localhost.localdomain',
      'home'          =>  'http://localhost.localdomain',
      '__unset'       =>  array(
                            'unwanted_array',
                          ),
  );
`

== Frequently Asked Questions ==

= Questions? =

Just like Jepordy, I hold all the answers, but I don't have the questions...

== Screenshots ==

No screenshots available.