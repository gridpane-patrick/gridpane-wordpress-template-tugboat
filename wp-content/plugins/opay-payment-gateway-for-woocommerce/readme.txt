=== OPay Payment Gateway for WooCommerce  ===
Contributors: opaycheckout
Donate link: https://doc.opaycheckout.com
Tags: woocommerce,OPay,Payment,Payment Gateway
Requires at least: 5.1
Tested up to: 5.8
Stable tag: 2.5.5
Requires PHP: 7.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
<strong>Give your Customer the Easiest and Smartest payment solutions ever</strong>

OPay WooCommence plugin supports consumers to pay with Bank Card, Reference Code, Shahry, ValU, Mobile Wallets, Bank Installment and OPay Now(Buy now Pay latter). With a few steps you can manage different payment methods through your Dashboard.Businesses could quickly establish their own websites on WordPress and integrate OPay Gateway payment methods quickly through the WooCommerce plugin.It is convenient for merchants to collect money quickly. OPay also provide professional customer services. If you encounter problems during the payment process, OPay will offer an expert team to deal with the relevant problems.


<strong>Benefits for different payments:</strong>

Convenient Bank Card payment

Just provide the cardholder's name, card number, CCV and expiration date to easily realize bank card payment without other operations and seamlessly connect the needs of merchants and consumers.

Reference code payment of online and offline linkage

The customer could find the nearest offline verification store through the received reference code and map guidance, make verification and payment through the offline POS machine. Choose your most convenient way, cash or coins. As long as there is store support, customer could easily complete the payment.

Flexible Shahry installment

Through the cooperation with Shahry installment service, it can bring customer an elastic payment experience and a variety of consumption installment payment methods to make the payment more flexible.

Mobile wallets with QR code

OPay offer easy access and fast payment methods make it easier for customers to pay. Only need to collect customer information and quickly complete the payment through scan QR code. It reduces the complex payment process and quickly meets the needs of merchants and customers.

ValU installment payment

OPay provide consumers with convenient payment methods, and convenient installment payment allows customer to reduce the payment burden,empower with the financial solution you need that is within your capacity. The fast approval process makes the payment process smoother.

Bank installment payment

OPay provide consumers with convenient Bank Installment payment methods.It is an agreement where the payer authorizes the payment for a single purchase to be split into a number of payments processed at agreed intervals. For example, pay for a purchase in six monthly installments.


OPay Now (Buy now Pay latter)

OPay Now (Buy now Pay later) payment method allows customer to make advance payment with their own authorized amount and then make repayment. It can not only solve the problem of urgent money for customer, but also achieve payment quickly with a simple payment process.

== Frequently Asked Questions ==

How to works：
1.Bank Card：Insert Cardholder's name, Card number, CCV and expiration date to complete payment
2.Reference Code：Receive reference code through email/SMS and use reference code to write off POS in nearby stores
3.Shahry installment：Complete the payment through Shahry ID and token
4.ValU：Fill in the customer information, select the installment payment method
5.Mobile Wallets: Add the customer information, receive and scan QR code
6.Bank installment: Add payment products, select installment terms, jump and add bank card information to easily complete installment payment.
7.OPay Now：Register as an OPay app user and use OPay app for QR code scanning payment during payment.
== Installation ==

Download and Installation:
1.Download the plugin found on the top right corner of page
2.Login to your Wordpress Dashboard
3.Go to Plugins =>Add New
4.Click on Upload Plugin button found on the top left corner of page
5.Click on Browse, select the .zip file of your plugin from your computer, and click ‘Install Now’ button
6.Click on Activate Plugin link
7.Find OPay entry and click activate

Set Up and Configuration:
1.Go to WooCommerce section=>Select Settings subsection
2.Click on the payment tab, find the new payment methods
3.Click on ‘Manage/Setup’ button
The merchant ID and secret key can be obtained through the merchant dashboard
Account setting=>account details=>Business(Merchant ID) and API keys & Web hook
4.Within the “Manage”page, please fill the fields as described (please see the link detail below)
5.Click on save changes buttons
6.Enable the OPay payment methods =>save changes

Verify your installation:
1.Create a test order on your site and proceed to checkout page
2.In your checkout page, OPay payment methods should be readily available to your customer
3.Click on Place Order. OPay Cashier page will appear to your customer to complete the payments
4.Once your customer complete the payments, OPay Cashier will redirect your customer to the order received page

Order Management:
1.Go to Woocommerce section
2.Select Orders subsection
3.On the orders page, you will find a list of your orders.

<strong>If you need more installation details，please click <a href="https://doc.opaycheckout.com/woocommerce-plugin" target="_blank">here</a></strong>.



== Screenshots ==

1.screenshot-1.png
2.screenshot-2.png

== Changelog ==

= 2.5.5 =
1.One payment methods are added: OPayNOW.

= 2.5.4 =
1.Update reference code icon.

= 2.5.3 =
1.One payment methods are added: Bank Installment.

= 2.5.2 =
1.Two payment methods are added: valu and mobile wallets.
2.Merchant Dashboard background order numbering rule optimization.

= 2.5.1 =
1.Optimize code structure

= 2.5.0 =
1.The signature verification method has been changed.

= 2.4.4 =
1.Added auto closing function
2.Order cancellation status optimization

= 2.4.1 =
1.The payment exception jump is optimized.

= 2.4.0 =
1.The total payment amount has been optimized and the postage has been increased..

= 2.3.1 =
1.This version is the first.

* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 2.5.5 =
This version adds one payment methods: OPayNOW.Upgrade immediately.

= 2.5.4 =
This version update reference code icon.Upgrade immediately.

= 2.5.3 =
This version adds one payment methods: Bank Installment.Upgrade immediately.

= 2.5.2 =
This version adds two payment methods: valu and mobile wallet.The background order numbering rules of Merchant Dashboard are optimized.Upgrade immediately.

= 2.5.1 =
This version optimizes the code structure.Upgrade immediately.

= 2.5.0 =
This version changes the signature verification method.  Upgrade immediately.

= 2.4.4 =
This version the auto closing function and order cancellation status optimization are added.  Upgrade immediately.

= 2.4.1 =
This version optimizes the payment exception jump.  Upgrade immediately.

= 2.4.0 =
This version pays the total amount plus postage.  Upgrade immediately.

= 2.3.1 =
This version is the first.

A few notes about the sections above:

* "Contributors" is a comma separated list of wordpress.org usernames
* "Tags" is a comma separated list of tags that apply to the plugin
* "Requires at least" is the lowest version that the plugin will work on
* "Tested up to" is the highest version that you've *successfully used to test the plugin*
* Stable tag must indicate the Subversion "tag" of the latest stable version

Note that the `readme.txt` value of stable tag is the one that is the defining one for the plugin.  If the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used for displaying information about the plugin.

If you develop in trunk, you can update the trunk `readme.txt` to reflect changes in your in-development version, without having that information incorrectly disclosed about the current stable version that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

If no stable tag is provided, your users may not get the correct version of your code.



