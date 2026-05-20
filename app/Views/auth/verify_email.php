<?php
$pageTitle = 'Verify Email - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-container">
    <!-- Left Side: Branding -->
    <div class="auth-left">
        <div class="auth-left-content">
            <?php if (file_exists(__DIR__ . '/../../../public/images/logo-large.png')): ?>
                <img src="<?php echo $basePath; ?>/images/logo-large.png" alt="SPED LMS Logo" class="auth-logo-large">
            <?php else: ?>
                <i class="bi bi-envelope-check-fill" style="font-size: 6rem; margin-bottom: 2rem;"></i>
            <?php endif; ?>
            
            <h1>Email Verification</h1>
            <p>Verify your email address to continue</p>
            <p>We've sent a 6-digit code to your email</p>
        </div>
    </div>

    <!-- Right Side: Verification Form -->
    <div class="auth-right">
        <div class="auth-form-container">
            <h2>Verify Your Email</h2>
            <p class="subtitle">
                Enter the 6-digit code sent to<br>
                <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></strong>
            </p>

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- OTP Input Form -->
                <form method="POST" action="<?php echo $basePath; ?>/auth/verify-email" id="otpForm">
                    <div class="otp-input-container mb-4">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp1" name="otp1" required autofocus>
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp2" name="otp2" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp3" name="otp3" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp4" name="otp4" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp5" name="otp5" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp6" name="otp6" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-check-circle"></i> Verify Email
                    </button>
                </form>

                <!-- Resend OTP -->
                <div class="text-center mb-3">
                    <p class="text-muted mb-2">Didn't receive the code?</p>
                    <button type="button" class="btn btn-outline-secondary" id="resendBtn" onclick="resendOTP()">
                        <i class="bi bi-arrow-clockwise"></i> Resend Code
                    </button>
                    <p class="text-muted mt-2 small" id="countdown" style="display: none;">
                        Resend available in <span id="timer">60</span> seconds
                    </p>
                </div>

                <div class="text-center">
                    <a href="<?php echo $basePath; ?>/logout" class="text-muted text-decoration-none">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.otp-input-container {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin: 20px 0;
}

.otp-input {
    width: 50px;
    height: 60px;
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    border: 2px solid #ddd;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: white;
}

.otp-input:focus {
    border-color: #a01422;
    outline: none;
    box-shadow: 0 0 0 3px rgba(160, 20, 34, 0.1);
}

.otp-input:valid {
    border-color: #3b6d11;
    background: #f0f9f0;
}

@media (max-width: 576px) {
    .otp-input {
        width: 40px;
        height: 50px;
        font-size: 20px;
    }
    
    .otp-input-container {
        gap: 5px;
    }
}
</style>

<script>
// OTP Input Auto-focus
const otpInputs = document.querySelectorAll('.otp-input');

otpInputs.forEach((input, index) => {
    input.addEventListener('input', function() {
        if (this.value.length === 1 && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
            otpInputs[index - 1].focus();
        }
    });

    // Only allow numbers
    input.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
    
    // Paste support
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').replace(/\D/g, '');
        
        if (pastedData.length === 6) {
            otpInputs.forEach((inp, idx) => {
                inp.value = pastedData[idx] || '';
            });
            otpInputs[5].focus();
        }
    });
});

// Resend OTP with countdown
let countdownTimer = null;

function resendOTP() {
    const resendBtn = document.getElementById('resendBtn');
    const countdown = document.getElementById('countdown');
    const timer = document.getElementById('timer');
    
    // Disable button
    resendBtn.disabled = true;
    countdown.style.display = 'block';
    
    // Send AJAX request
    fetch('<?php echo $basePath; ?>/auth/resend-otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = '<i class="bi bi-check-circle"></i> A new verification code has been sent to your email. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.querySelector('.auth-form-container').insertBefore(alertDiv, document.getElementById('otpForm'));
            
            // Clear OTP inputs
            otpInputs.forEach(input => input.value = '');
            otpInputs[0].focus();
        } else {
            alert(data.message || 'Failed to resend code. Please try again.');
            resendBtn.disabled = false;
            countdown.style.display = 'none';
            return;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to resend code. Please try again.');
        resendBtn.disabled = false;
        countdown.style.display = 'none';
        return;
    });
    
    // Start countdown
    let seconds = 60;
    timer.textContent = seconds;
    
    countdownTimer = setInterval(() => {
        seconds--;
        timer.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(countdownTimer);
            resendBtn.disabled = false;
            countdown.style.display = 'none';
        }
    }, 1000);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
