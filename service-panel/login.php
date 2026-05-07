<?php
session_start();
if (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login — Infinity Computer Service Panel</title>
    <meta name="description" content="Secure staff login portal for Infinity Computer Service Panel.">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;850&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary-color: #1f5fae;
            --primary-dark: #1b518f;
            --bg-light: #f4f7fb;
            --text-dark: #1f2a37;
            --success: #1f7a52;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --border-color: #dbe4ef;
            --card-bg: #ffffff;
            --shadow: 0 10px 30px rgba(20, 40, 80, 0.08);
            --muted: #64748b;
        }

        body {
            font-family: 'Poppins', 'Inter', 'Roboto', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle background pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(31, 95, 174, 0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(251, 42, 113, 0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 80%, rgba(31, 95, 174, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* Header bar — matching main site */
        .login-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
            z-index: 100;
        }
        .header-inner {
            width: min(100% - 2rem, 1160px);
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .header-inner img { height: 38px; width: auto; }
        .brand-col { display: flex; flex-direction: column; align-items: flex-start; line-height: 1; }
        .brand-text {
            font-size: 1.45rem;
            font-weight: 850;
            letter-spacing: -0.8px;
            background: linear-gradient(90deg, #fb2a71 0%, #ff0000 50%, #fb2a71 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #fb2a71;
        }
        .text-accent {
            background: linear-gradient(135deg, #111111 0%, #333333 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #111111;
            font-weight: 700;
            margin-left: 3px;
        }
        .brand-sub {
            font-size: 0.65rem;
            color: #fb2a71;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* Login Card */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            margin: 90px 20px 40px;
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .login-card:hover {
            box-shadow: 0 16px 40px rgba(20, 40, 80, 0.13);
        }

        .card-top-bar {
            height: 5px;
            background: linear-gradient(90deg, #fb2a71 0%, var(--primary-color) 50%, #fb2a71 100%);
        }

        .card-body { padding: 40px 40px 35px; }

        .card-icon-row {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
        }
        .card-icon-badge {
            width: 64px; height: 64px;
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .card-heading {
            text-align: center;
            margin-bottom: 30px;
        }
        .card-heading h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .card-heading p {
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: 400;
        }

        /* Steps */
        .step { display: none; animation: fadeSlide 0.35s ease; }
        .step.active { display: block; }

        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Elements — matching main site */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #495057;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            background: #fff;
            color: var(--text-dark);
            font-family: inherit;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(31, 95, 174, 0.12);
        }

        .btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid transparent;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 14px 1.15rem;
            cursor: pointer;
            background: var(--primary-color);
            color: #fff;
            box-shadow: 0 10px 18px rgba(31, 95, 174, 0.25);
            transition: transform 0.3s, box-shadow 0.3s, background 0.3s;
            text-decoration: none;
            font-family: inherit;
            gap: 8px;
        }
        .btn:hover:not(:disabled) {
            background: var(--primary-dark);
            box-shadow: 0 14px 32px rgba(31, 95, 174, 0.35);
            transform: translateY(-3px) scale(1.03);
        }
        .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none !important;
        }

        /* OTP Boxes */
        .otp-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 25px 0 30px;
        }
        .otp-box {
            width: 52px; height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: #fff;
            outline: none;
            transition: all 0.25s ease;
            font-family: 'Poppins', monospace;
            caret-color: var(--primary-color);
        }
        .otp-box:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(31, 95, 174, 0.12);
        }
        .otp-box.filled {
            border-color: var(--primary-color);
            background: #f0f5ff;
        }
        .otp-box.error-shake {
            border-color: var(--danger);
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .otp-info {
            text-align: center;
            font-size: 0.88rem;
            color: var(--muted);
            margin-bottom: 5px;
        }
        .otp-info strong { color: var(--primary-color); word-break: break-all; }

        /* Action row */
        .action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            font-size: 0.85rem;
        }
        .link-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            transition: color 0.2s;
        }
        .link-btn:hover { color: #fb2a71; }
        .link-btn:disabled { color: var(--muted); cursor: not-allowed; }

        .timer-text {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
        }
        .timer-text span { color: #fb2a71; font-weight: 700; }

        /* Spinner */
        .spinner {
            width: 18px; height: 18px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Toast System */
        .toast-container {
            position: fixed;
            top: 75px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
            width: 90%;
            max-width: 420px;
        }
        .toast {
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastIn 0.35s ease;
            pointer-events: auto;
            border: 1px solid;
        }
        .toast.removing { animation: toastOut 0.3s ease forwards; }
        .toast-success { background: #d1fae5; color: #065f46; border-color: #a7f3d0; }
        .toast-error { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .toast-info { background: #e9f1fb; color: var(--primary-color); border-color: #bfdbfe; }
        .toast-icon { font-size: 1.1rem; flex-shrink: 0; }

        @keyframes toastIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes toastOut {
            to { opacity: 0; transform: translateY(-10px); }
        }

        /* Blocked overlay */
        .blocked-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.97);
            z-index: 20;
            border-radius: 14px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            text-align: center;
            padding: 40px;
        }
        .blocked-overlay.active { display: flex; }
        .blocked-icon { font-size: 2.8rem; }
        .blocked-title { font-size: 1.15rem; font-weight: 700; color: var(--danger); }
        .blocked-text { font-size: 0.88rem; color: var(--muted); line-height: 1.5; }

        /* Footer text */
        .card-footer {
            text-align: center;
            padding: 0 40px 25px;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .card-body { padding: 30px 24px 25px; }
            .otp-box { width: 44px; height: 52px; font-size: 1.3rem; }
            .otp-container { gap: 7px; }
            .card-footer { padding: 0 24px 20px; }
            .header-inner { padding: 0 16px; }
        }
    </style>
</head>
<body>

    <!-- Header — matches main site -->
    <div class="login-header">
        <div class="header-inner">
            <img src="../images/logos/infinity_computer_logo.png" alt="Infinity Computer Logo">
            <div class="brand-col">
                <span class="brand-text">Infinity<span class="text-accent">Computer</span></span>
                <span class="brand-sub">Service Panel</span>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Login Card -->
    <div class="login-card" id="loginCard">
        <div class="card-top-bar"></div>

        <!-- Blocked Overlay -->
        <div class="blocked-overlay" id="blockedOverlay">
            <div class="blocked-icon">🔒</div>
            <div class="blocked-title">Access Temporarily Blocked</div>
            <div class="blocked-text">Too many failed attempts.<br>Please try again after some time.</div>
        </div>

        <div class="card-body">
            <!-- STEP 1: Email -->
            <div class="step active" id="stepEmail">
                <div class="card-icon-row">
                    <div class="card-icon-badge">🔐</div>
                </div>
                <div class="card-heading">
                    <h2>Staff Login</h2>
                    <p>Enter your authorized staff email to continue</p>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="emailInput" class="form-control" placeholder="name@staff.infinitycomputer.in" autocomplete="email" autofocus>
                </div>
                <button class="btn" id="btnGetOtp" onclick="requestOtp()">
                    Get OTP
                </button>
            </div>

            <!-- STEP 2: OTP -->
            <div class="step" id="stepOtp">
                <div class="card-icon-row">
                    <div class="card-icon-badge">✉️</div>
                </div>
                <div class="card-heading">
                    <h2>Verify OTP</h2>
                    <p>Enter the 6-digit code sent to your email</p>
                </div>
                <div class="otp-info">Sent to <strong id="displayEmail"></strong></div>
                <div class="otp-container" id="otpContainer">
                    <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="0">
                    <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                    <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                    <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                    <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                    <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
                </div>
                <button class="btn" id="btnVerify" onclick="verifyOtp()">
                    Verify OTP
                </button>
                <div class="action-row">
                    <button class="link-btn" id="btnBack" onclick="goBack()">← Change Email</button>
                    <div>
                        <span class="timer-text" id="timerText">Resend in <span id="countdown">30</span>s</span>
                        <button class="link-btn" id="btnResend" onclick="requestOtp()" style="display:none;">Resend OTP</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            &copy; 2026 Infinity Computer &middot; Authorized personnel only
        </div>
    </div>

    <script>
        let currentEmail = '';
        let resendTimer = null;

        // Check for session expired redirect
        const params = new URLSearchParams(window.location.search);
        if (params.get('expired') === '1') {
            document.addEventListener('DOMContentLoaded', () => showToast('Session expired. Please login again.', 'info'));
        }

        // Enter key support
        document.getElementById('emailInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') requestOtp();
        });

        // OTP Box Logic
        const otpBoxes = document.querySelectorAll('.otp-box');
        otpBoxes.forEach((box, i) => {
            box.addEventListener('input', (e) => {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val;
                if (val) {
                    e.target.classList.add('filled');
                    if (i < 5) otpBoxes[i + 1].focus();
                } else {
                    e.target.classList.remove('filled');
                }
                if (getOtpValue().length === 6) {
                    setTimeout(() => verifyOtp(), 250);
                }
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && i > 0) {
                    otpBoxes[i - 1].focus();
                    otpBoxes[i - 1].value = '';
                    otpBoxes[i - 1].classList.remove('filled');
                }
            });
            box.addEventListener('focus', () => box.select());
            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').slice(0, 6);
                paste.split('').forEach((ch, idx) => {
                    if (otpBoxes[idx]) {
                        otpBoxes[idx].value = ch;
                        otpBoxes[idx].classList.add('filled');
                    }
                });
                if (paste.length > 0) otpBoxes[Math.min(paste.length, 5)].focus();
                if (paste.length === 6) setTimeout(() => verifyOtp(), 250);
            });
        });

        function getOtpValue() {
            return Array.from(otpBoxes).map(b => b.value).join('');
        }
        function clearOtpBoxes() {
            otpBoxes.forEach(b => { b.value = ''; b.classList.remove('filled', 'error-shake'); });
        }

        // Request OTP
        async function requestOtp() {
            const email = document.getElementById('emailInput').value.trim().toLowerCase();
            if (!email) { showToast('Please enter your email address', 'error'); return; }
            if (!email.includes('@')) { showToast('Please enter a valid email address', 'error'); return; }

            const btn = document.getElementById('btnGetOtp');
            setLoading(btn, true);

            try {
                const res = await fetch('api/send_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const json = await res.json();

                if (json.status === 'success') {
                    currentEmail = email;
                    document.getElementById('displayEmail').textContent = email;
                    showStep('stepOtp');
                    clearOtpBoxes();
                    otpBoxes[0].focus();
                    startResendTimer();
                    showToast('OTP sent to your email', 'success');
                } else {
                    showToast(json.message, 'error');
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
            }
            setLoading(btn, false);
        }

        // Verify OTP
        async function verifyOtp() {
            const otp = getOtpValue();
            if (otp.length !== 6) { showToast('Please enter all 6 digits', 'error'); return; }

            const btn = document.getElementById('btnVerify');
            setLoading(btn, true);

            try {
                const res = await fetch('api/verify_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ otp })
                });
                const json = await res.json();

                if (json.status === 'success') {
                    showToast('Login successful! Redirecting...', 'success');
                    btn.innerHTML = '✓ Redirecting...';
                    btn.disabled = true;
                    setTimeout(() => { window.location.href = json.redirect || 'index.php'; }, 800);
                    return;
                } else if (json.blocked) {
                    document.getElementById('blockedOverlay').classList.add('active');
                    showToast(json.message, 'error');
                } else if (json.expired) {
                    showToast(json.message, 'error');
                    goBack();
                } else {
                    showToast(json.message, 'error');
                    otpBoxes.forEach(b => b.classList.add('error-shake'));
                    setTimeout(() => { clearOtpBoxes(); otpBoxes[0].focus(); }, 500);
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
            }
            setLoading(btn, false);
        }

        function goBack() {
            showStep('stepEmail');
            clearResendTimer();
            document.getElementById('emailInput').focus();
        }

        function showStep(id) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById(id).classList.add('active');
        }

        // Resend timer
        function startResendTimer() {
            clearResendTimer();
            let seconds = 30;
            document.getElementById('timerText').style.display = 'inline';
            document.getElementById('btnResend').style.display = 'none';
            document.getElementById('countdown').textContent = seconds;

            resendTimer = setInterval(() => {
                seconds--;
                document.getElementById('countdown').textContent = seconds;
                if (seconds <= 0) {
                    clearResendTimer();
                    document.getElementById('timerText').style.display = 'none';
                    document.getElementById('btnResend').style.display = 'inline';
                }
            }, 1000);
        }
        function clearResendTimer() {
            if (resendTimer) { clearInterval(resendTimer); resendTimer = null; }
        }

        // Loading state
        function setLoading(btn, loading) {
            if (loading) {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = '<div class="spinner"></div> Please wait...';
            } else {
                btn.disabled = false;
                if (btn.dataset.originalText) btn.innerHTML = btn.dataset.originalText;
            }
        }

        // Toast notifications
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const icons = { success: '✅', error: '❌', info: 'ℹ️' };
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<span class="toast-icon">${icons[type]}</span><span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>
