=== Apollo - invoicing for WooCommerce ===
Contributors: jank404
Tags:  apollo, invoice, invoicing, generate, pdf, woocommerce, attachment, email, customer invoice, estimate
Requires at least: 4.9
Requires PHP: 5.6.3
Stable tag: trunk
Tested up to: 5.7.0

== Description ==
## DEPRECATED
We have released a new way of connecting WooCommerce with Apollo, for instructions please check the following link:
- [Instructions in EN](https://www.notion.so/studiofourzerofour/How-to-connect-WooCommerce-7526a17e612f47c0958b079a60f42644)
- [Instructions in SI](https://www.notion.so/studiofourzerofour/Kako-pove-em-WooCommerce-7e62874edde24835ab412f1ca851e9ca)

#### About Apollo
Apollo is an intuitive all-in-one online invoicing software allowing you to create, edit and send professional invoices with ease. With Apollo, it’s easier-than-ever to create professional invoices.

**Apollo features**
- Overview your business
- Track payments, partial payments, and overdue invoices
- Create documents of varying types (i.e. invoices, estimates, etc)
- Easily duplicate or convert documents from one type to another
- Manage invoiceable items and services
- Manage your clients with the status overview and robust contact database
- Manage users by assigning accounts with read and/or write permission to your data
- Manage multiple organizations with one user account


You can get more information about Apollo at [https://getapollo.io](https://getapollo.io).

#### About Apollo WordPress extension

This extension offers some of the features from Apollo, such as creating estimates and invoices or the generation of PDF documents for orders. You can easily create invoices in WooCommerce, and track all the invoicing data on [Apollo webpage](https://getapollo.io).

= Main features =
- Automatic (or manual) invoice creation for individual orders
- Automatic creation of estimate documents
- Create, view, and download PDF documents for each invoice or estimate
- Send an email with a PDF attachment of invoices or estimates (can either be automatically generated or sent with an order status update.)
- Tracking your invoices and estimates at [https://getapollo.io](https://getapollo.io)

#### PDF customization

You can customize your PDF document at the Apollo webpage, where you can set a logo for your company, choose a primary theme color, and set all of the PDF texts.
To do so, simply go to the [Apollo website](https://getapollo.io), find the "Account" icon 
x
at the bottom of the sidebar and click on "Customizations".

### How To Use
For the extension to work, you will need to provide the Apollo token and the Apollo organization ID. You can find those on our [Apollo page](https://getapollo.io), under extensions tab.

Apollo is proudly powered by the [Space invoices API](https://spaceinvoices.com/). For further information on extension implementation, please feel free to check out our [Space invoices API PHP documentation](https://docs.spaceinvoices.com/?php) for developers.

#### Support

If you have any questions or problems with plugin, you can contact us at <support@getapollo.io>.

== Screenshots ==

1. Apollo settings 1/2
2. Apollo settings 2/2
3. Create new invoice/estimate on WooCommerce order page
4. View invoice on Apollo page view inovice PDF
5. Example invoice on Apollo
6. Example invoice PDF
7. New order flow
8. Manual document sending flow

== Installation ==

First thing you need for Apollo extension to work, is to sign up on [Apollo sign up page](https://getapollo.io/app/signup). After you confirmed your email, you can find data you need at extensions tab.

**Before installation make sure you have WooCommerce version 3.0 or higher already installed on your WordPress page.**

#### Automatic installation
To do an automatic install of Apollo plugin, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

Search for "Apollo – invoicing for WooCommerce", once you find it you can simply click "Install Now" button, wait for plugin to install and after that click "Activate" button and you can start using our plugin.

#### Manual installation
The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application.

To install plugin manually you need to:

1. Download the plugin file and unzip it
2. Upload the unzipped folder to your WordPress plugin folder (wp-content/plugins/).
3. Go to your WordPress admin panel, under "Installed Plugins" you will find Apollo plugin. Click "Activate" and plugin is ready to use.

Make sure you go to extension settings and set "token" and "organization id" in order for extension to work. You can find your data on [Apollo](https://getapollo.io), under Integrations > WooCommerce.

== Changelog ==
= 1.1.19 - March 10, 2021 =

- IMPORTANT: THIS PLUGIN IS NO LONGER SUPPORTED, please follow [these instructions](https://www.notion.so/studiofourzerofour/How-to-connect-WooCommerce-7526a17e612f47c0958b079a60f42644) to configure new version.

= 1.1.18 - February 9, 2021 =

- Fixed: Bug fixes

= 1.1.17 - February 7, 2021 =

- Fixed: Online payment double invoice bug

= 1.1.16 - January 8, 2021 =

- Added: Role shop_manager permissions to plugin

= 1.1.15 - January 8, 2021 =

- Added: Support for subscription renewal orders

= 1.1.14 - January 2, 2021 =

- Fixed: PDF file path bug

= 1.1.13 - November 9, 2020 =

- Fixed: Shipping tax rate calculation

= 1.1.12 - November 7, 2020 =

- Fixed: Cupon price calculating
- Added: Organization units setting

= 1.1.11 - October 10, 2020 =

- Fixed: Minor price calculation fixes

= 1.1.10 - September 13, 2020 =

- Fixed: Variable product data fetching fix

= 1.1.9 - August 11, 2020 =

- Added: Document language can now be set in Apollo settings

= 1.1.8 - July 27, 2020 =

- Updated: Get item taxe rate instead of calculating it
- Fixed: Document path to Apollo

= 1.1.7 - June 03, 2020 =

- Updated: Added option for COD payments, invoice can now be created when order is completed (previously it was created with new order)

= 1.1.6 - May 28, 2020 =

- Fixed: Estimate sending to user when new order is craeted
- Updated: Some payment types now match Apollo payment types
- Updated: Error logging

= 1.1.5 - April 4, 2020 =

- Fixed: Sending documents on fail order
- Updated: Invoices are now created when order is completed, only exceptin is Cash On Delivery payment, where invoice is created with new order (invoice is not marked as paid in this case)

= 1.1.4 - February 24, 2020 =

- Fixed: Access token was not set when creating documents

= 1.1.3 - February 22, 2020 =

- Added: Option to add SKU in item description on invoice
- Updated: Invoices with payment option COD (Cash on delivery) are no longer marked as paid automatically

= 1.1.2 - June 7, 2019 =

- Added: PDF language now mataches store language
- Fixed: Invoices were generating even if payment failed, now inovice is created when order is completed

= 1.1.1 - May 24, 2019 =

Documents are now always generated automatically for chosen payment methods, sending is optional.

- Added: Category alias
- Added: Order notes when creating document
- Added: Extra settings for "Direct bank transfer" payment method
- Changed: Settings names and descriptions
- Code improvments

= 1.1.0 - April 23, 2019 =

Please check Apollo settings page after updating!

- Added: Invoice fiscalization
- Updated: Automatic invoicing system. Invoices now generate based on order payments (previously plugin was using order status to determinate when to create invoice)
- Updated: Apollo settings
- Updated: Now works on WooCommerce 3.6.1
- Updated: Languages

= 1.0.3 - April 15, 2019 =

- Minor bug fixes

= 1.0.2 - March 21, 2019 =

- Fixed: JS and CSS script adding error
- Added: Extra error handling
- Show some extra details when error occurs
- Minor bug fixes

= 1.0.1 - January 28, 2019 =

- Added: SKU codes (In preparation for Apollo inventory tracking with SKU codes in next patch)

= 1.0.0 - January 16, 2019 =

- Initial release.
