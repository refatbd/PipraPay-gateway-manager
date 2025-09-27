=== Gateway Order Manager ===
Contributors: Refat Rahman
Donate link: https://refat.ovh/donate
Tags: gateway, payment gateway, order, checkout, drag and drop
Requires at least: 1.0.0
Tested up to: 1.0.0
Stable tag: 1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

A simple yet powerful plugin for PipraPay that allows administrators to manage the display order of payment gateways on the checkout page using an intuitive drag-and-drop interface. The custom order is seamlessly reflected on the public-facing payment page, giving you full control over the user's payment experience.


**Key Features:**

**Drag & Drop Interface**: Easily reorder payment gateways by dragging and dropping them in the desired sequence.

**AJAX Powered**: The order is saved instantly in the background without needing a page refresh.

**Automatic Frontend Sorting**: The plugin automatically applies the saved order to the payment gateways list on the public checkout page.

**Seamless Integration**: Integrates directly into the PipraPay admin dashboard.

**Lightweight**: Minimal code and no unnecessary features to slow down your site.

**Easy to Use**: No complex configuration is required. Just activate, and you're ready to organize your gateways.

== Installation ==

Download the plugin.

Upload the plugin folder to your PipraPay Plugin section (/pp-content/plugins/modules/).

Activate the plugin from PipraPay's module settings.

Go to Admin Dashboard → Module → Gateway Order Manager.

Drag and drop the gateways into your preferred order and click "Save Order".

== Changelog ==

= 1.3 =
* Replaced frontend AJAX call with a server-side data injection.
* Implemented `MutationObserver` for instant, flicker-free reordering of gateways.


= 1.0 =

*Initial release of the plugin.

*Added a drag-and-drop interface for sorting active payment gateways.

*Implemented AJAX saving for the gateway order.

*Added frontend JavaScript to reorder gateways for public users.