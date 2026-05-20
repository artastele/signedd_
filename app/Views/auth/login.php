<?php
$pageTitle = 'Login - SPED LMS';
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
                <i class="bi bi-mortarboard-fill" style="font-size: 6rem; margin-bottom: 2rem;"></i>
            <?php endif; ?>
            
            <h1>SPED LMS</h1>
            <p>Special Education Learning Management System</p>
            <p>Empowering educators, supporting learners, building futures.</p>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="auth-right">
        <div class="auth-form-container">
            <h2>Welcome Back</h2>
            <p class="subtitle">Login to your account</p>

            <!-- Session Timeout Alert -->
            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-clock-history"></i> Your session has expired. Please log in again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="<?php echo $basePath; ?>/login">
                <!-- CSRF Token -->
                <?php require_once __DIR__ . '/../../Helpers/CSRFHelper.php'; ?>
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(CSRFHelper::getToken()); ?>">

                <!-- Google Sign-In Button -->
                <a href="<?php echo $basePath; ?>/auth/google" class="btn btn-outline-secondary w-100 mb-3" style="border: 2px solid #ddd;">
                    <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="vertical-align: middle; margin-right: 8px;">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        <path fill="none" d="M0 0h48v48H0z"/>
                    </svg>
                    Sign in with Google
                </a>

                <div class="text-center my-3">
                    <span class="text-muted">OR</span>
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="email" name="email" placeholder="name@example.com or LRN" required autofocus>
                    <label for="email"><i class="bi bi-envelope"></i> Email Address or LRN</label>
                    <div class="form-text">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Learners can use their 12-digit LRN as username
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="bi bi-lock"></i> Password</label>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" 
                                style="z-index: 10; text-decoration: none;" 
                                onclick="togglePassword('password', 'togglePasswordIcon')">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-muted mb-0">Don't have an account?</p>
                    <a href="<?php echo $basePath; ?>/register" class="text-decoration-none fw-semibold">Register here</a>
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
