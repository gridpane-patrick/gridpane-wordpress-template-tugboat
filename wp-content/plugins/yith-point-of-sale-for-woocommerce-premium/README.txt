=== YITH Point of Sale for WooCommerce ===

== Changelog ==

= 1.0.14 - Released on 5 March 2021 =

* New: support for WordPress 5.7
* New: support for WooCommerce 5.1
* New: show payment methods in order details
* New: show payment methods in receipts
* New: show change in order details
* New: show change in receipts
* Update: YITH plugin framework
* Update: language files
* Fix: show notes in order items
* Fix: issue with dark color
* Fix: login time issue with Safari
* Fix: issue with saved carts
* Fix: issue when generating cart ID

= 1.0.13 - Released on 4 February 2021 =

* New: support for WooCommerce 5.0
* Update: YITH plugin framework
* Update: language files
* Fix: issue with saved carts
* Fix: date issue when timezone offset is negative
* Fix: issue with custom colors in rgb format
* Dev: added yith_pos_customer_info_box_shipping_required_fields filter

= 1.0.12 - Released on 30 Dec 2020 =

* New: support for WooCommerce 4.9
* Update: plugin framework
* Update: language files
* Fix: issue with timezone when grouping orders by date in order history

= 1.0.11 - Released on 02 Dec 2020 =

* New: support for WordPress 5.6
* New: support for WooCommerce 4.8
* Update: plugin framework
* Update: language files
* Fix: empty cashier label when creating the default receipt
* Fix: notice when taking-over the register
* Fix: login time based on the site timezone
* Dev: added yith_pos_customer_info_box_details filter
* Dev: added yith_pos_receipt_order_data_elements filter

= 1.0.10 - Released on 30 Oct 2020 =

* New: support for WooCommerce 4.7
* Update: plugin framework
* Update: language files
* Fix: menu background overlay after closing the calculator
* Tweak: fixed notice for catalog visibility on variations

= 1.0.9 - Released on 07 Oct 2020 =

* New: show 'Total Sales' in dashboard
* Update: plugin framework
* Update: language files
* Fix: 'Customers' tab in dashboard

= 1.0.8 - Released on 06 Oct 2020 =

* New: support for WooCommerce 4.6
* Update: plugin framework
* Update: language files
* Fix: store VAT field in customer billing and in order billing address
* Fix: check int values for quantities in POS Cart
* Dev: added yith_pos_cart_raw_cart_item_key filter
* Dev: added yith_pos_receipt_before_tax_line filter

= 1.0.7 - Released on 17 Sep 2020 =

* New: support for WooCommerce 4.5
* Update: plugin framework
* Update: language files

= 1.0.6 - Released on 13 Ago 2020 =

* New: support for WordPress 5.5
* Update: plugin framework
* Update: language files
* Fix: meta_data issue in combination with WooCommerce 4.3.2
* Fix: issue when creating a new user through POS in combination with YITH WooCommerce Customize My Account Page
* Fix: issue when calculating Popular Tendered for some currencies such as 'Mauritian rupee'
* Fix: show fee and discounts in order details
* Fix: take Refunds into account when retrieving reports
* Fix: issue when importing products with Catalog Visibility set to 'POS results only'
* Tweak: stylized focused fields in Manage Cash
* Tweak: show order note in order details and receipts
* Tweak: changed label for Receipt option in registers and title of the default receipt automatically created
* Tweak: improved receipt print
* Dev: added yith_pos_receipt_show_order_note filter
* Dev: added filters for getters of Store and Receipt
* Dev: added yith_pos_allow_out_of_stock_products_when_scanning filter
* Dev: added yith_pos_header_links filter
* Dev: added yith_pos_dashboard_order_charts filter

= 1.0.5 - Released on 03 Jul 2020 =

* New: support for WooCommerce 4.3
* Update: plugin framework
* Update: language files
* Fix: scrolling issues in category view
* Fix: tax rounding issue
* Fix: issues when some payment methods have empty amount when paying through POS
* Fix: scan product by SKU when there are restrictions for categories in Register
* Tweak: prevent notices when retrieving orders through REST API
* Tweak: added login messages and errors
* Tweak: improved search speed
* Tweak: set the default value for 'tax status' field to 'Enabled' when creating a new product in POS
* Tweak: improved search by SKU when scanning a product
* Tweak: fixed style issue in placeholders of select fields in Registers
* Tweak: limit the 'Popular Tendered' suggestions to 6 to prevent style issues
* Dev: added yith_pos_order_processed_after_showing_details action
* Dev: added yith_pos_default_selected_payment_gateway filter
* Dev: added yith_pos_coupon_custom_discount_amount filter
* Dev: added yith_pos_coupon_custom_discounts_array filter
* Dev: added yith_pos_is_product_coupon filter
* Dev: added yith_pos_is_cart_coupon filter
* Dev: added yith_pos_coupon_is_valid_for_product filter
* Dev: added yith_pos_coupon_is_valid_for_cart filter
* Dev: added yith_pos_cart_item_product_name filter
* Dev: added yith_pos_show_stock_badge_in_search_results filter
* Dev: added yith_pos_receipt_order_item_name_quantity filter
* Dev: added yith_pos_header_menu_items filter
* Dev: added yith_pos_receipt_order_item_price filter
* Dev: added yith_pos_product_list_query_args filter
* Dev: added yith_pos_product_section_tabs filter
* Dev: added yith_pos_search_include_variations filter
* Dev: added yith_pos_search_include_searching_by_sku filter
* Dev: added yith_pos_scan_product_tab_active_default filter
* Dev: added yith_pos_new_product_default_data filter
* Dev: added yith_pos_customer_to_update filter
* Dev: added yith_pos_customer_use_email_as_username filter
* Dev: added yith_pos_customer_to_create filter
* Dev: added yith_pos_cart_item_product_price filter in react

= 1.0.4 - Released on 14 May 2020 =

* New: support for WooCommerce 4.2
* New: restock items automatically after refunds
* Update: plugin framework
* Update: language files
* Fix: issue when adding cash-in-end and closing the register
* Fix: issue when editing customer
* Dev: added yith_pos_product_get_meta filter in React
* Dev: added yith_pos_show_price_including_tax_in_receipt filter
* Dev: added yith_pos_show_tax_row_in_receipt filter

= 1.0.3 - Released on 22 April 2020 =

* New: support for WooCommerce 4.1
* New: French translation (thanks to Josselyn Jayant)
* New: Greek translation
* Fix: show dates in correct language
* Fix: empty search field after scanning a product
* Fix: issue when changing order status for orders including custom products
* Fix: issue when reducing the stock of products without multi-stock options set
* Fix: search results width and height in small screens
* Fix: RTL style
* Fix: undefined variable error in store wizard summary
* Fix: issue when activating the plugin in the network
* Tweak: improved popular tendered behavior
* Tweak: prevent register-closing call failure by waiting for closing before redirect
* Dev: added yith_pos_show_itemized_tax_in_receipt filter

= 1.0.2 - Released on 3 March 2020 =

* New: support for WordPress 5.4
* New: support for WooCommerce 4.0
* New: show prices including/excluding taxes based on WooCommerce settings
* New: italian translation
* New: spanish translation
* New: dutch translation
* Update: plugin framework
* Fix: language option in combination with WPML
* Fix: multi-stock option in Product Edit page
* Fix: show correct currency in 'cash in hand' window
* Fix: show iOS body class for iOS devices only
* Fix: multi-stock issue with variable products
* Tweak: improved search
* Tweak: remove 'Cashier' and 'Manager' roles automatically whenever users are removed from Cashiers or Managers from the store settings.

= 1.0.1 - Released on 13 February 2020 =

* New: order status set to 'Processing' if the order includes shipping lines, otherwise it'll be set to 'Completed'
* Fix: password issue when creating a customer
* Fix: issue with admin capabilities
* Tweak: improved category exclusion in registers
* Tweak: improved barcode behaviour after scanning the product
* Tweak: filter by YITH POS or online shown as select
* Tweak: added a default receipt when installing the plugin for the first time
* Tweak: prevent errors if using an outdated version of WooCommerce Admin
* Tweak: added control to check if the browser is supported
* Tweak: improved style
* Tweak: removed mandatory option for pos gateway payments on WooCommerce Settings Payment
* Tweak: play sound when changing product quantity
* Dev: added yith_pos_order_status filter

= 1.0.0 - Released on 05 February 2020 =

* Initial release
