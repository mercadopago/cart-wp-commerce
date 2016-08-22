=== WPeComm Mercado Pago Module Oficial ===
Contributors: mercadopago, mercadolivre
Donate link: https://www.mercadopago.com.br/developers/
Tags: ecommerce, mercadopago, wpecommerce
Requires at least: WP-eCommerce 3.11.x
Tested up to: WP-eCommerce 3.11.x
Stable tag: 4.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is the oficial module of Mercado Pago for WP-eCommerce plugin.

== Description ==

This module enables WP-eCommerce to use Mercado Pago as a payment Gateway for purchases made in your e-commerce store.

= Why chose Mercado Pago =
Mercado Pago owns the highest security standards with PCI certification level 1 and a specialized internal team working on fraud analysis. With Mercado Pago, you will be able to accept payments from the most common brands of credit card, offer purchase installments options and receive your payment with antecipation. You can also enable your customers to pay in the web or in their mobile devices.

= Mercado Pago Main Features =
* Online and real-time processment through IPN mechanism;
* High approval rate with a robust fraud analysis;
* Potential new customers with a base of more than 120 millions of users in Latin America;
* PCI Level 1 Certification;
* Support to major credit card brands;
* Payment installments;
* Anticipation of receivables in D+2 or D+14 (According to Mercado Pago terms and conditions);
* Payment in one click with Mercado Pago basic and custom checkouts;
* Payment via tickets;
* Seller's Protection Program.

== Installation ==

You have two ways to install this module: from your WordPress Store, or by downloading and manually copying the module directory.

= Install from WordPress =
1. On your store administration, go to **Plugins** option in sidebar;
2. Click in **Add New** button and type "WPeComm Mercado Pago Module" in the **Search Plugins** text field. Press Enter;
3. You should find the module read to be installed. Click install.

= Manual Download =
1. Get the module sources from a repository (<a href="https://github.com/mercadopago/cart-wp-commerce/archive/master.zip">Github</a> or <a href="https://downloads.wordpress.org/plugin/wpecomm-mercado-pago-module.4.1.0.zip">WordPress Plugin Directory</a>);
2. Unzip the folder and find "wpecomm-mercado-pago-module" directory;
3. Copy "wpecomm-mercado-pago-module" directory to **[WordPressRootDirectory]/wp-content/plugins/** directory.

To confirm that your module is really installed, you can click in **Plugins** item in the store administration menu, and check your just installed module. Just click **enable** to activate it and you should receive the message "Plugin enabled." as a notice in your WordPress.

= Configuration =
1. On your store administration, go to **Settings > Store > Payments** tab. You can find configurations for **Mercado Pago - Basic Checkout**, **Mercado Pago - Custom Checkout**, and **Mercado Pago - Ticket**.
	* To get your **Client_id** and **Client_secret** for your country, you can go to:
		* Argentina: https://www.mercadopago.com/mla/account/credentials?type=basic
		* Brazil: https://www.mercadopago.com/mlb/account/credentials?type=basic
		* Chile: https://www.mercadopago.com/mlc/account/credentials?type=basic
		* Colombia: https://www.mercadopago.com/mco/account/credentials?type=basic
		* Mexico: https://www.mercadopago.com/mlm/account/credentials?type=basic
		* Peru: https://www.mercadopago.com/mpe/account/credentials?type=basic
		* Venezuela: https://www.mercadopago.com/mlv/account/credentials?type=basic
	* And to get your **Public Key**/**Access Token** you can go to:
		* Argentina: https://www.mercadopago.com/mla/account/credentials?type=custom
		* Brazil: https://www.mercadopago.com/mlb/account/credentials?type=custom
		* Chile: https://www.mercadopago.com/mlc/account/credentials?type=custom
		* Colombia: https://www.mercadopago.com/mco/account/credentials?type=custom
		* Mexico: https://www.mercadopago.com/mlm/account/credentials?type=custom
		* Peru: https://www.mercadopago.com/mpe/account/credentials?type=custom
		* Venezuela: https://www.mercadopago.com/mlv/account/credentials?type=custom
2. For the solutions **Mercado Pago - Basic Checkout**, **Mercado Pago - Custom Checkout**, and **Mercado Pago - Ticket**, you can:
	* Enable/Disable you plugin (for all solutions);
	* Set the title of the payment option that will be shown to your customers (for all solutions);
	* Set up your credentials (Client_id/Client_secret for Basic, Public Key/Access Token for Custom and Ticket);
	* Set the description of the payment option that will be shown to your customers (for all solutions);
	* Set the category of your store (for all solutions);
	* Set the description that will be shown in your customer's invoice (for Custom and Ticket);
	* Set URL for approved/pending callbacks;
	* Enable coupon of campaigns for discounts (for Custom and Ticket);
	* Set binary mode that when charging a credit card, only [approved] or [reject] status will be taken (only for Custom);	
	* Set a prefix to identify your store, when you have multiple stores for only one Mercado Pago account (for all solutions);
	* Define how your customers will interact with Mercado Pago to pay their orders (only for Basic);
	* Configure the after-pay return behavior (only for Basic);
	* Configure the maximum installments allowed for your customers (only for Basic);
	* Configure the payment methods that you want to not work with Mercado Pago (only for Basic);
	* Enable currency conversion (for all solutions);
	* Enable/disable sandbox mode, where you can test your payments in Mercado Pago sandbox environment (for all solutions);
	* Enables/disable system logs (for all solutions).

= In this video, we show how you can install and configure from your WordPress store =

yet to be uploaded...

== Frequently Asked Questions ==

= What is Mercado Pago? =
Please, take a look: https://vimeo.com/125253122

= Any questions? =
Please, check our FAQ at: https://www.mercadopago.com.br/ajuda/

== Screenshots ==

1. `Configuration of Basic Checkout`

== Changelog ==

= v4.1.0 (22/08/2016) =
* Improvements
	- Wrapped the module as an WordPress Plugin Directory.
	
= v4.0.0 (20/08/2016) =
* Features
	- LatAm Basic Checkout support. Great for merchants who want to get going quickly and easily. This is the basic payment integration with Mercado Pago. Want to see how it works on-the-fly? Please check this video: <a href="https://www.youtube.com/watch?v=DgOsX1eXjBU">Standard Checkout</a>
* Improvements
	- Improved set of configurable fields and customizations. Title, description, category, and external reference customizations, integrations via iframe, modal, and redirection, with configurable auto-returning, max installments and payment method exclusion setup;
	- Checkout flow with a more complete integration;
	- Metafiles standardization (readme, changelo, etc).

== Upgrade Notice ==

If you're migrating to version 4.x.x, please be sure to make a backup of your site and database, as there are many additional features and modifications between these versions.
