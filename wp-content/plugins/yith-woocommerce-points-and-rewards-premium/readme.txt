=== YITH WooCommerce Points and Rewards  ===

Contributors: yithemes
Tags: points, rewards, Points and Rewards, point, woocommerce, yith, point collection, reward, awards, credits, multisite, advertising, affiliate, beans, coupon, credit, Customers, discount, e-commerce, ecommerce, engage, free, incentive, incentivize, loyalty, loyalty program, marketing, promoting, referring, retention, woocommerce, woocommerce extension, WooCommerce Plugin
Requires at least: 5.7
Tested up to: 5.9
Stable tag: 3.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

YITH WooCommerce Points and Rewards allows you to add a rewarding program to your site and encourage your customers collecting points.

== Description ==

Have you ever started collecting shopping points? What was your reaction? Most of us are really motivated in storing as many points as possible, because so we can get more and often we do not care about spending more because, if we do, we can have a better reward. Hasn't this happened to you too? That's what you get by putting into your site a point and reward programme: loyalising your customers, encouraging them to buy always from your shop and being rewarded for their loyalty.
If you think that reward programmes were only prerogative of big shopping centres or supermarkets, you're have to change your mind, because now you can add such a programme to your own e-commerce shop too. How? Simple, with YITH WooCommerce Points and Rewards: easy to setup and easy to understand for your customers!


== Installation ==
Important: First of all, you have to download and activate WooCommerce plugin, which is mandatory for YITH WooCommerce Points and Rewards to be working.

1. Unzip the downloaded zip file.
2. Upload the plugin folder into the `wp-content/plugins/` directory of your WordPress site.
3. Activate `YITH WooCommerce Points and Rewards` from Plugins page.


== Changelog ==
= Version 3.7 - Released on 9 February 2022 =
* New: support for WooCommerce 6.3
* Update: YITH plugin framework
* Fix: removed expiration process if the assignment of points is manual
* Fix: fixed issue with full discount obtained using points and the option to reduce the points earned if a discount is applied
* Fix: show order id on history when extra points are earned after an order creation

= Version 3.6 - Released on 9 February 2022 =
* New: support for WooCommerce 6.2
* Update: YITH plugin framework
* Fix: fixed issue with points assignment if redeeming is disabled
* Fix: fixed issue with YITH Multi Currency Switcher for WooCommerce and redeeming points on cart
* Fix: fixed issue with removing points on totally refund

= Version 3.5.3 - Released on 27 January 2022 =
* Fix: fixed issue with WooCommerce Multilingual plugin

= Version 3.5.2 - Released on 26 January 2022 =
* Fix: fixed issue with PHP 7.x

= Version 3.5.1 - Released on 25 January 2022 =
* Fix: fixed issue with upload images for Levels and Banners
* Fix: fixed issue points message in single product page for variable product

= Version 3.5.0 - Released on 19 January 2022 =
* New: support for WordPress 5.9
* Update: YITH plugin framework
* Fix: Fixed issue with expired points and shared coupons used
* Fix: Fixed issue with wp cache
* Fix: Fixed issue to display banners on the "My Account" page with WPML
* Fix: fixed copy to clipboard style for Manage Points tab on on the "My Account" page

= Version 3.4.0 - Released on 4 January 2022 =
* New: support for WooCommerce 6.1
* Tweak: added "Points collected" and "Rank" information on settings panel
* Fix: assign points for referrals even if the order is placed by guests
* Fix: default value for expiration points option
* Fix: fixed issue with deleted products on get points with reviews banner

= Version 3.3.4 - Released on 23 December 2021 =
* Fix: fixed issues with cache plugins
* Dev: new filter 'ywpar_force_add_points_to_previous_orders' to force the assigment points to old orders during the bulk actions

= Version 3.3.3 - Released on 20 December 2021 =
* Fix: fixed WPML issue with referral link, now the link url changes based on current language
* Fix: fixed wrong value when remove points in backend
* Fix: fixed issue with Minimum cart amount to redeem points
* Fix: fix shortcode that shows the single product points message

= Version 3.3.2 - Released on 14 December 2021 =
* Fix: fixed issue with the daily email update points
* Fix: limit to use share points and redeem coupon in cart
* Fix: fixed birthdate extra points issue
* Fix: fixed function to get user roles
* Fix: fixed issue with round function

= Version 3.3.1 - Released on 10 December 2021 =
* Fix: show cart and checkout messages for guest if option is enabled
* Fix: issues in Point rules and Rewards rules
* Fix: fixed daily update points emails
* Fix: fixed an issue to prevent broken orders during checkout

= Version 3.3.0 - Released on 7 December 2021 =
* New: option on Point rules and Rewards rules not it is possible choose all product on sale or all products of specific tags
* Fix: reward message issue on checkout page
* Fix: max discount calculation with rewards rules

= Version 3.2.0 - Released on 3 December 2021 =
* New: support for WooCommerce 6.0
* Update: YITH plugin framework
* Fix: issue on checkout message
* Fix: issue with manual points assigment and My Account endpoint for Points and Rewards
* Dev: added a logger for expiring points

= Version 3.1.0 - Released on 29 November 2021 =
* New: integration with YITH Multi Currency Switcher for WooCommerce
* Tweak: renamed the sub-tab "Shortcodes" to "Ranking" and added the option to disable the ranking feature
* Fix: issue on customer history page when the points are added or removed from the customer
* Fix: issue with extra points for users that achieve specific levels settings
* Fix: added range of points earned for variation products

= Version 3.0.0 - Released on 25 November 2021 =
* New: levels and badges feature to rank and reward your customers
* New: shortcodes to show a ranking of the best customers that collected most points
* New: banners feature to push users to collect more points on "My Account" page
* New: points assignment rules that replace the old settings in product and category editors
* New: points redeeming rules that replace the old settings in product and category editors
* New: redesign of the admin panel
* New: new action "Unban users" inside the Customer Points > Bulk Actions tab
* New: Customer Points > Shortcodes tab to copy the plugin shortcodes to show a list of your customers' points
* New: option to calculate points considering product price with taxes included or excluded for points assignment and points redeeming
* New: extra points for the first daily login to daily reward the users whenever they log in
* New: extra points for completed profile to incentivize the users to complete all their profile fields on "My Account" page
* New: extra points for referrals to reward the users for every new user registered through their referral link
* New: extra points for referrals purchase to reward the users for every purchase made by users that have been referred through their referral link
* New: extra points for users who collect the most points in the week or month
* New: extra points for users that achieve specific levels
* New: "minimum discount users can get" option for the percentage discount reward conversion method
* New: integration with YITH WooCommerce Membership plugin
* Update: YITH plugin framework
* Dev: Totally refactoring code

= Version 2.3.0 - Released on 13 October 2021 =
* New: support for WooCommerce 5.8
* Update: YITH plugin framework

= Version 2.2.1 - Released on 27 September 2021 =
* Update: YITH plugin framework
* Fix: debug info feature removed for all logged in users

= Version 2.2.0 - Released on 10 September 2021 =
* New: support for WooCommerce 5.7
* Update: YITH plugin framework
* Fix: fixed option to assign different amounts of points based on the user role
* Dev: added filter 'ywpar_customers_tab_subtabs' to customize customers sub-tabs
* Dev: added filter 'ywpar_apply_points_on_previous_orders_bulk' to exclude some orders during the bulk action

= Version 2.1.3 - Released on 9 August 2021 =
* New: support for WooCommerce 5.6
* Tweak: added 'show_worth' attribute inside [yith_points] shortcode
* Update: YITH plugin framework

= Version 2.1.2 - Released on 24 June 2021 =
* New: Support for WordPress 5.8
* New: support for WooCommerce 5.5
* Update: YITH plugin framework
* Tweak: added currency info to the percentage redeem conversion field
* Fix: fixed points calculations on "My Account" points history
* Fix: fixed style issue in customers tab options
* Dev: added the parameter 'type' to the hook 'ywpar_get_point_earned_price'

= Version 2.1.1 - Released on 10 June 2021 =
* Update: YITH plugin framework
* Fix: fixed issue on auto redeeming points on cart
* Fix: fixed issue adding WooCommerce email css style

= Version 2.1.0 - Released on 27 May 2021 =
* New: support for WooCommerce 5.4
* Update: YITH plugin framework
* Fix: fixed email css using woocommerce filters
* Fix: fix empty notice in cart
* Dev: added new filter 'ywpar_default_container'

= Version 2.0.9 - Released on 11 May 2021 =
* New: support for WooCommerce 5.3
* Update: YITH plugin framework
* Fix: fixed missing tax in total while removing earned points on order refund
* Fix: fixed issue on calculation in customer history view
* Fix: fixed duplicated messages on cart on update cart totals and on adding to cart from cart page

= Version 2.0.8 - Released on 20 April 2021 =
* New: support for French language

= Version 2.0.7 - Released on 13 April 2021 =
* New: support for WooCommerce 5.2
* Update: YITH plugin framework
* Fix: managed some filter to correctly change the max discount worth value
* Fix: added class to show the points options on gift card product editing page
* Dev: added filters 'ywpar_reward_message_format', 'ywpar_commission_points_for_affiliate' and 'ywpar_product_points_formatted'

= Version 2.0.6 - Released on 4 March 2021 =
* New: support for WordPress 5.7
* New: support for WooCommerce 5.1
* Update: YITH plugin framework
* Fix: fixed during the order refund
* Fix: fixed issue in threshold message shortcode
* Fix: fixed on discount option in product category
* Fix: fixed points worth calculation
* Dev: added filter 'ywpar_my_account_worth_value'


= Version 2.0.5 - Released on 4 February 2021 =
* New: support for WooCommerce 5.0
* Tweak: added multi currency support for extra points
* Update: YITH plugin framework
* Dev: added filter 'ywpar_max_discount_redeem_message' to manage max_discount placeholder in redeem message

= Version 2.0.4 - Released on 31 Dec 2020 =
New: Support for WooCommerce 4.9
Update: plugin framework
Fix: Managed guest visibility in elementor widget for single product page message
Fix: Shortcode visibility for guests
Dev: Added new filters 'ywpar_include_tax_totals_on_point_calculation' and 'ywpar_apply_previous_order_posts_per_page'

= Version 2.0.3 - Released on 3 Dec 2020 =
New: Support for WooCommerce 4.8
Update: plugin framework
Fix: Added a check for expiring points cron actions
Fix: Points options on product category editor
Fix: Fixed integration with WooCommerce Currency Switcher
Fix: Max discount on product category
Dev: Added filter 'ywpar_customer_history_limit' to change the limit of customer history results on frontend (My Points Page)

= Version 2.0.2 - Released on 6 Nov 2020 =
New: Support for WordPress 5.6
New: Support for WooCommerce 4.7
Update: plugin framework
Fix: Removed WooCommerce Alert on changing page
Fix: Fixed point message label on frontend

= Version 2.0.1 - Released on 24 Oct 2020 =
Fix: check for old option
Fix: order query with option to check past orders on new user registration
Fix: shortcode style
Update: plugin framework

= Version 2.0.0 - Released on 22 Oct 2020 =
New: added filter in Customers' Points Table
New: Messages Tab Options moved to General Options Tab
New: Redeem Options moved to Redeem Points Section
New: Redeem Message moved to Redeem Options Message
New: new style for Reward Message in Cart/Checkout
Tweak: improved admin panel and settings UX
Tweak: improved email messages style
Tweak: improved My Account Points section style
Update: plugin framework
Update: All language files

= Version 1.8.3 - Released on 7 Oct 2020 =
New: Support for WooCommerce 4.6
Update: Plugin Framework
Fix: Refund issue
Dev: Added filter 'ywpar_flush_cache filter'

= Version 1.8.2 - Released on 17 Sep 2020 =
New: Support for WooCommerce 4.5
Update: Plugin Framework

= Version 1.8.1 - Released on 11 Aug 2020 =
New: Support for WordPress 5.5
New: Support for WooCommerce 4.4
Update: Plugin Framework
Fix: Fixed points label on My Account order view and emails
Fix: Points and rewards message on checkout page
Fix: Issue with YITH WooCommerce Product Bundles

= Version 1.8.0 - Released on 2 Jul 2020 =
New: Support for WooCommerce 4.3
New: Support for YITH Subscription 2.0
Tweak: Firing rules rewriting only on Myaccount section
Tweak: Added Points endpoint label for breadcrumbs
Update: Plugin Framework
Update: Language Files
Fix: Fixed points label on my account order view
Dev: Added filter 'ywpar_prevent_extra_points' and 'ywpar_add_affiliate_commission_points'
Dev: Added filter ywpar_clear_current_coupon for compatibility with WooCommerce Subscriptions

= Version 1.7.9 - Released on 26 May 2020 =
New: Support for WooCommerce 4.2
New: Added point settings inside YITH WooCommerce Account Funds products
New: Added new option to show a message for Checkout Threshold - Extra Points
Update: Plugin Framework
Fix: issue with updating options

= Version 1.7.8 - Released on 11 May 2020 =
Update: Plugin Framework
Fix: Fixed issues with rewarding points.

= Version 1.7.7 - Released on 28 April 2020 =
New: Support for WooCommerce 4.1
Update: Plugin Framework
Update: Language Files
Fix: Changed the admin notice for not active coupons


= Version 1.7.6 - Released on 19 March 2020 =
New: Support for Elementor
Tweak: Added admin notice if WooCommerce Coupons are disabled
Update: Plugin Framework
Update: Language Files
Fix: Integration with Aelia Currency Switcher for WooCommerce
Fix: Untranslated String

= Version 1.7.5 - Released on 9 March 2020 =
Fix: Fixed Total point message 

= Version 1.7.4 - Released on 6 March 2020 =
New: Support for WordPress 5.4
New: Support for WooCommerce 4.0
Update: Plugin Framework

= Version 1.7.3 - Released on 12 February 2020 =
New: Checkout total Thresholds ( gain points by Checkout totals)
New: Added new option to assign points to a new registered user if there are previous orders with the same email as billing email
Tweak: Added sanitize function for My Points endpoint so it will always have uppercase and spaces
Tweak: Translate role names automatically
Update: Plugin Framework
Update: Language Files
Fix: Fixed expired points calculation
Fix: Fixed conversion rate issue

= Version 1.7.2 - Released on 13 January 2020 =
Update: Plugin Framework
Fix: Fixed MyAccount Points message
Fix: Message after the excerpt position in single product page


= Version 1.7.1 - Released on 03 January 2020 =
New: Integration with Proteo Theme
Update: Language files

= Version 1.7.0 - Released on 02 January 2020 =
New: Support for WooCommerce 3.9
New: Added option to disable points awarding while redeeming
New: Added a new option in Extra Points - Birthday date, in order to select where to show the field
New: Added some options to Points Updated Email settings: to send an email daily (Cron) or as soon as points are updated.
New: New option - Auto apply points on cart/checkout page
New: Added new email placeholders
Tweak: Added security check on Edit Account details for Birthday date field
Tweak: New panel settings style
Update: Plugin Framework
Update: Language files
Fix: Birthday date field impossible to reset in user backend edit forms
Fix: Fixed back-compatibility with WooCommerce 3.6.4 with older function 'get_used_coupons' to prevent fatal errors
Fix:Added a check on the calculation of expiring points
Fix: Fixed worth points information, when reward is set as a percentage
Dev: Added filter to Import method to save the database log with imported points: ywpar_save_log_on_import
Dev: Added filter ywpar_import_description_label to Import action in order to provide a description for import operation: ywpar_import_description_label


= Version 1.6.6 - Released on 30 October 2019 =
New: added points value as money on My Points (my account) view; for example, you have 220 Points (worth 2,20€)
New: added Points Rounding Option
Update: Plugin Framework
Update: Spanish language


= Version 1.6.5 - Released on 29 October 2019 =

New: Support for WordPress 5.3
New: Support for WooCommerce 3.8
Update: Plugin Framework
Update: Language files
Fix: Sanitize "ywpar_affiliates_earning_conversion" option
Fix: Replaced deprecated function get_used_coupons() by get_coupon_codes()
Fix: Replaces double line-breaks with paragraph elements on expired points email
Fix: Fixed ajax call on rewards messages
Fix: Added option to assign points when an order is made in guest mode but from a registered user (checking the email address)


= Version 1.6.4 - Released on 31 July 2019 =
New: Support for WooCommerce 3.7
Update: Plugin Framework
Fix: Added check for birthday points
Dev: Added new filter 'ywpar_product_points_formatted', 'ywpar_override_points_label', 'ywpar_calculate_points_for_product'

= Version 1.6.3 - Released on 05 July 2019 =
Update: Plugin Framework
Update: Language files
Fix: Conversion rate for Role on redeem points
Fix: Added failed to completed to complete check redeem points
Fix: Fixed allowed rules on redeem points
Fix: Fixed plural forms for translation
Dev: Added new filter ywpar_check_ywpar_coupon_before_remove and ypar_extrapoints_renew_num


= Version 1.6.2 - Released on 20 May 2019 =
Update: Plugin Framework
Fix: Message on cart with minimum point required
Fix: Enable/disable rewards points
Dev: New filter 'yith_ywpar_action_label'

= Version 1.6.1 - Released on 05 April 2019 =
New: Support for WooCommerce 3.6
Update: Plugin Framework
Update: Language files
Fix: Added subtotal check on minimum amount for redeeming
Fix: Extra points on registration even if option disabled
Fix: Added error message on reedem minimum point
Dev: Added new action 'ywpar_before_apply_discount_calculation'

= Version 1.6.0 - Released on 18 February 2019 =
New: Ban/Unban users
New: Reset Points to users
New: Now it is possible to show the points spent and earned on my account order detail page and inside the email of order complete
New: Set the order status then the points will be assigned to the customer
New: Added an option to exclude products on sale to earn points
New: Extra points rule for birthday
New: Bulk actions tab
New: Manual update of points with description
New: Integration with YITH WooCommerce Subscription
New: Integration with YITH WooCommerce Affiliates
Tweak: Panel Settings
Update: Plugin Framework
Update: Language files

= Version 1.5.8 - Released on 05 December 2018 =
New: Support for WordPress 5.0
Update: Plugin Framework
Fix: Apply point message does now show after removed coupon
Fix: Fixed max points if conversion rate is in percentual
Fix: Update discount on update quantity item in cart
Dev: fixing a non numeric value warning in the gift card product page

= Version 1.5.7 - Released on 23 October 2018 =
Update: Plugin Core 3.0.28
Update: Language files

= Version 1.5.6 - Released on 16 October 2018 =
New: Support for WooCommerce 3.5.0 RC2
Update: Plugin Framework
Update: Language files
Fix: Issue with minimum amount and minimum discount to redeem
Fix: Possible warning with PHP 7.2.x

= Version 1.5.5 - Released on 26 September 2018 =
Dev: Added new filters 'ywpar_export_csv_first_row' and 'ywpar_export_csv_row'
Update: Plugin Framework
Update: Language files
Fix: Issues with WooCommerce Multilingual

= Version 1.5.4 - Released on 22 August 2018 =
New: Support for WordPress 4.9.8
Dev: New filter 'ywpar_discount_applied_message'
Dev: New filter 'ywpar_approx_function'
Dev: New filter ywpar_update_wp_cache and force update user meta replacing the wp user cache
Update: Plugin Framework
Update: Language files
Fix: Issue with rewards coupons with WooCommerce Multilingual
Fix: Issue with PHP 7.2
Fix: Fixed non-numeric value on adding points to user for first time when $pointsvar is empty
Fix: Counter on repeating rules for extra points
Fix: Added missing string for WPML

= Version 1.5.3 - Released on 17 May 2018 =
New: Support for WordPress 4.9.6
New: Support for WooCommerce 3.4
New: Integration with WooCommerce Currency Switcher version 1.2.4
Dev: New filter 'ywpar_points_earned_in_category', 'ywpar_coupon_label to change coupon label', 'ywpar_before_currency_loop'
Dev: New filters 'ywpar_get_point_earned_price', 'ywpar_before_rewards_message','ywpar_calculate_rewards_discount_max_discount_fixed'
Update: Plugin Framework
Update: Language files
Fix: Amount redeemed when order cancelled or failed
Fix: Get back redeemed points when an order is cancelled
Fix: Percentual symbol Reward Percentual Conversion Rate
Fix: Free shipping on redemption
Fix: Empty value warning

= Version 1.5.2 - Released on 13 March 2018 =
Update: Language Files
Fix: option 'Hide points message for guest' wasn't working correctly

= Version 1.5.1 - Released on 28 February 2018 =
Tweak: Conversion update
Fix: Dashboard Widget

= Version 1.5.0 - Released on 23 February 2018 =
New: My Account Page Endpoint
New: Integration with WooCommerce Multilingual from version 4.2.9
New: Integration with Aelia Currency Switcher for WooCommerce from version 4.5.14
Update: Plugin Framework
Update: Language Files
Fix: Earnings points message doesn't update in checkout page after a points coupons applied
Fix: Coupon Rewards Points issue
Fix: Wrong processing status slug

= Version 1.4.3 - Released on 04 February 2018 =
Update: Plugin Framework
Fix: Minimized javascript file on administrator panel
Fix: Load scripts only on settings panel

= Version 1.4.2 - Released on 29 January 2018 =
New: Support for WooCommerce 3.3
Update: Plugin Framework
Dev: New filter 'ywpar_calculate_rewards_discount_max_discount'
Dev: New filter 'ywpar_calculate_rewards_discount_max_points'
Fix: Dutch support
Fix: Calculation Worth price
Fix: Points redeeming issue

= Version 1.4.1 - Released on 21 December 2017 =
Update: Plugin Framework
Dev: Added filter 'ywpar_change_coupon_type_discount'
Fix: Subtotal calculation
Fix: Calculation percentual discount

= Version 1.4.0 - Released on 11 December 2017 =
Update: Plugin Framework
Fix: Points earned for order
Fix: Points not displayed if a variation has 0
Fix: Calculation discount in reward points percentual
Fix: Rewards points calculation


= Version 1.3.1 - Released on 17 August 2017 =
New: Support for WooCommerce 3.2
New: Dutch support
Dev: Added 'ywpar_rewards_conversion_rate'
Update: Plugin Framework
Fix: Shortcode point list
Fix: Double points issue when an order pass from cancelled to completed status
Fix: Fix max discount amount in percentage redeem points

= Version 1.3.0 - Released on 17 August 2017 =
New: Support for WooCommerce 3.1.2
New: Option to choose how use WooCommerce Coupons and Rewards Points
New: Export points
New: German support by Alexander Cekic
Fix: Rewrite expiration system
Fix: Show/Hide Messages to Guest
Fix: Variable products points calculation
Fix: Product points calculation
Dev: New filter 'yith_par_messages_class' to customize woocommerce messages class
Dev: New filter 'ywpar_hide_messages' to customize show/hide messages
Dev: New filter 'ywpar_previous_orders_statuses' to add custom order statuses for previous order points redeem


= Version 1.2.7 - Released on 26 May 2017 =
New: Export user/points from database
Fix: Show message in cart to reedeem points

= Version 1.2.6 - Released on 26 May 2017 =
Fix: Method to calculate price worth

= Version 1.2.5 - Released on 25 May 2017 =
New: Support for WooCommerce 3.0.7
Fix: Coupons to Redeem points
Fix: Fix previuos orders price
Fix: Removed earning points in YITH Multivendor Suborders when vendor's orders are synchronized
Fix: Message in single product page for variable products
Dev: moved filter ywpar_set_max_discount_for_minor_subtotal
Dev: added filter ywpar_set_percentage_cart_subtotal
Dev: added wrapper for my-account elements


= Version 1.2.4 - Released on 05 May 2017 =
New: Support for WooCommerce 3.0.5
New: Added option to reassign redeemed points for total refund
Fix: Import points from previous orders
Fix: Readded options to enable point removal for total or partial refund
Fix: Shop Manager capabilities


= Version 1.2.3 - Released on 28 April 2017 =
New: Support for WooCommerce 3.0.4
Fix: Filter of customer in Customer Points tab
Update: Plugin Framework

= Version 1.2.2 - Released on 12 April 2017 =
New: Support for WooCommerce 3.0.1
Update: Plugin Framework
Fix: Error with coupons
Fix: Remove points redeemed

= Version 1.2.1 - Released on 04 April 2017 =
New: Support for WooCommerce 3.0
Tweak: Changed registration date with local registration date
Update: Plugin Framework
Fix: Error with php 5.4
Dev: Added filter 'ywpar_points_registration_date'

= Version 1.2.0 - Released on 16 March 2017 =
New: Support for WooCommerce 3.0 RC 1
New: Compatibility with AutomateWoo - Referrals Add-on 1.3.5
New: Spanish translation
Tweak: Refresh of messages after cart updates
Update: Plugin Framework
Fix: Update messages on the cart page


= Version 1.1.4  - Released on 25 January 2017 =
Fix: Calculation points when the category overrides the global conversion
Fix: Calculation price discount in fixed conversion value
Dev: Changed the style class 'product_point' with 'product_point_loop'
Dev: Added method 'calculate_price_worth' in class YITH_WC_Points_Rewards_Redemption
Dev: Added method 'get_price_from_point_earned' in class YITH_WC_Points_Rewards_Earning

= Version 1.1.3  - Released on 21 December 2016 =
New: Option to enable shop manager to edit points
New: A placeholder {price_discount_fixed_conversion} for message in single product page
New: An option to change the label of button "Apply Discount"
New: An option to select the rules that earning the points
New: An option to select the rules that redeem the points
New: An option to show points in loop
New: Message to show points earned in order pay
New: A filter 'ywpar_enabled_user' to enable or disable user
New: An option to choose if free shipping allowed to redeem
Tweak: Compatibility with YITH WooCommerce Email Template
Tweak: Calculation points on older orders if product doesn't exists
Fix: Overriding of points earned in variations
Fix: Removed earning points in YITH Multivendor Suborders
Fix: Update points to redeem when the cart is updated
Fix: Email expiring content
Fix: Earning point message on cart if a totally discount coupon is applied

= Version 1.1.2  - Released on 24 March 2016 =
New: The return of points redeemed to the cancellation of the order
New: Options on products and categories to override the rewards conversion discounts
Tweak: Improvement Product Points calculation changed floor by round
Fix: Javascript error in frontend.js

= Version 1.1.1  - Released on 14 March 2016 =
New: Button to reset points
New: Change points values when variation select change
Tweak: Improvement Product Points calculation
Udate: Label of options in administrator panel

= Version 1.1.0 - Released on 08 March 2016 =
Update: Plugin Framework
Fix: Calculation earned points is a Dynamic Pricing and Discount rule is applied
Fix: Moved ob_start() function in update send_email_update_points() method
Fix: Update merge of default options with options from free version

= Version 1.0.9 - Released on 29 February 2016 =
New: Option to redeem points with percentual discount
New: Option to remove the possibility to redeem points
New: Option to add a minimum amount discount to redeem points

= Version 1.0.8 - Released on 11 February 2016 =
New: filter ywpar_get_product_point_earned that let third party plugin to set the point earned by specific product

= 1.0.7 - Released on 05 February 2016 =
New: Shortcode yith_ywpar_points_list to show the list of points of a user
New: Option to hide points in my account page
Fix: Pagination on Customer's Points list

= 1.0.6 - Released on 01 February 2016 =
Fix: Calculation points when coupons are used

= 1.0.5 - Released on 26 January 2016 =
New: Option to remove points when coupons are used
New: Earning Points in a manual order
New: In Customer's Points tab all customers are showed also without points
New: Compatibility with YITH WooCommerce Multi Vendor Premium hidden the points settings on products for vendors
Fix: Removed Fatal in View Points if the order do not exists
Fix: Conflict js with YITH Dynamic Pricing and Discounts
Fix: Refund points calculation for partial refund
Fix: Extra points double calculation

= 1.0.4 - Released on 07 January 2016 =
New: Compatibility with WooCommerce 2.5 RC1
Fix: Redeem points also if the button "Apply discount" is not clicked
Fix: Calculation points on a refund order
Fix: Update Points content

= 1.0.3 - Released on 14 December 2015 =
New: Compatibility with Wordpress 4.4
Update: Changed Text Domain from 'ywpar' to 'yith-woocommerce-points-and-rewards'
Update: Plugin Framework
Fix: Extra points options
Fix: Reviews assigment points for customers
Fix: String translations

= 1.0.2 - Released on 30 November 2015 =
Update: Plugin Framework
Fix: Enable/Disable Option
Fix: Double points assigment


= 1.0.1 - Released on 23 September 2015 =
New: Minimun amount to reedem
New: Italian Translation

= 1.0.0 - Released on 17 September 2015 =
Initial release
