<?php
    if (!defined('pp_allowed_access')) {
        die('Direct access not allowed');
    }

    $plugin_slug = 'telegram-bot-notification-pro';
    $settings = pp_get_plugin_setting($plugin_slug);
    $bot_token = $settings['bot_token'] ?? '';
    $bot_username = $settings['bot_username'] ?? '';
    $webhook_status = $settings['webhook_status'] ?? 'Not set. Connect bot to set webhook.';
    $chat_ids = isset($settings['chat_ids_json']) ? json_decode($settings['chat_ids_json'], true) : [];
    if (!is_array($chat_ids)) $chat_ids = [];

    // Define default templates
    $default_templates = [
        'completed' => "✅ *New Transaction: Completed*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *Transaction ID:* `{gateway_trx_id}`",
        'pending' => "⚪️ *New Transaction: Pending*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *Transaction ID:* `{gateway_trx_id}`",
        'failed' => "❌ *New Transaction: Failed*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *Transaction ID:* `{gateway_trx_id}`"
    ];

    // If a template setting is empty, use the default.
    $templates = [
        'completed' => !empty($settings['template_completed']) ? $settings['template_completed'] : $default_templates['completed'],
        'pending'   => !empty($settings['template_pending']) ? $settings['template_pending'] : $default_templates['pending'],
        'failed'    => !empty($settings['template_failed']) ? $settings['template_failed'] : $default_templates['failed'],
    ];
?>
<div class="page-header">
  <h1 class="page-header-title">Telegram Bot Notification Pro</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div id="ajaxResponse" class="mb-3"></div>

        <div class="card mb-3">
            <div class="card-header"><h4 class="card-title">Plugin Updates</h4></div>
            <div class="card-body">
                <p class="form-text">Check for new versions of the plugin directly from GitHub.</p>
                <button id="checkForUpdatesBtn" class="btn btn-secondary">Check for Updates</button>
                <div id="updateCheckResponse" class="mt-3"></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h4 class="card-title">1. Bot Settings</h4></div>
            <div class="card-body">
                <?php if(empty($bot_token)): ?>
                <form id="botTokenForm">
                    <input type="hidden" name="telegram-bot-notification-pro-action" value="save_bot_token">
                    <div class="mb-3">
                        <label for="bot_token" class="form-label">Telegram Bot Token</label>
                        <input type="text" class="form-control" id="bot_token" name="bot_token" placeholder="Enter your Bot Token" required>
                        <div class="form-text">Get a token from <a href="https://t.me/botfather" target="_blank">@BotFather</a> by typing `/newbot`.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Connect Bot</button>
                </form>
                <?php else: ?>
                <div class="alert alert-soft-success">
                    <h5 class="alert-heading">Bot Connected!</h5>
                    <p class="mb-1">Your bot <strong>@<?php echo htmlspecialchars($bot_username); ?></strong> is ready to send notifications.</p>
                    <hr class="my-2">
                    <p class="mb-0">
                        <strong>Webhook Status:</strong>
                        <span class="<?php echo (strpos(strtolower($webhook_status), 'error') === false) ? 'text-success' : 'text-danger'; ?>">
                            <?php echo htmlspecialchars($webhook_status); ?>
                        </span>
                    </p>
                </div>
                <button id="deleteBotToken" class="btn btn-danger btn-sm">Disconnect</button>
                <?php endif; ?>
            </div>
        </div>

        <form id="mainSettingsForm" method="post" action="">
            <input type="hidden" name="telegram-bot-notification-pro-action" value="save_settings">

            <div class="card mb-3">
                <div class="card-header"><h4 class="card-title">2. Notification Settings</h4></div>
                <div class="card-body">
                    <h5>Global Status</h5>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="notifications_enabled" name="notifications_enabled" <?php echo ($settings['notifications_enabled'] ?? 'true') === 'true' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="notifications_enabled"><b>Enable All Notifications</b></label>
                    </div>

                    <h5>Receive Notifications For</h5>
                    <div class="d-flex gap-4">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="notify_pending" name="notify_pending" <?php echo ($settings['notify_pending'] ?? 'true') === 'true' ? 'checked' : ''; ?>><label class="form-check-label" for="notify_pending">Pending</label></div>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="notify_completed" name="notify_completed" <?php echo ($settings['notify_completed'] ?? 'true') === 'true' ? 'checked' : ''; ?>><label class="form-check-label" for="notify_completed">Completed</label></div>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="notify_failed" name="notify_failed" <?php echo ($settings['notify_failed'] ?? 'true') === 'true' ? 'checked' : ''; ?>><label class="form-check-label" for="notify_failed">Failed</label></div>
                    </div>
                    
                    <hr>
                    
                    <h5>Interactive Features</h5>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="enable_confirm_button" name="enable_confirm_button" <?php echo ($settings['enable_confirm_button'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="enable_confirm_button"><b>Enable "Confirm Transaction" Button for Pending Notifications</b></label>
                        <div class="form-text">If enabled, a button will appear on pending transaction notifications, allowing you to confirm them directly from Telegram.</div>
                    </div>
                    <hr>

                    <h5>Recipient Chat IDs</h5>
                    <p class="form-text">To get a Chat ID, send the `/start` command to your bot on Telegram.</p>
                    <div id="chatIdsContainer">
                        <?php if (empty($chat_ids)): ?>
                            <p class="text-muted">No chat IDs added yet.</p>
                        <?php endif; ?>
                        <?php foreach($chat_ids as $index => $chat): ?>
                        <div class="row g-2 align-items-center mb-2 chat-id-row">
                            <div class="col"><input type="text" class="form-control" name="chat_ids[<?php echo $index; ?>][id]" placeholder="Chat ID" value="<?php echo htmlspecialchars($chat['id']); ?>" required></div>
                            <div class="col"><input type="text" class="form-control" name="chat_ids[<?php echo $index; ?>][name]" placeholder="Name/Description" value="<?php echo htmlspecialchars($chat['name']); ?>"></div>
                            <div class="col-auto"><div class="form-check form-switch" title="Enable/Disable"><input class="form-check-input" type="checkbox" name="chat_ids[<?php echo $index; ?>][enabled]" <?php echo ($chat['enabled'] ?? 'false') === 'true' ? 'checked' : ''; ?>></div></div>
                            <div class="col-auto"><button type="button" class="btn btn-soft-danger btn-icon btn-sm remove-chat-id" title="Remove"><i class="bi-trash"></i></button></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="addChatId" class="btn btn-soft-secondary btn-sm mt-2"><i class="bi-plus"></i> Add Chat ID</button>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h4 class="card-title">3. Message Templates</h4></div>
                <div class="card-body">
                    <p class="form-text">Customize your notification messages. Available placeholders: <code>{amount}</code>, <code>{currency}</code>, <code>{customer_name}</code>, <code>{payment_method}</code>, <code>{sender_number}</code>, <code>{date}</code>, <code>{payment_id}</code>, <code>{gateway_trx_id}</code>, <code>{status}</code></p>
                    <div class="mb-3">
                        <label for="template_completed" class="form-label">Completed Transaction</label>
                        <textarea class="form-control" id="template_completed" name="template_completed" rows="5"><?php echo htmlspecialchars($templates['completed']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="template_pending" class="form-label">Pending Transaction</label>
                        <textarea class="form-control" id="template_pending" name="template_pending" rows="5"><?php echo htmlspecialchars($templates['pending']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="template_failed" class="form-label">Failed Transaction</label>
                        <textarea class="form-control" id="template_failed" name="template_failed" rows="5"><?php echo htmlspecialchars($templates['failed']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title">4. Available Bot Commands</h4></div>
                <div class="card-body">
                    <p class="form-text">You can interact with your bot by sending these commands in your Telegram chat.</p>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>/start</strong> - Get your Chat ID.</li>
                        <li class="list-group-item"><strong>/last_transaction</strong> - Get details of the most recent transaction.</li>
                        <li class="list-group-item"><strong>/sales_today</strong> - Get the total sales amount for today.</li>
                        <li class="list-group-item"><strong>/sales_yesterday</strong> - Get the total sales amount for yesterday.</li>
                        <li class="list-group-item"><strong>/sales_this_month</strong> - Get the total sales amount for the current month.</li>
                        <li class="list-group-item"><strong>/pending_transactions</strong> - Get a count of pending transactions.</li>
                        <li class="list-group-item"><strong>/failed_transactions</strong> - Get a count of failed transactions.</li>
                        <li class="list-group-item"><strong>/completed_transactions</strong> - Get a count of completed transactions.</li>
                        <li class="list-group-item"><strong>/help</strong> - Show all available commands.</li>
                    </ul>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-primary">Save All Settings</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // --- Helper Functions ---
    function showResponse(message, isSuccess) {
        const alertClass = isSuccess ? 'alert-success' : 'alert-danger';
        $('#ajaxResponse').html(`<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`);
        window.scrollTo(0, 0);
    }

    function handleFormSubmit(form, successCallback) {
        const button = form.find('button[type="submit"]');
        const originalButtonText = button.html();
        button.html('<span class="spinner-border spinner-border-sm"></span> Saving...').prop('disabled', true);

        $.ajax({
            url: '', type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(response) {
                showResponse(response.message, response.status);
                if (response.status && successCallback) {
                    setTimeout(() => successCallback(response), 500);
                }
            },
            error: function() { showResponse('An unexpected error occurred. Please check server logs.', false); },
            complete: function() { button.html(originalButtonText).prop('disabled', false); }
        });
    }

    // --- Event Handlers ---
    $('#botTokenForm, #mainSettingsForm').on('submit', function(e) {
        e.preventDefault();
        handleFormSubmit($(this), () => window.location.reload());
    });

    $('#deleteBotToken').on('click', function() {
        if (!confirm('Are you sure? This will disconnect your bot.')) return;
        const button = $(this);
        button.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        $.ajax({
            url: '', type: 'POST', data: { 'telegram-bot-notification-pro-action': 'delete_bot_token' }, dataType: 'json',
            success: function(response) {
                showResponse(response.message, response.status);
                if (response.status) setTimeout(() => window.location.reload(), 500);
            }
        });
    });

    $('#addChatId').on('click', function() {
        const index = $('.chat-id-row').length;
        if(index === 0) $('#chatIdsContainer').html('');
        const newRow = `
            <div class="row g-2 align-items-center mb-2 chat-id-row">
                <div class="col"><input type="text" class="form-control" name="chat_ids[${index}][id]" placeholder="Chat ID" required></div>
                <div class="col"><input type="text" class="form-control" name="chat_ids[${index}][name]" placeholder="Name/Description"></div>
                <div class="col-auto"><div class="form-check form-switch" title="Enable/Disable"><input class="form-check-input" type="checkbox" name="chat_ids[${index}][enabled]" checked></div></div>
                <div class="col-auto"><button type="button" class="btn btn-soft-danger btn-icon btn-sm remove-chat-id" title="Remove"><i class="bi-trash"></i></button></div>
            </div>`;
        $('#chatIdsContainer').append(newRow);
    });

    $('#chatIdsContainer').on('click', '.remove-chat-id', function() {
        $(this).closest('.chat-id-row').remove();
    });

    // --- Update Checker ---
    $('#checkForUpdatesBtn').on('click', function() {
        const button = $(this);
        const responseContainer = $('#updateCheckResponse');
        const originalButtonText = button.html();
        
        button.html('<span class="spinner-border spinner-border-sm"></span> Checking...').prop('disabled', true);
        responseContainer.html('');

        $.ajax({
            url: '',
            type: 'POST',
            data: { 'telegram-bot-notification-pro-action': 'check_for_updates' },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    if (response.update_available) {
                        const update = response.data;
                        const changelog = update.changelog.replace(/\n/g, '<br>');
                        const updateHtml = `
                            <div class="alert alert-info mt-3">
                                <h4 class="alert-heading">🚀 New Version Available!</h4>
                                <p>A new version (<strong>${update.new_version}</strong>) is available.</p>
                                <hr>
                                <h5>Release Notes:</h5>
                                <div>${changelog}</div>
                                <a href="${update.download_url}" class="btn btn-success mt-3" target="_blank">Download Update</a>
                            </div>`;
                        responseContainer.html(updateHtml);
                    } else {
                        responseContainer.html(`<div class="alert alert-success mt-3">${response.message}</div>`);
                    }
                } else {
                     responseContainer.html(`<div class="alert alert-danger mt-3">Error: ${response.message}</div>`);
                }
            },
            error: function() {
                responseContainer.html('<div class="alert alert-danger mt-3">An unexpected error occurred.</div>');
            },
            complete: function() {
                button.html(originalButtonText).prop('disabled', false);
            }
        });
    });
});
</script>