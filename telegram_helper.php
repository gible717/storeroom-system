<?php
// telegram_helper.php - Helper functions for Telegram notifications

require_once __DIR__ . '/telegram_config.php';

/**
 * Send notification to Telegram
 * @param string $message - Message to send
 * @param array $keyboard - Optional inline keyboard buttons
 * @return bool - True if sent successfully to at least one admin
 */

function send_telegram_notification($message, $keyboard = null) {
    // Check if Telegram is enabled
    if (!TELEGRAM_ENABLED) {
        return false;
    }

    // Validate bot token
    if (TELEGRAM_BOT_TOKEN === 'YOUR_BOT_TOKEN_HERE' || empty(TELEGRAM_BOT_TOKEN)) {
        error_log("Telegram notification failed: Bot token not configured");
        return false;
    }

    // Validate chat IDs
    if (empty(TELEGRAM_ADMIN_CHAT_IDS)) {
        error_log("Telegram notification failed: No admin chat IDs configured");
        return false;
    }

    $bot_token = TELEGRAM_BOT_TOKEN;
    $chat_ids = TELEGRAM_ADMIN_CHAT_IDS;
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

    $success_count = 0;

    foreach ($chat_ids as $chat_id) {
        // Skip empty chat IDs
        if (empty($chat_id) || $chat_id === '123456789') {
            continue;
        }

        // Use cURL to send message
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout

        // If keyboard is provided, send as JSON
        if ($keyboard !== null) {
            $data = [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
                'reply_markup' => [
                    'inline_keyboard' => $keyboard
                ]
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            // Send as URL-encoded form data
            $data = [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response !== false && $http_code === 200) {
            $success_count++;
        } else {
            error_log("Telegram notification failed for chat ID {$chat_id}: " . curl_error($ch));
        }

        curl_close($ch);
    }

    return $success_count > 0;
}

/**
 * Format new request notification message
 * @param int $id_permohonan - Request ID
 * @param string $nama_pemohon - Requester name
 * @param string $jawatan_pemohon - Requester position
 * @param int $item_count - Number of items requested
 * @param string $catatan - Notes (optional)
 * @return string - Formatted message
 */
function format_new_request_notification($id_permohonan, $nama_pemohon, $jawatan_pemohon, $item_count, $catatan = '') {
    // Set timezone to Malaysia
    date_default_timezone_set('Asia/Kuala_Lumpur');

    $message = "🔔 <b>PERMOHONAN BARU</b>\n\n";
    $message .= "📋 ID Permohonan: <b>#{$id_permohonan}</b>\n";
    $message .= "👤 Pemohon: {$nama_pemohon}\n";

    // Only show jawatan if not empty
    if (!empty($jawatan_pemohon)) {
        $message .= "💼 Jawatan: {$jawatan_pemohon}\n";
    }

    $message .= "📦 Jumlah Item: {$item_count}\n";
    $message .= "📅 Tarikh: " . date('d/m/Y H:i') . "\n";

    // Only show catatan if not empty
    if (!empty($catatan)) {
        $message .= "📝 Catatan: " . htmlspecialchars($catatan) . "\n";
    }

    $message .= "\n⚠️ Sila log masuk ke sistem untuk semakan dan kelulusan.";

    return $message;
}

/**
 * Send new request notification with clickable button
 */
function send_new_request_notification($id_permohonan, $nama_pemohon, $jawatan_pemohon, $item_count, $catatan = '') {
    $message = format_new_request_notification($id_permohonan, $nama_pemohon, $jawatan_pemohon, $item_count, $catatan);

    // Only add button if URL is not localhost (Telegram doesn't support localhost URLs)
    $keyboard = null;
    $login_url = SYSTEM_BASE_URL . '/index.php';

    if (strpos($login_url, 'localhost') === false && strpos($login_url, '127.0.0.1') === false) {
        // URL is public, add button
        $keyboard = [
            [
                ['text' => '🔗 Log Masuk ke Sistem', 'url' => $login_url]
            ]
        ];
    } else {
        // URL is localhost, add text instead
        $message .= "\n\n🔗 Link: {$login_url}";
    }

    return send_telegram_notification($message, $keyboard);
}

/**
 * Format monthly restock reminder notification
 * @return string - Formatted message
 */
function format_monthly_restock_reminder() {
    // Set timezone to Malaysia
    date_default_timezone_set('Asia/Kuala_Lumpur');

    $message = "📅 <b>PERINGATAN STOK BULANAN</b>\n\n";
    $message .= "🔔 Ini adalah peringatan untuk menyemak dan membuat permohonan stok bulanan.\n\n";
    $message .= "📋 Sila semak:\n";
    $message .= "• Stok semasa di stor\n";
    $message .= "• Item yang perlu ditambah\n";
    $message .= "• Keperluan untuk bulan hadapan\n\n";
    $message .= "📅 Bulan: <b>" . date('F Y') . "</b>\n\n";
    $message .= "⚠️ Sila log masuk ke sistem untuk membuat permohonan stok.";

    return $message;
}

/**
 * Check if today should send monthly restock reminder
 * Sends on any weekday (Monday-Friday) during the first week of each month
 * @return bool - True if should send reminder today
 */
function should_send_monthly_reminder() {
    // Set timezone to Malaysia
    date_default_timezone_set('Asia/Kuala_Lumpur');

    // Get current day of month (1-31)
    $day_of_month = (int)date('j');

    // Get current day of week (1=Monday, 2=Tuesday, ..., 7=Sunday)
    $day_of_week = (int)date('N');

    // Check if today is a weekday (Monday=1 to Friday=5)
    $is_weekday = ($day_of_week >= 1 && $day_of_week <= 5);

    // Check if it's the first week (days 1-7)
    $is_first_week = ($day_of_month >= 1 && $day_of_month <= 7);

    // Send on any weekday during the first week of the month
    return $is_weekday && $is_first_week;
}

/**
 * Send monthly restock reminder to admins
 * @return bool - True if sent successfully
 */
function send_monthly_restock_reminder() {
    if (!should_send_monthly_reminder()) {
        return false;
    }

    $message = format_monthly_restock_reminder();

    // Only add button if URL is not localhost (Telegram doesn't support localhost URLs)
    $keyboard = null;
    $login_url = SYSTEM_BASE_URL . '/index.php';

    if (strpos($login_url, 'localhost') === false && strpos($login_url, '127.0.0.1') === false) {
        // URL is public, add button
        $keyboard = [
            [
                ['text' => '🔗 Log Masuk ke Sistem', 'url' => $login_url]
            ]
        ];
    } else {
        // URL is localhost, add text instead
        $message .= "\n\n🔗 Link: {$login_url}";
    }

    return send_telegram_notification($message, $keyboard);
}
?>
