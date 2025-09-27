<?php
    if (!defined('pp_allowed_access')) {
        die('Direct access not allowed');
    }

$plugin_meta = [
    'Plugin Name'       => 'Telegram Bot Notification Pro',
    'Description'       => 'An enhanced Telegram Bot Notification plugin for PipraPay with support for multiple chat IDs and advanced notification controls.',
    'Version'           => '2.1.1',
    'Author'            => 'Refat Rahman',
    'Author URI'        => 'https://github.com/refatbd',
    'License'           => 'GPL-2.0+',
    'License URI'       => 'http://www.gnu.org/licenses/gpl-2.0.txt',
    'Requires at least' => '1.0.0',
    'Plugin URI'        => 'https://github.com/refatbd/PipraPay-bot-notification-pro',
    'Text Domain'       => 'telegram-bot-notification-pro',
    'Domain Path'       => '',
    'Requires PHP'      => '7.4'
];

$funcFile = __DIR__ . '/functions.php';
if (file_exists($funcFile)) {
    require_once $funcFile;
}

// Load the admin UI rendering function
function telegram_bot_notification_pro_admin_page() {
    $viewFile = __DIR__ . '/views/admin-ui.php';

    if (file_exists($viewFile)) {
        include $viewFile;
    } else {
        echo "<div class='alert alert-warning'>Admin UI not found.</div>";
    }
}