<?php
$pageTitle = 'Register - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';

$oldFirstName = $_SESSION['old_first_name'] ?? '';
$oldMiddleName = $_SESSION['old_middle_name'] ?? '';
$oldLastName = $_SESSION['old_last_name'] ?? '';
$oldSuffix = $_SESSION['old_suffix'] ?? '';
$oldEmail = $_SESSION['old_email'] ?? '';
$oldContact = $_SESSION['old_contact'] ?? '';
unset($_SESSION['old_first_name'], $_SESSION['old_middle_name'], $_SESSION['old_last_name'], $_SESSION['old_suffix'], $_SESSION['old_email'], $_SESSION['old_contact']);
?>

<div class="auth-container">
    <!-- Left Side: Branding -->
    <div class="auth-left">
        <div class="auth-left-content">
            <?php if (file_exists(__DIR__ . '/../../../public/images/logo-large.png')): ?>
                <img src="<?php echo $basePath; ?>/images/logo-large.png" alt="SPED LMS Logo" class="auth-logo-large">
            <?php else: ?>
                <i class="bi bi-mortarboard-fill" style="font-size: 6rem; margin-bottom: 2rem;"></i>
            <?php endif; ?>
            
            <h1>Join SPED LMS</h1>
            <p>Create your account and start your journey with us.</p>
            <p>Supporting special education, one learner at a time.</p>
        </div>
    </div>

    <!-- Right Side: Registration Form -->
    <div class="auth-right">
        <div class="auth-form-container">
            <h2>Create Account</h2>
            <p class="subtitle">Fill in your details to register</p>

            <!-- Error Messages -->
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="<?php echo $basePath; ?>/register">
                <!-- CSRF Token -->
                <?php require_once __DIR__ . '/../../Helpers/CSRFHelper.php'; ?>
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(CSRFHelper::getToken()); ?>">

                <!-- Google Sign-Up Button -->
                <a href="<?php echo $basePath; ?>/auth/google" class="btn btn-outline-secondary w-100 mb-3" style="border: 2px solid #ddd;">
                    <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="vertical-align: middle; margin-right: 8px;">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        <path fill="none" d="M0 0h48v48H0z"/>
                    </svg>
                    Sign up with Google
                </a>

                <div class="text-center my-3">
                    <span class="text-muted">OR</span>
                </div>

                <!-- Name Fields (2-column grid) -->
                <div class="form-row-2col mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($oldFirstName); ?>" required autofocus>
                        <label for="first_name"><i class="bi bi-person"></i> First Name *</label>
                    </div>

                    <div class="form-floating">
                        <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle Name" value="<?php echo htmlspecialchars($oldMiddleName); ?>">
                        <label for="middle_name">Middle Name</label>
                    </div>
                </div>

                <div class="form-row-2col mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($oldLastName); ?>" required>
                        <label for="last_name">Last Name *</label>
                    </div>

                    <div class="form-floating">
                        <select class="form-select" id="suffix" name="suffix">
                            <option value="">None</option>
                            <option value="Jr." <?php echo $oldSuffix === 'Jr.' ? 'selected' : ''; ?>>Jr.</option>
                            <option value="Sr." <?php echo $oldSuffix === 'Sr.' ? 'selected' : ''; ?>>Sr.</option>
                            <option value="II" <?php echo $oldSuffix === 'II' ? 'selected' : ''; ?>>II</option>
                            <option value="III" <?php echo $oldSuffix === 'III' ? 'selected' : ''; ?>>III</option>
                            <option value="IV" <?php echo $oldSuffix === 'IV' ? 'selected' : ''; ?>>IV</option>
                        </select>
                        <label for="suffix">Suffix</label>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($oldEmail); ?>" required>
                    <label for="email"><i class="bi bi-envelope"></i> Email Address *</label>
                </div>

                <!-- Contact Number -->
                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="contact_number" name="contact_number" placeholder="09XX XXX XXXX" value="<?php echo htmlspecialchars($oldContact); ?>" pattern="[0-9]{11}" required>
                    <label for="contact_number"><i class="bi bi-telephone"></i> Contact Number *</label>
                    <div class="form-text">
                        <small>Format: 09XXXXXXXXX (11 digits)</small>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="bi bi-lock"></i> Password *</label>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" 
                                style="z-index: 10; text-decoration: none;" 
                                onclick="togglePassword('password', 'togglePasswordIcon')">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <!-- Password strength indicator will be inserted here by JavaScript -->
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        <label for="confirm_password"><i class="bi bi-lock-fill"></i> Confirm Password *</label>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" 
                                style="z-index: 10; text-decoration: none;" 
                                onclick="togglePassword('confirm_password', 'toggleConfirmPasswordIcon')">
                            <i class="bi bi-eye" id="toggleConfirmPasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Register
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-muted mb-0">Already have an account?</p>
                    <a href="<?php echo $basePath; ?>/login" class="text-decoration-none fw-semibold">Login here</a>
                </div>
            </form>

            <!-- Footer -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    &copy; <?php echo date('Y'); ?> SPED LMS. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Real-time password validation
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const form = document.querySelector('form');
    
    // Create password strength indicator
    const strengthIndicator = document.createElement('div');
    strengthIndicator.className = 'password-strength mt-2';
    strengthIndicator.innerHTML = `
        <div class="strength-requirements">
            <small class="requirement" id="req-length">
                <i class="bi bi-x-circle text-danger"></i> At least 8 characters
            </small>
            <small class="requirement" id="req-uppercase">
                <i class="bi bi-x-circle text-danger"></i> One uppercase letter
            </small>
            <small class="requirement" id="req-number">
                <i class="bi bi-x-circle text-danger"></i> One number
            </small>
            <small class="requirement" id="req-special">
                <i class="bi bi-x-circle text-danger"></i> One special character
            </small>
        </div>
    `;
    passwordInput.closest('.mb-3').appendChild(strengthIndicator);
    
    // Validate password on input
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        // Check length
        const lengthReq = document.getElementById('req-length');
        if (password.length >= 8) {
            lengthReq.innerHTML = '<i class="bi bi-check-circle text-success"></i> At least 8 characters';
            lengthReq.classList.add('text-success');
            lengthReq.classList.remove('text-muted');
        } else {
            lengthReq.innerHTML = '<i class="bi bi-x-circle text-danger"></i> At least 8 characters';
            lengthReq.classList.remove('text-success');
            lengthReq.classList.add('text-muted');
        }
        
        // Check uppercase
        const uppercaseReq = document.getElementById('req-uppercase');
        if (/[A-Z]/.test(password)) {
            uppercaseReq.innerHTML = '<i class="bi bi-check-circle text-success"></i> One uppercase letter';
            uppercaseReq.classList.add('text-success');
            uppercaseReq.classList.remove('text-muted');
        } else {
            uppercaseReq.innerHTML = '<i class="bi bi-x-circle text-danger"></i> One uppercase letter';
            uppercaseReq.classList.remove('text-success');
            uppercaseReq.classList.add('text-muted');
        }
        
        // Check number
        const numberReq = document.getElementById('req-number');
        if (/[0-9]/.test(password)) {
            numberReq.innerHTML = '<i class="bi bi-check-circle text-success"></i> One number';
            numberReq.classList.add('text-success');
            numberReq.classList.remove('text-muted');
        } else {
            numberReq.innerHTML = '<i class="bi bi-x-circle text-danger"></i> One number';
            numberReq.classList.remove('text-success');
            numberReq.classList.add('text-muted');
        }
        
        // Check special character
        const specialReq = document.getElementById('req-special');
        if (/[^A-Za-z0-9]/.test(password)) {
            specialReq.innerHTML = '<i class="bi bi-check-circle text-success"></i> One special character';
            specialReq.classList.add('text-success');
            specialReq.classList.remove('text-muted');
        } else {
            specialReq.innerHTML = '<i class="bi bi-x-circle text-danger"></i> One special character';
            specialReq.classList.remove('text-success');
            specialReq.classList.add('text-muted');
        }
        
        // Check confirm password match
        if (confirmPasswordInput.value) {
            validatePasswordMatch();
        }
    });
    
    // Validate password match
    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword) {
            if (password === confirmPassword) {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            } else {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
            }
        }
    }
    
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        // Validate password requirements
        const isValid = password.length >= 8 &&
                       /[A-Z]/.test(password) &&
                       /[0-9]/.test(password) &&
                       /[^A-Za-z0-9]/.test(password);
        
        if (!isValid) {
            e.preventDefault();
            alert('Please meet all password requirements before submitting.');
            passwordInput.focus();
            return false;
        }
        
        // Validate password match
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            confirmPasswordInput.focus();
            return false;
        }
    });
});
</script>

<style>
.password-strength {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px;
}

.strength-requirements {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.requirement {
    display: block;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.requirement i {
    margin-right: 6px;
}

.is-valid {
    border-color: #198754 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
