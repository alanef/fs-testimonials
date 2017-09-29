=== Freemius testimonial ===

Contributors: pootlepress, shramee, jamesmarsland
Plugin Name: Freemius testimonial
Plugin URI: https://github.com/pootlepress/pootle-cloud
Tags: freemius, testimonials, reviews, freemius api
Author URI: http://www.pootlepress.com
Author: PootlePress
Requires at least: 4.1.0
Tested up to: 4.8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Show featured testimonials for your Plugins/Themes integrated with Freemius.

== Description ==

Adds a simple shortcode to let you display featured testimonials for your Freemius plugins/themes on your WordPress website ;)

== Installation ==

Installing "Freemius testimonial" can be done either by searching for "Freemius testimonial" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org.
2. Upload the ZIP file through the "Plugins > Add New > Upload" screen in your WordPress dashboard.
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Visit the settings screen and configure, as desired.

== Frequently Asked Questions ==

= How can I show the testimonials? =

First you need to set your Freemius API credentials, this can be done in `WordPress admin area` > `Settings` > `General` > `Freemius testimonials – API credentials`
After that, just use this shortcode **after changing 999 with your plugin/theme ID** `[freemius-testimonials plugin=999]`

= How can I use that expandable drawer like thingy? =

Just add `compress` to have some of your reviews hidden by default, a button to display all reviews will be added ;)
You can use this shortcode after replacing 999 with your plugin ID `[freemius-testimonials compress plugin=999]`

== Screenshots ==

1. Reviews frontend display
2. Reviews compressed drawer
3. Admin settings area

== Upgrade Notice ==

= 1.0.0 =
 * Initial release

== Changelog ==

= 1.0.0 =
* 2017-10-05
* Initial release. It's alive!
