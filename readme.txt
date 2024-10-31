=== PageViews Counter ===
Contributors: bouk
Donate link: 
Tags: pageviews, counter
Requires at least: 5.3
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Implement your own pageviews counter in efficient way, using different backend storages such as MemcacheD or Redis.
 
== Description ==
 
There are deffinitely different methods for implementing pageview counters. Considering some external service such as [Jetpack](https://jetpack.com/) or [Google Analytics](https://analytics.google.com/) is surely a good option, but there may be scenarios where you may want to handle this just by yourself and have everything more under control.

Since it's very common to utilize some caching mechanism to improve your site's performance, implementing counter directly on PHP level wouldn't work reliably as counter would be barely incremeneted due to the caching. Better solution would be to use WordPress way of triggering AJAX requests and increment pageview counters anytime page is loaded, no matter if served from cache or not. 

When we start to think on bigger scale though, we find tradional WP AJAX implementation quite resources heavy as well. This plugin uses slightly more complicated method to increase counters, but it's very lightweight. In a nuthsell, counters are stored 'outside' of WordPress ecosystem into some fast storage such as Memcached or Redis. Then there's implemented re-occuring cron task on WordPress level, which regularly checks for new counters and store its values as postmeta for each respective post.

Plugin utilizes [PhpFastCache library](https://www.phpfastcache.com/) which allows to store data into many types of back-ends. See their [documentation](https://github.com/PHPSocialNetwork/phpfastcache/blob/master/docs/DRIVERS.md) for more details. This feature allows you to choose any back-end depending on your hosting provider.

This plugin comes preconfigured with file-based storage, which should work on any hosting environment, but for higher traffic sites you may want to consider in-memory storage such as MemcacheD.

Whole principle and idea is described in following [article](https://www.bouk.info/efficient-handling-of-ajax-requests-on-wordpress-platform/) published on my blog.

== Installation ==
 
1. Upload the contents of this .zip file into '/wp-content/plugins/pageviews-counter' on your WordPress installation, or via the 'Plugins->Add New' option in the Wordpress dashboard.
1. Activate the plugin via the 'Plugins' option in the WordPress dashboard.
1. Once activated new re-occuring cron task is scheduled, which automatically updates pageview counters for each post published. Plugin comes pre-configured for file-based storage, but you can easily change settings via WP_PVC_CONFIG constant added into wp-config.php. 

<pre><code>
define( 'WP_PVC_CONFIG', [
&nbsp;&nbsp;&nbsp;&nbsp;'driver'		=> 'memcached',
&nbsp;&nbsp;&nbsp;&nbsp;'configClass'	=> '\Phpfastcache\Drivers\Memcached\Config',
&nbsp;&nbsp;&nbsp;&nbsp;'prefix'        => 'my_prefix_',
&nbsp;&nbsp;&nbsp;&nbsp;'options'		=> [
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'host'  => '127.0.0.1',
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'port'  => 11211
&nbsp;&nbsp;&nbsp;&nbsp;]	
]);</code></pre>

== Frequently Asked Questions ==
 
== Screenshots ==

== Changelog ==

= 3.0.1 - 16th July 2024 =
* Adding pvc_update_views action allowing 3rd party plugins to hook in and potentially collect data for more in-depth analysis

= 3.0.0 - 18th April 2024 =
* Make sure object cache is updated when increasing counter
* Allow to define custom configuration via WP_PVC_CONFIG constant defined in wp-config.php. This prevents custom config being wiped out after plugin upgrade.

= 2.1.0 - 2nd March 2024 =
* Adding twig variable allowing for manual injection of counter script via Timber/Twig context

= 2.0.0 - 21st July 2023 =
* Removing dependency on jQuery
* Making plugin multisite compatible
* Making cache key more unique allowing to run plugin on single memcached instance and multiple sites for instance

= 1.1.3 - 26th May 2022 =
* Updating tested up to version

= 1.1.2 - 26th January 2022 =
* Updating tested up to version

= 1.1.1 =
* Updating tested up to version

= 1.1.0 =
* Updating PHP libraries

= 1.0.1 =
* Updating tested up to version

= 1.0.0 =
* First stable release of the plugin