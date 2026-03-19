<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Infinity Computer</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #fb2a71;
            --primary-hover: #ff0000;
            --secondary-color: #111111;
            --bg-gradient: linear-gradient(135deg, #fdf2f7 0%, #f1f5f9 100%);
            --card-shadow: 0 15px 40px rgba(251, 42, 113, 0.15);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 420px;
            padding: 3rem;
            border: 1px solid rgba(251, 42, 113, 0.05);
        }

        .login-card h2 {
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .login-card p {
            color: #64748b;
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.8rem 1.2rem;
            border: 2px solid #edf2f7;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(251, 42, 113, 0.1);
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(251, 42, 113, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(251, 42, 113, 0.3);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .modal-content {
            border-radius: 24px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .otp-input {
            letter-spacing: 0.8rem;
            font-size: 1.8rem;
            font-weight: 800;
            text-align: center;
            color: var(--secondary-color);
            border: 2px solid var(--primary-color);
        }

        .timer-text {
            color: var(--primary-color);
            font-weight: 700;
        }

        #loadingOverlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.85);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .spinner-border {
            color: var(--primary-color) !important;
            width: 3rem;
            height: 3rem;
        }

        .brand-logo {
            height: 60px;
            width: auto;
            display: block;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>

    <div id="loadingOverlay">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="login-card">
        <img src="../images/logos/infinity_computer_logo.png" alt="Infinity Computer Logo" class="brand-logo">
        <h2>Admin Portal</h2>
        <p>Enter your authorized email to receive OTP</p>
        
        <form id="emailForm">
            <div class="mb-4">
                <label for="email" class="form-label d-none">Email Address</label>
                <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                <div class="invalid-feedback" id="emailError">Unauthorized email address.</div>
            </div>
            <button type="submit" class="btn btn-primary" id="generateOtpBtn">Generate OTP</button>
        </form>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title w-100 text-center fw-bold">Verify OTP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted mb-4">Enter the 6-digit code sent to your email.</p>
                    
                    <form id="otpForm">
                        <input type="text" id="otpInput" class="form-control otp-input mb-3" maxlength="6" pattern="\d{6}" required placeholder="000000">
                        <div class="mb-3 text-center small">
                            OTP expires in: <span id="timer" class="timer-text">00:30</span>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">Verify & Login</button>
                        <button type="button" id="resendBtn" class="btn btn-link w-100 text-decoration-none">Resend OTP</button>
                    </form>
                    <div id="otpFeedback" class="mt-3 small"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const emailForm = document.getElementById('emailForm');
        const otpForm = document.getElementById('otpForm');
        const emailInput = document.getElementById('email');
        const otpInput = document.getElementById('otpInput');
        const generateOtpBtn = document.getElementById('generateOtpBtn');
        const resendBtn = document.getElementById('resendBtn');
        const timerSpan = document.getElementById('timer');
        const otpFeedback = document.getElementById('otpFeedback');
        const emailError = document.getElementById('emailError');
        const loading = document.getElementById('loadingOverlay');
        const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));

        let timerInterval;
        let resendAttempts = 0;

        function startTimer(duration) {
            return new Promise((resolve) => {
                clearInterval(timerInterval);
                let timer = duration, minutes, seconds;
                timerInterval = setInterval(() => {
                    minutes = parseInt(timer / 60, 10);
                    seconds = parseInt(timer % 60, 10);

                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    seconds = seconds < 10 ? "0" + seconds : seconds;

                    timerSpan.textContent = minutes + ":" + seconds;

                    if (--timer < 0) {
                        clearInterval(timerInterval);
                        timerSpan.textContent = "Expired";
                        resolve();
                    }
                }, 1000);
            });
        }

        emailForm.onsubmit = async (e) => {
            e.preventDefault();
            const email = emailInput.value;
            
            loading.style.display = 'flex';
            generateOtpBtn.disabled = true;

            try {
                const response = await fetch('generate_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}`
                });
                const result = await response.json();

                if (result.success) {
                    otpModal.show();
                    startTimer(30).then(() => {
                         // Optional: You could disable the verify button here if needed
                    }); 
                    emailError.style.display = 'none';
                    otpFeedback.textContent = '';
                } else {
                    emailError.textContent = result.message;
                    emailError.style.display = 'block';
                    emailInput.classList.add('is-invalid');
                }
            } catch (err) {
                alert('An error occurred. Please try again.');
            } finally {
                loading.style.display = 'none';
                generateOtpBtn.disabled = false;
            }
        };

        otpForm.onsubmit = async (e) => {
            e.preventDefault();
            const otp = otpInput.value;

            loading.style.display = 'flex';

            try {
                const response = await fetch('verify_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `otp=${encodeURIComponent(otp)}`
                });
                const result = await response.json();

                if (result.success) {
                    window.location.href = 'admin_dashboard.php';
                } else {
                    otpFeedback.className = 'mt-3 small text-danger';
                    otpFeedback.textContent = result.message;
                }
            } catch (err) {
                alert('Verification failed.');
            } finally {
                loading.style.display = 'none';
            }
        };

        resendBtn.onclick = async () => {
            loading.style.display = 'flex';
            resendBtn.disabled = true;

            try {
                const response = await fetch('generate_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(emailInput.value)}&resend=true`
                });
                const result = await response.json();

                if (result.success) {
                    startTimer(30);
                    otpFeedback.className = 'mt-3 small text-success';
                    otpFeedback.textContent = 'New OTP sent to your email.';
                } else {
                    otpFeedback.className = 'mt-3 small text-danger';
                    otpFeedback.textContent = result.message;
                    if (result.limit_reached) resendBtn.style.display = 'none';
                }
            } catch (err) {
                alert('Resend failed.');
            } finally {
                loading.style.display = 'none';
                if (!resendBtn.style.display === 'none') {
                    // Re-enable after a short delay to prevent spamming
                    setTimeout(() => resendBtn.disabled = false, 10000);
                }
            }
        };
    </script>
</body>
</html>
