<?php

// Standard PipraPay loader to ensure the environment is ready
if (file_exists(__DIR__."/../../../../pp-config.php")) {
    if (file_exists(__DIR__.'/../../../../maintenance.lock')) {
        if (file_exists(__DIR__.'/../../../../pp-include/pp-maintenance.php')) {
           include(__DIR__."/../../../../pp-include/pp-maintenance.php");
        } else {
            die('System is under maintenance. Please try again later.');
        }
        exit();
    } else {
        if (file_exists(__DIR__.'/../../../../pp-include/pp-controller.php')) { include(__DIR__."/../../../../pp-include/pp-controller.php"); } 
        else { exit(); }
        
        if (file_exists(__DIR__.'/../../../../pp-include/pp-model.php')) { include(__DIR__."/../../../../pp-include/pp-model.php"); } 
        else { exit(); }

        if (file_exists(__DIR__.'/../../../../pp-include/pp-view.php')) { include(__DIR__."/../../../../pp-include/pp-view.php"); } 
        else { exit(); }
    }
} else {
    exit();
}

if (!defined('pp_allowed_access')) {
    die('Direct access not allowed');
}

// Check for the correct GET parameter from the webhook URL
if (isset($_GET['telegram-bot-notification-pro'])) {

    if (!function_exists('pp_get_plugin_setting')) {
        exit();
    }

    $plugin_slug = 'telegram-bot-notification-pro';
    $settings = pp_get_plugin_setting($plugin_slug);
    
    $update = json_decode(file_get_contents('php://input'), true);

    if (!$update) {
        exit();
    }
    
    $bot_token = $settings['bot_token'] ?? '';
    if (empty($bot_token)) {
        exit();
    }

    // --- Callback Query Handler (for button clicks) ---
    if (isset($update['callback_query'])) {
        $callback_query = $update['callback_query'];
        $chat_id = $callback_query['message']['chat']['id'];
        $message_id = $callback_query['message']['message_id'];
        $callback_data = $callback_query['data'];

        // Function to check if a chat ID is authorized
        function is_chat_id_authorized($chat_id, $settings) {
            if (empty($settings['chat_ids_json'])) return false;
            $chat_ids = json_decode($settings['chat_ids_json'], true);
            if (empty($chat_ids) || !is_array($chat_ids)) return false;
            foreach ($chat_ids as $chat) {
                if (($chat['enabled'] ?? 'false') === 'true' && $chat['id'] == $chat_id) {
                    return true;
                }
            }
            return false;
        }

        if (!is_chat_id_authorized($chat_id, $settings)) {
             @file_get_contents("https://api.telegram.org/bot{$bot_token}/answerCallbackQuery?callback_query_id={$callback_query['id']}&text=🚫 Authorization Failed");
            exit();
        }

        // First confirmation step
        if (strpos($callback_data, 'confirm_pending_') === 0) {
            $transaction_id = str_replace('confirm_pending_', '', $callback_data);
            
            $reply_markup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅ Yes, Confirm It!', 'callback_data' => 'confirm_final_' . $transaction_id],
                        ['text' => '❌ No, Cancel', 'callback_data' => 'confirm_cancel_' . $transaction_id]
                    ]
                ]
            ];

            $params = [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => $callback_query['message']['text'] . "\n\n*Are you sure you want to mark this transaction as completed?*",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode($reply_markup)
            ];
            @file_get_contents("https://api.telegram.org/bot{$bot_token}/editMessageText?" . http_build_query($params));
        }

        // Final confirmation step
        elseif (strpos($callback_data, 'confirm_final_') === 0) {
            $transaction_id = str_replace('confirm_final_', '', $callback_data);
            
            // Use PipraPay's internal function to update the status
            if (pp_set_transaction_status($transaction_id, 'completed')) {
                $new_text = preg_replace("/\n\n\*Are you sure.*\*/", "", $callback_query['message']['text']);
                $new_text = str_replace("⚪️ *New Transaction: Pending*", "✅ *Transaction Confirmed: Completed*", $new_text);
                
                @file_get_contents("https://api.telegram.org/bot{$bot_token}/answerCallbackQuery?callback_query_id={$callback_query['id']}&text=Success!");
            } else {
                $new_text = $callback_query['message']['text'] . "\n\n*❌ Failed to confirm transaction. Please check the admin panel.*";
                @file_get_contents("https://api.telegram.org/bot{$bot_token}/answerCallbackQuery?callback_query_id={$callback_query['id']}&text=Failed!");
            }
            
            // Edit the message to show it's confirmed and remove the buttons
            $params = ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $new_text, 'parse_mode' => 'Markdown'];
            @file_get_contents("https://api.telegram.org/bot{$bot_token}/editMessageText?" . http_build_query($params));
        }

        // Cancel action
        elseif (strpos($callback_data, 'confirm_cancel_') === 0) {
            $transaction_id = str_replace('confirm_cancel_', '', $callback_data);
            $original_text = preg_replace("/\n\n\*Are you sure.*\*/", "", $callback_query['message']['text']);
            
            $reply_markup = ['inline_keyboard' => [[['text' => 'Confirm Pending Transaction', 'callback_data' => 'confirm_pending_' . $transaction_id]]]];

            $params = [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => $original_text,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode($reply_markup)
            ];
            @file_get_contents("https://api.telegram.org/bot{$bot_token}/editMessageText?" . http_build_query($params));
            @file_get_contents("https://api.telegram.org/bot{$bot_token}/answerCallbackQuery?callback_query_id={$callback_query['id']}");
        }
        
        exit();
    }


    // --- Regular Message Handler (for commands) ---
    $message_text = $update['message']['text'] ?? '';
    $chat_id = $update['message']['chat']['id'] ?? null;

    if (!$chat_id) {
        exit();
    }
    
    $reply = '';

    if(!function_exists('is_chat_id_authorized')){
        function is_chat_id_authorized($chat_id, $settings) {
            if (empty($settings['chat_ids_json'])) return false;
            $chat_ids = json_decode($settings['chat_ids_json'], true);
            if (empty($chat_ids) || !is_array($chat_ids)) return false;
            foreach ($chat_ids as $chat) {
                if (($chat['enabled'] ?? 'false') === 'true' && $chat['id'] == $chat_id) {
                    return true;
                }
            }
            return false;
        }
    }

    $is_authorized = is_chat_id_authorized($chat_id, $settings);
    $restricted_commands = [
        '/last_transaction', '/sales_today', '/sales_yesterday', '/sales_this_month',
        '/pending_transactions', '/failed_transactions', '/completed_transactions', '/help',
    ];

    if ($message_text === "/start") {
        $reply = "👋 Here's your Chat ID: `{$chat_id}`\n\nCopy this and paste it into the Chat ID section in your PipraPay settings.";
    } elseif (in_array($message_text, $restricted_commands) && !$is_authorized) {
        $reply = "🚫 Chat id is not authorized. Add chat id to your admin panel first";
    } elseif ($is_authorized) {
        global $conn, $db_host, $db_user, $db_pass, $db_name, $db_prefix;

        if (!isset($conn)) {
            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
            if ($conn->connect_error) { exit('Database connection failed.'); }
        }
        
        try {
            switch ($message_text) {
                case '/last_transaction':
                    $sql = "SELECT * FROM {$db_prefix}transaction ORDER BY id DESC LIMIT 1";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $reply = "*Last Transaction Details:*\n\n" .
                                 "💰 *Amount:* `{$row['transaction_amount']} {$row['transaction_currency']}`\n" .
                                 "👤 *From:* `{$row['c_name']}`\n" .
                                 "💳 *Method:* `{$row['payment_method']}`\n" .
                                 "📊 *Status:* `{$row['transaction_status']}`\n" .
                                 "🗓️ *Date:* `{$row['created_at']}`";
                    } else { $reply = "No transactions found."; }
                    break;

                case '/sales_today':
                    $today_start = date('Y-m-d 00:00:00'); $today_end = date('Y-m-d 23:59:59');
                    $sql = "SELECT COALESCE(SUM(transaction_amount), 0) as total_sales FROM {$db_prefix}transaction WHERE created_at >= '{$today_start}' AND created_at <= '{$today_end}' AND transaction_status = 'completed'";
                    $result = $conn->query($sql);
                    $total_sales = $result->fetch_assoc()['total_sales'];
                    $reply = "📈 *Today's Sales:* `" . number_format($total_sales, 2) . "`";
                    break;

                case '/sales_yesterday':
                    $yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 day')); $yesterday_end = date('Y-m-d 23:59:59', strtotime('-1 day'));
                    $sql = "SELECT COALESCE(SUM(transaction_amount), 0) as total_sales FROM {$db_prefix}transaction WHERE created_at >= '{$yesterday_start}' AND created_at <= '{$yesterday_end}' AND transaction_status = 'completed'";
                    $result = $conn->query($sql);
                    $total_sales = $result->fetch_assoc()['total_sales'];
                    $reply = "📈 *Yesterday's Sales:* `" . number_format($total_sales, 2) . "`";
                    break;

                case '/sales_this_month':
                    $month_start = date('Y-m-01 00:00:00'); $month_end = date('Y-m-t 23:59:59');
                    $sql = "SELECT COALESCE(SUM(transaction_amount), 0) as total_sales FROM {$db_prefix}transaction WHERE created_at >= '{$month_start}' AND created_at <= '{$month_end}' AND transaction_status = 'completed'";
                    $result = $conn->query($sql);
                    $total_sales = $result->fetch_assoc()['total_sales'];
                    $reply = "📅 *This Month's Sales:* `" . number_format($total_sales, 2) . "`";
                    break;
                
                case '/pending_transactions':
                    $sql = "SELECT COUNT(*) as count FROM {$db_prefix}transaction WHERE transaction_status = 'pending'";
                    $result = $conn->query($sql);
                    $count = $result->fetch_assoc()['count'];
                    $reply = "⚪️ *Pending Transactions:* `{$count}`";
                    break;
                
                case '/failed_transactions':
                    $sql = "SELECT COUNT(*) as count FROM {$db_prefix}transaction WHERE transaction_status = 'failed'";
                    $result = $conn->query($sql);
                    $count = $result->fetch_assoc()['count'];
                    $reply = "❌ *Failed Transactions:* `{$count}`";
                    break;

                case '/completed_transactions':
                    $sql = "SELECT COUNT(*) as count FROM {$db_prefix}transaction WHERE transaction_status = 'completed'";
                    $result = $conn->query($sql);
                    $count = $result->fetch_assoc()['count'];
                    $reply = "✅ *Completed Transactions:* `{$count}`";
                    break;

                case '/help':
                    $reply = "🤖 *Available Commands:*\n\n" .
                             "`/start` - Get your Chat ID.\n" .
                             "`/last_transaction` - Details of the most recent transaction.\n" .
                             "`/sales_today` - Total sales for today.\n" .
                             "`/sales_yesterday` - Total sales for yesterday.\n" .
                             "`/sales_this_month` - Total sales for the current month.\n" .
                             "`/pending_transactions` - Count of pending transactions.\n" .
                             "`/failed_transactions` - Count of failed transactions.\n" .
                             "`/completed_transactions` - Count of completed transactions.\n" .
                             "`/help` - Show all available commands.";
                    break;

                default:
                    $reply = "🤔 Invalid command. To get your chat ID, type `/start` or type `/help` to see all available commands.";
                    break;
            }
        } catch (Exception $e) {
            $reply = "An error occurred while processing your command. Please try again later.";
        } finally {
            if (isset($conn)) { $conn->close(); }
        }
    } else {
        $reply = "🤔 Invalid command. To get your chat ID, type `/start`.";
    }
    
    if(!empty($reply)) {
        $params = ['chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'Markdown'];
        $url = "https://api.telegram.org/bot{$bot_token}/sendMessage?" . http_build_query($params);
        @file_get_contents($url);
    }
}
?>