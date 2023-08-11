=== HurryTimer PRO - An Scarcity Countdown Timer for WordPress & WooCommerce ===
Contributors: nlemsieh
Tags: Sale countdown, Countdown, Countdown for WooCommerce, Countdown timer, Evergreen countdown timer, regular countdown timer, Woocommerce countdown timer, Woocommerce scarcity, Scarcity, scarcity builder, Urgency countdown timer, Woocommerce, Increase sales, Pre-launch page, Landing page, Coming soon page
Requires at least: 4.0
Tested up to: 5.9
WC requires at least: 3.0
WC tested up to: 6.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Requires PHP: 5.6
Stable tag: 2.7.3

Create unlimited scarcity evergreen and regular countdown timers for WordPress and WooCommerce to boost conversions and sales instantly.

== Description ==

[HurryTimer](https://hurrytimer.com/) allows you to boost your conversions and sales with the "Fear Of Missing Out" marketing strategy, by using a powerful and easy-to-use urgency countdown timer.

#### What is Scarcity?

Scarcity is a scientifically proven technique to improve conversion rate. It creates a sense of urgency, triggers The "Feer Of Missing Out", and evoke people to act immediately.

### Features

* Unlimited Evergreen, Recurring, One-Time (regular) countdown timers for WordPress and WooCommerce.
* Powerful Cookie & IP detection technique.
* Place the countdown timer in different positions on the WooCommerce single product page.
* Shortcode support: Use the countdown timer anywhere on your website.
* Multiple countdown timers on the same page.
* Schedule Campaigns.
* Expiration actions: Redirect to URL, Hide countdown timer, Display a message, Change stock status, Hide "Add to cart" button.
* Multiple Actions: Take more than one action at the same time.
* Show a headline above or below the countdown timer.
* Restart the evergreen countdown automatically when expired: Restart immediately, or after page reload.
* Custom labels: days, hours, minutes, seconds.
* Ability to change every element's visibility.
* Live style customizer: Create unlimited looks.
* Custom CSS: Add your own custom CSS to every element.
* Sticky Bar.
* WooCommerce Display Conditions: Create a set of conditions to determine when countown timer will be displayed on product page.
* Call To Action Button.
* Hooks for developers.
* [See all features](https://hurrytimer.com/features)

### Usage

1. Visit "Hurrytimer > Add New Timer".
2. Choose between evergreen and regular mode.
3. Enter a period for "Evergreen" mode, or select a date and time for "Regular" mode.
4. Click on "Publish".
5. Copy shortCode and paste it into your post, page, or text widget content. You can also go to "WooCommerce" tab to integrate the countdown timer directly within a product page.


### Support

If need support, send us your question at support@hurrytimer.com or via [our support page.](https://hurrytimer.com/contact).

### Further reading

For more info check out the following:

* The [HurryTimer Plugin](https://hurrytimer.com/) official homepage.
* Follow HurryTimer on [Twitter](https://twitter.com/wp_hurrytimer).


== Frequently Asked Questions ==

== Screenshots ==

1. "Evergreen" mode settings.
2. "Recurring" mode settings.
3. "One-Time (regular)" mode settings.
4. Actions.
5. Add a countdown timer to a WooCommerce single product page.
6. Change visibility.
7. Custom labels.
8. Live style customization.
9. Custom CSS.

== Changelog ==

= 2.7.3 =

* Add compatibility with PHP 8.x
* Added a work-around to fix a PHP 8.1 compatibility issue with WP `dbDelta`.
* Added a new filter `hurrytimer_zero_padded_digits` to control digits padding.
* Added a new filter `hurrytimer_auto_pluralize` to automatically pluralize labels.
* Fixed a minor issue with custom CSS not being loaded properly.
* Added a new dynamic variable `{delivery_day}` to display the same/next day delivery.
* Various performance and stability improvements.

= 2.7.2 =

* Fixed an issue causing some daily timers to no work properly.

= 2.7.1 =

* Fix an issue causing a recurring timer to expire when the duration is less than the recurrence interval.

= 2.7.0 =

* Show total count of unit till the end when left unit is hidden.
* Add ability to pause monthly timer before restarting.
* Fix an issue when setting a monthly timer on a specific day of week.

= 2.6.9.1 =

* Fix plugin admin scripts conflict with some 3rd-party.

= 2.6.9 =

* Add the ability to set a custom duration for monthly campaigns.
* Minor bug fixes.

= 2.6.8.2 =

* Fix issue with custom css preview interfering with the campaign admin page. 
* Disable saving unfiltered HTML when `DISALLOW_UNFILTERED_HTML` is set to true.

= 2.6.8.1 =

* [WooCommerce] Fix an issue when using the "Hide to cart button" action and sticky bar. 

= 2.6.8 =

* [WooCommerce] Fix an issue when using the "Change stock status" with external products. 
* Fix an issue causing some daily timers to hide when the next day is unselected.


= 2.6.7 =

* [WooCommerce] Fix an issue with the expiry action "Hide add to cart button" not working on the product archive page. 
* [WooCommerce] Fix slow loading of the campaign admin page when there are too many coupons.
* Minor bugfixes.

= 2.6.6 =

* Fix an issue with expiry actions executed when editing in Elementor.
* Improve recurring timers.

= 2.6.5 =

* Fix 404 page when resetting or de/activating a campaign in multisite network.
* Remove strict URLs comparisons when showing sticky bar on specific URLs. 

= 2.6.4 =

* Fix a compatibility issue with Elementor pop-ups preventing timer from counting down after pop-up is loaded.

= 2.6.3 =

* Fix a bug causing some recurring campaigns to hide.

= 2.6.2 =

* Add a sub-option to the "Recur on" option to choose whether to countdown to next selected days or hide countdown timer. This is only available in daily, hourly, and minutely campaigns. 
In weekly campaigns the only possible action at this time is to hide countdown timer on unseleted days.

= 2.6.1 =

* Fix a bug causing some evergreen campaigns to reset on page reload.

= 2.6.0 =

* Add a new option to restart evergreen timer after a specific time
* Fix a conflict with some plugins/themes using Carbon
* Stability improvement

= 2.5.2 =

* Fix an issue with headline shortcodes not rendering.

= 2.5.1 =

* Fixed an issue with the "Reset countdown" for the admin not working properly when logged-in from another browser.
* Stability improvement

= 2.5.0 =
* Fixed an issue with JS and CSS code in the "Display message" not working properly.
* Improved recurring campaigns.

= 2.4.0 =

* Added monthly recurring.
* Improved recurring timers performance.
* Added compatibility with WooCommerce 5.0
* Stability improvement.

= 2.3.4 =
* Moved the license activation form under HurryTimer > License.
* Fixed an issue with selected coupon doesn't reflect under the Actions tab.
* Stability improvement.


= 2.3.3.1 =

- Fixed with an issue with 'Expire coupon' action locked.
- Added compatibility with 5.6.
- Stability improvement.


= 2.3.3 =

- Added the ability to choose which detection methods to use.
- Fixed an issue with Sticky bar excluded URLs not working properly.

= 2.3.2.1 =

- Fixed an issue with excluded pages URLs not saved properly under the sticky bar settings.

= 2.3.2 =
- Added an new feature to detect returning users by using session (experimental). To enable it use the filter: `add_filter('hurryt_enable_user_session_detection', '__return_true');`
- Stability improvement.

= 2.3.1.1 =

- Fixed an issue with excluded pages URLs not saved properly under the sticky bar settings.

= 2.3.1 =

- Fixed timer labels not showing up.

= 2.3.0 =

- Moved headline to Appearance > Elements > Headline. Now you can add campaign's name
- Added ability to reset evergreen timer on page refresh
- Added action "Expire coupon" for WooCommerce which allows to automatically expire a coupon code once timer reaches zero
- Added possibility to use shortcode when sticky bar is enabled
- Added ability to change sticky bar re-opening delay when closed by user
- Added ability to add rich-content in the "Display message" action
- Added possibility to use private products in WooCommerce integration
- Added new options in sticky bar settings
- Fixed issue with timer CSS being randomly deleted/cached after the plugin update
- Fixed issue with evergreen reset button not working properly
- Improved evergreen timer detection
- Improved appearance interface experience: "General" interface merged with "Elements" interface
- Enhanced color picker
- Other bugfixes and stability improvement

= 2.2.28 =

- Enhanced recurring.
- Stability improvement.

= 2.2.27 =

- Fixed menu position conflict.

= 2.2.26 =

- Fixed an issue with WooCommerce settings not displaying all products selection.
- Stability improvement.

= 2.2.25 =

- Fixed an issue with sticky bar not showing properly.

= 2.2.24 =

- Stability improvement.

= 2.2.23 =

- Fixed a bug causing evergreen timers to expire on page refresh for 32-bit/PHP 7.2.22.

= 2.2.22 =

- Added new JS lifecycle hooks for developers: `hurryt:pre-init`, `hurryt:init`, and `hurryt:started`.

= 2.2.21 =

- Fixed reset option doesn't reopen sticky bar.
- Added new javascript event `hurryt:finished` that trigger when timer is finished. 


= 2.2.20 =

- Fixed timer doesn't start when it's dynamically added to DOM. 

= 2.2.19 =

- Fix issue with actions with ajax requests.


= 2.2.18 =

- Stability and performance improvement.

= 2.2.17 =

- Stability and performance improvement.

= 2.2.16 =

- Internal dependencies support for PHP >=5.4

= 2.2.15 =

- Fixed minor issue with recurring mode.
- Universal end date through all timezones based on WP timezone.
- Added few helpful hooks

= 2.2.14 =

- Fixed minor issue with timezone

= 2.2.13 =

- Recurring mode improvements

= 2.2.12 =

- Redirect before showing page content

= 2.2.11 =
- Prevent interaction while redirecting

= 2.2.10 =
- Handle some undefined functions when using the slim build of jQuery.
- Fix admin menu position conflict with some plugins.


= 2.2.9 =

- Fixed minor issue causing duplicate countdown timer instance when using sticky bar on product page.

= 2.2.8 =

- Fixed minor causing `display on` not saved properly under Appearance > Sticky Bar. 

= 2.2.7 =

- Added two new filters for developers to control campaign display `hurryt_show_sticky_bar` to show/hide sticky bar and `hurryt_show_campaign` to show/hide the campaign. 

= 2.2.6 =

- Fixed minor bug when specifying pages in Sticky Bar. 

= 2.2.5 =

- [Fixed] Fix time-to-recur from the browser side.
- [Updated] Tested up to

= 2.2.4 =

- [Improved] Improved recurring mode when setting end option to "Never" for low-resource servers.

= 2.2.3 =

- [Added] Create a set of conditions to determine when a campaign will be displayed on selected products.

= 2.2.2 =

- [Fixed] Can't add additional action (bug since v2.2.0).
- [Fixed] "Show close button" not updated correctly.

= 2.2.1 =

- [Fix] Added a virtual limit when the end option is set to "Never", this will prevents script from crashing on an infinitely recurring rule, you can change the virtual limit using the filter `hurryt_recurring_vlimit` 

= 2.2.0 =

- [New] Create unlimited and customizable recurring countdown timers.
- [Added] Reset runnning evergreen countdown timers.
- [Added] New setting that allows you to disable actions when editing or previewing a page in the admin area.
- Minor Bugfixes and improvements.

= 2.1.8 =

* [fixed] Fixed a bug that add a delete permanently link to other posts table rows.
* [improved] Move campaign to trash instead of delete permanently.


= 2.1.7 =

* fixed an issue with sticky bar display

= 2.1.6 =

* fixed an issue with the previous version update


= 2.1.5 =

* Display sticky bar on selected products in WooCoommerce tab.
* Improved settings interface.
* Improved stability.

= 2.1.4 =

* [Fix] Call-to-Action text not visually changed in the settings.
* [Added] New filter hooks introduced for developers:  `hurryt_{$campaign_id}_campaign_headline`,  `hurryt_{$campaign_id}_campaign_cta_text`,  `hurryt_{$campaign_id}_campaign_cta_url`,  `hurryt_{$campaign_id}_campaign_timer_template`, and more.
* [Added] New `hurryt_{$campaign_id}_campaign_ended` action hook.


= 2.1.3 =

* [Fix] bugfix.
* Stability improvement.

 = 2.1.2 =

 * Improved Sticky bar.
 * Stability improvement.

 = 2.1.1 =

 * Fix a compatibility issue.

 = 2.1.0 =

  * Added Sticky bar
  * Added Call to Action.
  * New customization capabilities.
  * Inline display.

 = 2.0.4 =

  * Fix some actions that do not run correctly.

 = 2.0.3 =

  * Stability improvement.

  = 2.0.2 =

  * Fix a caching issue.

  = 2.0.1 =

* Live style customizer.
* Live custom CSS.
* Ability to change every element's visibility.
* New actions.
* Add more than one action at the same time.
* Stability improvement.

 == Upgrade Notice ==


= 2.6.7 =

This is update is advised for WooCommerce users.
