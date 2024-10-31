=== Pacific Payment Gateway ===
Contributors: InterSynergy
Donate link:
Tags: payment, pacific, payment gateway, blik, card, inpost, paczkomaty, inpost paczkomaty
Requires at least: 5.3
Tested up to: 6.0.1
Requires PHP: 7.1
Stable tag: 1.0.23
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pacific payment gateway plugin for Woocommerce.

== Description ==

The plugin offers access to payments via the pacific.org gateway. One account – many stores! When you use Pacific, you have access to many stores. You don’t have to create separate accounts in each of the stores. You just browse and buy immediately after entering email address.
https://checkout.pacific.org/

== Screenshots ==

1. [Step one] Product page
2. [First purchase] First purchase registration form
3. [First purchase] Set Pin Code
4. [First purchase] Verify account via SMS
5. [First purchase] Default shipping address
6. [Step three] Order modal
7. [Step four] Success

== Frequently Asked Questions ==

= Inpost paczkomaty =

After activating the plugin, you need to go to woocommerce -> settings -> shipping-> country and add a new delivery method of type "paczkomaty (pacific)".

= Settings =

From the menu on the left, select the "Pacific Gateway" tab, you must fill in all the fields. You will get the access key through Pacific applications
Plugin requires PHP Intl module

= Shipping methods =

Remember to correctly assign the delivery methods from Pacific to the delivery methods in WooCommerce in the plugin settings.

== Changelog ==

= 1.0.23 =
* Change ok button to text
* Add Css for modal scrollbars

= 1.0.22 =
* Upload languages

= 1.0.21 =
* Hide deactivated shipping method in admin dashboard
* Hide deactivated shipping method in client modal
* Display only last typed PIN value as type text

= 1.0.20 =
* Handle virtual keyboard on mobile devices
* Add on load event listener to window

= 1.0.19 =
* Change Pacific posID

= 1.0.18 =
* ADD trim method for form inputs
* Add CSS for various templates in WordPress
* Fix click on address box after adding new shipment address
* Prevent paste text into pin input

= 1.0.17 =
* Display message about missing address for product final price
* Add CSS for various templates in WordPress
* Fix endpoint for checking if email exists for email with plus in address

= 1.0.16 =
* Show plugin version in data attribute in widget
* Add CSS for various templates in WordPress.

= 1.0.15 =
* Improvement of error with shipping rate

= 1.0.14 =
* Bug fix with missing delivery amount in order confirmation email
* Bug fix with changing input focus in login modal on mobile
* Bug fix an error in calculating the final price for locker shipment method
* Add CSS for various templates in WordPress

= 1.0.13 =
* Remove query add-to-cart from url to get product data

= 1.0.12 =
* Change main widget button to anchor tag in plugin_box.php
* Add e.preventDefault and e.stopPropagation to prevent adding product to shop cart

= 1.0.11 =
* Update modal in english

= 1.0.10 =
* Add CSS for various templates in WordPress
* Disable clicking on address box when paying for an order

= 1.0.9 =
* Add CSS for various templates in WordPress
* Move modal wrapper direct to body tag

= 1.0.8 =
* Handle server errors and display relevant messages
* Handle display product without any photo

= 1.0.7 =
* CSS fixes if parcel map for various templates in WordPress
* Add spinner for fetching payments methods

= 1.0.6 =
* Add CSS for parcel map
* Add global context for adding new address and new payment cards

= 1.0.5 =
* Add CSS for various templates in WordPress
* Handle error codes for BLIK payment

= 1.0.4 =
* Deploy plugin
* Add reset CSS file

= 1.0.3 =
* Improvement of pin validation problem when initial character is 0
* Display product price with or without tax - depending on woocommerce settings
* Load InPost geowidget
* Remove birthDate field from user

= 1.0.2 =
* Fixed problem with wrongly calculated tax for shipping
* Improved tax calculation when tax handling is disabled
* Fixing an error when placing an order if the shipping method is courier

= 1.0.1 =
* Fix escaping data in templates
* Fix readme
* Replace Guzzle to Symfony HttpClient

= 1.0.0 =
* Hello Pacific!

== Upgrade Notice ==

= 1.0.3 =
API Update
