<?php
/**
 * cron_monthly_reminder.php
 *
 * Automated script to send monthly restock reminders to admins
 * This script should be run daily via a cron job or Windows Task Scheduler
 *
 * It will send notifications on ANY weekday (Monday-Friday) during the first
 * week of the month (days 1-7). This provides multiple opportunities to send
 * the reminder in case the script fails on a particular day.
 *
 * The script will only send ONE reminder per month (tracked in database).
 *
 * CRON SETUP:
 * Run this script daily at 9:00 AM:
 * 0 9 * * * php /path/to/storeroom/cron_monthly_reminder.php
 *
 * Or for Windows Task Scheduler:
 * Program: C:\laragon\bin\php\php-8.x.x\php.exe
 * Arguments: C:\laragon\www\storeroom\cron_monthly_reminder.php
 * Schedule: Daily at 9:00 AM
 */

// Prevent direct browser access (optional security measure)
// Comment this out if you want to test via browser
// if (php_sapi_name() !== 'cli') {
//     die('This script can only be run from command line');
// }

require_once __DIR__ . '/telegram_helper.php';
require_once __DIR__ . '/db.php';

// Log file for tracking when reminders are sent
$log_file = __DIR__ . '/logs/monthly_reminder.log';
$log_dir = dirname($log_file);

// Create logs directory if it doesn't exist
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Function to write to log
function write_log($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Start logging
write_log("=== Cron job started ===");

// Check if monthly reminders are enabled
if (!MONTHLY_REMINDER_ENABLED) {
    write_log("Monthly reminders are disabled in config");
    write_log("=== Cron job ended ===\n");
    exit(0);
}

// Check if today is a valid day to send reminder
if (!should_send_monthly_reminder()) {
    $day_of_month = date('j');
    $day_name = date('l');
    write_log("Skipping: Not in first week or not a weekday (Day {$day_of_month}, {$day_name})");
    write_log("=== Cron job ended ===\n");
    exit(0);
}

// Check if reminder was already sent today
$today = date('Y-m-d');
$check_sql = "SELECT COUNT(*) as count FROM telegram_reminder_log
              WHERE reminder_date = ? AND reminder_type = 'monthly_restock'";

// Create table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS telegram_reminder_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reminder_type VARCHAR(50) NOT NULL,
    reminder_date DATE NOT NULL,
    sent_at DATETIME NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY unique_reminder (reminder_type, reminder_date)
)";

try {
    $conn->query($create_table_sql);

    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        write_log("Reminder already sent today ({$today})");
        write_log("=== Cron job ended ===\n");
        exit(0);
    }

    // Send the reminder
    write_log("Sending monthly restock reminder...");
    $success = send_monthly_restock_reminder();

    if ($success) {
        // Log the successful send
        $insert_sql = "INSERT INTO telegram_reminder_log
                       (reminder_type, reminder_date, sent_at, success)
                       VALUES ('monthly_restock', ?, NOW(), 1)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("s", $today);
        $stmt->execute();

        write_log("SUCCESS: Monthly restock reminder sent to admins");
    } else {
        write_log("FAILED: Could not send monthly restock reminder");

        // Log the failed attempt
        $insert_sql = "INSERT INTO telegram_reminder_log
                       (reminder_type, reminder_date, sent_at, success)
                       VALUES ('monthly_restock', ?, NOW(), 0)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("s", $today);
        $stmt->execute();
    }

} catch (Exception $e) {
    write_log("ERROR: " . $e->getMessage());
}

write_log("=== Cron job ended ===\n");
?>