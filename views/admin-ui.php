<div class="card">
    <div class="card-header">
        <h4 class="card-title">Manage Gateway Order</h4>
    </div>
    <div class="card-body">
        <p>Drag and drop the payment gateways to reorder them. The order will be reflected on the checkout page.</p>
        <div id="response-message" class="mb-3"></div>
        <ul id="sortable-gateways" class="list-group">
            <?php
            global $db_prefix;
            
            $conn = connectDatabase();
            $order_table = $db_prefix . 'gateway_order';
            $plugins_table = $db_prefix . 'plugins';

            $sql = "SELECT p.plugin_name, p.plugin_slug
                    FROM `$plugins_table` p
                    JOIN `$order_table` o ON p.plugin_slug = o.plugin_slug
                    WHERE p.plugin_dir = 'payment-gateway' AND p.status = 'active'
                    ORDER BY o.display_order ASC";

            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<li class="list-group-item" data-id="' . htmlspecialchars($row['plugin_slug']) . '" style="cursor: move;">' . htmlspecialchars($row['plugin_name']) . '</li>';
                }
            } else {
                echo '<li class="list-group-item text-muted">No active payment gateways found. Please activate them and then deactivate/reactivate this plugin to update the list.</li>';
            }
            $conn->close();
            ?>
        </ul>
        <button id="save-order" class="btn btn-primary mt-3">Save Order</button>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h4 class="card-title">Plugin Updates</h4>
    </div>
    <div class="card-body">
        <p class="form-text">Check for new versions of the plugin directly from GitHub.</p>
        <button id="checkForUpdatesBtn" class="btn btn-secondary">Check for Updates</button>
        <div id="updateCheckResponse" class="mt-3"></div>
    </div>
</div>

<div class="developer-info" style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px; text-align: center;">
    <h5 class="developer-title" style="margin-bottom: 10px;">Plugin Developer Information</h5>
    <p class="developer-name" style="margin-bottom: 5px; font-size: 16px;"><strong>Refat Rahman</strong></p>
    <div class="developer-links">
        <a href="https://www.facebook.com/rjrefat" target="_blank" style="margin-right: 10px; text-decoration: none;">
            <i class="fab fa-facebook-square" style="font-size: 24px;"></i> Facebook
        </a>
        <a href="https://github.com/refatbd/" target="_blank" style="text-decoration: none;">
            <i class="fab fa-github-square" style="font-size: 24px;"></i> Github
        </a>
    </div>
</div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/pp-content/plugins/modules/gateway-manager/assets/Sortable.min.js'; ?>"></script>
<script>
    const sortableList = document.getElementById('sortable-gateways');

    if (sortableList) {
        Sortable.create(sortableList);

        const saveBtn = document.getElementById('save-order');
        const responseBox = document.getElementById('response-message');
        
        // Define the URL for our dedicated AJAX handler
        const ajaxUrl = '<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/pp-content/plugins/modules/gateway-manager/ajax-handler.php'; ?>';

        saveBtn.addEventListener('click', function () {
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            saveBtn.disabled = true;

            const order = [];
            sortableList.querySelectorAll('li').forEach(function (li, index) {
                order.push({
                    id: li.getAttribute('data-id'),
                    order: index
                });
            });

            const formData = new FormData();
            formData.append('action', 'save_gateway_order');
            formData.append('order', JSON.stringify(order));
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    responseBox.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                } else {
                    responseBox.innerHTML = '<div class="alert alert-danger">Error: ' + (data.message || 'Failed to save order.') + '</div>';
                }
            })
            .catch(err => {
                responseBox.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please check the browser console.</div>';
                console.error('Fetch Error:', err);
            })
            .finally(() => {
                saveBtn.innerHTML = 'Save Order';
                saveBtn.disabled = false;
                setTimeout(() => { responseBox.innerHTML = ''; }, 5000);
            });
        });
    }

    // --- Update Checker ---
    document.getElementById('checkForUpdatesBtn').addEventListener('click', function() {
        const button = this;
        const responseContainer = document.getElementById('updateCheckResponse');
        const originalButtonText = button.innerHTML;
        
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Checking...';
        button.disabled = true;
        responseContainer.innerHTML = '';

        const formData = new FormData();
        formData.append('action', 'check_for_updates');

        fetch('<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/pp-content/plugins/modules/gateway-manager/ajax-handler.php'; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
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
                    responseContainer.innerHTML = updateHtml;
                } else {
                    responseContainer.innerHTML = `<div class="alert alert-success mt-3">${response.message}</div>`;
                }
            } else {
                 responseContainer.innerHTML = `<div class="alert alert-danger mt-3">Error: ${response.message}</div>`;
            }
        })
        .catch(err => {
            responseContainer.innerHTML = '<div class="alert alert-danger mt-3">An unexpected error occurred.</div>';
            console.error('Fetch Error:', err);
        })
        .finally(() => {
            button.innerHTML = originalButtonText;
            button.disabled = false;
        });
    });
</script>