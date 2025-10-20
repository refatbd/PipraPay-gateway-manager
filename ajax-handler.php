<?php
// Include the main configuration and controller files to access database functions
if (file_exists(__DIR__ . '/../../../../pp-config.php')) {
    require_once __DIR__ . '/../../../../pp-config.php';
} else {
    die('Config file not found.');
}

if (file_exists(__DIR__ . '/../../../../pp-include/pp-controller.php')) {
    require_once __DIR__ . '/../../../../pp-include/pp-controller.php';
} else {
    die('Controller file not found.');
}

if (file_exists(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}


// Set header to output JSON
header('Content-Type: application/json');

// --- Admin-only Actions ---

// Security Check: Only proceed for saving if an admin is logged in.
if (!isset($_COOKIE['pp_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'save_gateway_order') {
    if (!isset($_POST['order'])) {
        echo json_encode(['status' => 'error', 'message' => 'No order data received.']);
        exit;
    }

    $order = json_decode($_POST['order'], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data received.']);
        exit;
    }

    global $db_prefix;
    $tableName = $db_prefix . 'gateway_order';
    $success = true;

    foreach ($order as $item) {
        if (!isset($item['id']) || !isset($item['order'])) {
            $success = false;
            break;
        }
        $result = updateData($tableName, ['display_order'], [$item['order']], "plugin_slug = '" . escape_string($item['id']) . "'");
        if (!$result) {
            $success = false;
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Order saved successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'A database error occurred while saving the order.']);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'check_for_updates') {
    $update_info = gateway_manager_check_for_github_updates();
    if ($update_info) {
        echo json_encode([
            'status' => true, 
            'update_available' => true, 
            'data' => $update_info
        ]);
    } else {
        echo json_encode([
            'status' => true, 
            'update_available' => false, 
            'message' => 'You are using the latest version of the plugin.'
        ]);
    }
    exit;
}

// Fallback for any other requests
echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);