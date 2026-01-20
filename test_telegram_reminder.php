<?php
/**
 * test_telegram_reminder.php
 *
 * Manual test script to send a monthly reminder immediately
 * Access via browser: http://localhost/storeroom/test_telegram_reminder.php
 *
 * This bypasses the date/time checks and sends the reminder right away
 */

require_once __DIR__ . '/telegram_helper.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Telegram Reminder</title>";
echo "<style>body{font-family:Arial;padding:20px;max-width:800px;margin:0 auto;}";
echo ".success{color:green;} .error{color:red;} .info{color:blue;}";
echo "pre{background:#f4f4f4;padding:10px;border-radius:5px;}</style></head><body>";

echo "<h1>🔔 Test Monthly Restock Reminder</h1>";
echo "<p class='info'>This will send a monthly restock reminder to all configured admins via Telegram.</p>";

// Check if Telegram is enabled
if (!TELEGRAM_ENABLED) {
    echo "<p class='error'>❌ ERROR: Telegram notifications are disabled in telegram_config.php</p>";
    echo "<p>Please set <code>TELEGRAM_ENABLED</code> to <code>true</code> in telegram_config.php</p>";
    exit;
}

// Check if monthly reminders are enabled
if (!MONTHLY_REMINDER_ENABLED) {
    echo "<p class='error'>❌ ERROR: Monthly reminders are disabled in telegram_config.php</p>";
    echo "<p>Please set <code>MONTHLY_REMINDER_ENABLED</code> to <code>true</code> in telegram_config.php</p>";
    exit;
}

echo "<h2>Configuration:</h2>";
echo "<pre>";
echo "Telegram Enabled: " . (TELEGRAM_ENABLED ? "✅ Yes" : "❌ No") . "\n";
echo "Monthly Reminders: " . (MONTHLY_REMINDER_ENABLED ? "✅ Enabled" : "❌ Disabled") . "\n";
echo "Admin Chat IDs: " . implode(", ", TELEGRAM_ADMIN_CHAT_IDS) . "\n";
echo "System URL: " . SYSTEM_BASE_URL . "\n";
echo "</pre>";

echo "<h2>Sending Test Reminder...</h2>";

// Format and display the message that will be sent
$message = format_monthly_restock_reminder();
echo "<h3>Message Preview:</h3>";
echo "<pre>" . htmlspecialchars(strip_tags($message)) . "</pre>";

// Actually send the reminder (bypasses date check)
$keyboard = null;
$login_url = SYSTEM_BASE_URL . '/index.php';

if (strpos($login_url, 'localhost') === false && strpos($login_url, '127.0.0.1') === false) {
    $keyboard = [
        [
            ['text' => '🔗 Log Masuk ke Sistem', 'url' => $login_url]
        ]
    ];
} else {
    $message .= "\n\n🔗 Link: {$login_url}";
}

$success = send_telegram_notification($message, $keyboard);

if ($success) {
    echo "<p class='success'>✅ SUCCESS! Monthly reminder sent to all admins.</p>";
    echo "<p>Check your Telegram app to verify the message was received.</p>";
} else {
    echo "<p class='error'>❌ FAILED to send reminder.</p>";
    echo "<p>Possible issues:</p>";
    echo "<ul>";
    echo "<li>Bot token is invalid or not configured</li>";
    echo "<li>Chat IDs are incorrect</li>";
    echo "<li>Network/internet connection issue</li>";
    echo "<li>Telegram API is down</li>";
    echo "</ul>";
    echo "<p>Check your server error logs for more details.</p>";
}

echo "<hr>";
echo "<p><a href='cron_monthly_reminder.php'>← Back to automated cron script</a></p>";
echo "</body></html>";
?>
