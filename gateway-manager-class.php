<?php
    $plugin_meta = [
    'Plugin Name'       => 'Gateway Order Manager',
    'Description'       => 'Manage the display order of payment gateways using drag and drop.',
    'Version'           => '1.2',
    'Author'            => 'Refat Rahman',
    'Author URI'        => 'https://github.com/refatbd',
    'License'           => 'GPL-2.0+',
    'License URI'       => 'http://www.gnu.org/licenses/gpl-2.0.txt',
    'Requires at least' => '1.0.0',
    'Plugin URI'        => 'https://github.com/refatbd/PipraPay-gateway-manager',
    'Text Domain'       => 'gateway-manager',
    'Domain Path'       => '',
    'Requires PHP'      => '7.4'
];

    /**
     * This function is called by the system to render the plugin's admin page.
     * It now includes the actual UI file.
     */
    function gateway_manager_admin_page()
    {
        // The core logic is now in functions.php, and the UI is in admin-ui.php
        include_once(__DIR__ . '/views/admin-ui.php');
    }
?>