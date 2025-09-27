=== Telegram Bot Notification Pro ===
Contributors: Refat Rahman
Donate link: https://refat.ovh/donate
Tags: notification, bot notification, admin notification, telegram
Requires at least: 1.0.0
Tested up to: 1.0.0
Stable tag: 2.1.1
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

An enhanced Telegram Bot Notification plugin for PipraPay that delivers real-time transaction alerts directly to your Telegram.
This Pro version includes support for multiple chat IDs, granular notification controls, and a modern, user-friendly interface.
**Key Features:**
* **Easy Bot Setup**: Connect your Telegram bot by simply pasting the token.
* **Multiple Chat IDs**: Send notifications to multiple users or channels.
* **Granular Controls**: Enable or disable notifications for pending, completed, and failed payments.
* **Interactive Pending Confirmation**: Approve pending transactions directly from Telegram with a confirmation button.
* **Global Toggle**: Easily enable or disable the entire notification system.
* **Interactive Bot Commands**: Get real-time sales and transaction data directly from your bot.
* **User-Friendly Interface**: Modern and intuitive settings page.

== Installation ==
1.  Download the plugin.
2.  Upload the plugin folder to your PipraPay `Plugin` section.
3.  Activate the plugin from PipraPay's module settings.
4.  Go to **Admin Dashboard → Module → Telegram Bot Notification Pro**.
5.  Follow the on-screen instructions to set up your bot and chat IDs.

== Bot Commands ==
You can use the following commands in your Telegram chat with the bot:
* `/start` - Get your Chat ID.
* `/last_transaction` - Get details of the most recent transaction.
* `/sales_today` - Get the total sales amount for today.
* `/sales_yesterday` - Get the total sales amount for yesterday.
* `/sales_this_month` - Get the total sales amount for the current month.
* `/pending_transactions` - Get a count of pending transactions.
* `/failed_transactions` - Get a count of failed transactions.
* `/completed_transactions` - Get a count of completed transactions.
* `/help` - Show all the available commands.

== Changelog ==

= 2.1.0 =
* Added interactive "Confirm Transaction" button for pending notifications.
* Added a setting to enable/disable the confirmation button feature.

= 2.0.0 =
* Complete overhaul of the plugin
* Added support for multiple chat IDs
* Added granular notification controls
* Added interactive bot commands for sales and transaction data.
* Redesigned the admin interface
* Improved user experience