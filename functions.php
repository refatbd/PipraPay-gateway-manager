<?php
// Hook to run when an admin page is loaded
add_action('pp_admin_initialize', 'gateway_manager_activate');
// Hook to run on the public payment page to inject our scripts and data
add_action('pp_payment_initialize', 'inject_gateway_order_data');

/**
 * Creates the database table and populates it with gateways if it doesn't exist.
 */
function gateway_manager_activate()
{
    global $db_prefix;
    $conn = connectDatabase();
    $tableName = $db_prefix . 'gateway_order';

    // Create the custom table to store the order
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `plugin_slug` VARCHAR(255) NOT NULL UNIQUE,
        `display_order` INT NOT NULL DEFAULT 0
    )";
    $conn->query($sql);
    
    // Populate the table with any gateways that are not already in it
    $all_gateways = json_decode(getData($db_prefix.'plugins', 'WHERE plugin_dir="payment-gateway"'), true);
    if ($all_gateways['status']) {
        foreach ($all_gateways['response'] as $gateway) {
            $slug = escape_string($gateway['plugin_slug']);
            $check_sql = "SELECT * FROM `$tableName` WHERE plugin_slug = '$slug'";
            $result = $conn->query($check_sql);
            if ($result->num_rows === 0) {
                $insert_sql = "INSERT INTO `$tableName` (plugin_slug, display_order) VALUES ('$slug', 0)";
                $conn->query($insert_sql);
            }
        }
    }

    $conn->close();
}

/**
 * Injects the gateway order as a JavaScript variable and enqueues the frontend script.
 */
function inject_gateway_order_data()
{
    global $db_prefix;
    $conn = connectDatabase();
    $order_table = $db_prefix . 'gateway_order';

    // Fetch the ordered list of gateway slugs
    $sql = "SELECT `plugin_slug` FROM `$order_table` ORDER BY `display_order` ASC";
    $result = $conn->query($sql);

    $ordered_slugs = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $ordered_slugs[] = $row['plugin_slug'];
        }
    }
    $conn->close();

    // Embed the ordered slugs into a JavaScript variable
    echo '<script>';
    echo 'window.gatewayOrder = ' . json_encode($ordered_slugs) . ';';
    echo '</script>';

    // Enqueue the frontend JavaScript file
    $script_url = 'https://' . $_SERVER['HTTP_HOST'] . '/pp-content/plugins/modules/gateway-manager/assets/frontend.js';
    echo '<script src="' . $script_url . '"></script>';
}