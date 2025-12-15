<?php
// telegram_config.example.php - Telegram Bot Configuration Template
// Copy this file to telegram_config.php and fill in your actual values

// Your Telegram Bot Token (get from @BotFather)
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');

// Admin Chat IDs who will receive notifications (comma-separated)
// To get your chat ID:
// 1. Start a chat with your bot
// 2. Send any message
// 3. Visit: https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
// 4. Look for "chat":{"id":123456789}
define('TELEGRAM_ADMIN_CHAT_IDS', [
    '123456789',  // Admin 1 - Replace with your chat ID
    // '987654321',  // Admin 2 - Add more admins here if needed
]);

// Enable/Disable Telegram notifications
define('TELEGRAM_ENABLED', true); // Set to false to disable notifications

// Enable/Disable monthly restock reminders
define('MONTHLY_REMINDER_ENABLED', true); // Set to false to disable monthly reminders

// Time to send monthly reminders (24-hour format, e.g., '09:00' for 9 AM)
define('MONTHLY_REMINDER_TIME', '09:00');

// System URL for login link in notifications
// Change this to your production URL when deploying (e.g., 'https://yourdomain.com/storeroom')
define('SYSTEM_BASE_URL', 'http://localhost/storeroom');
?>
