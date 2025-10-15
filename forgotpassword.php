<?php
session_start();
$message = $_SESSION['forgot_message'] ?? '';
$success = $_SESSION['forgot_success'] ?? false;
unset($_SESSION['forgot_message'], $_SESSION['forgot_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LaundryHand - Forgot Password</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #a0e7e5 0%, #ffd6e0 50%, #cdb4db 100%);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
    font-family: 'Poppins', sans-serif;
    color: #0f172a;
}
.bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    animation: float 6s ease-in-out infinite;
}
.bubble:nth-child(odd) { animation-delay: -2s; }
.bubble:nth-child(even) { animation-delay: -4s; }
@keyframes float { 0%,100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
.bubble-1 { width: 80px; height: 80px; top: 20%; left: 10%; }
.bubble-2 { width: 120px; height: 120px; top: 60%; left: 5%; }
.bubble-3 { width: 60px; height: 60px; top: 40%; right: 15%; }
.bubble-4 { width: 100px; height: 100px; top: 70%; right: 20%; }

.navbar { 
    background: linear-gradient(90deg, #a0e7e5 0%, #ffd6e0 100%);
    padding: 1rem 2rem;
    border-radius: 20px;
    margin: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.navbar-brand { 
    font-weight: bold; 
    font-size: 1.8rem; 
    color: #5dade2 !important; 
    display:flex; 
    align-items:center; 
}
.navbar-brand i { margin-right: 8px; }
.navbar-nav .nav-link { 
    font-size: 1.1rem;
    margin-left: 15px; 
    color: #5dade2 !important; 
    font-weight: 500;
    transition: all 0.3s ease;
}
.navbar-nav .nav-link.active { font-weight: 600; color: #ff80a0 !important; }
.navbar-nav .nav-link:hover { color: #ff80a0 !important; }

.form-container {
    background: rgba(255,255,255,0.95);
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    padding: 2.5rem;
    max-width: 450px;
    margin: 100px auto;
    text-align: center;
    position: relative;
    z-index: 10;
}
.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    transition: all 0.3s ease;
}
.form-control:focus {
    border-color: #ff80a0;
    box-shadow: 0 0 0 0.2rem rgba(255,128,160,0.25);
}
.btn-primary {
    background: #ff80a0;
    border: none;
    border-radius: 10px;
    padding: 12px 30px;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background: #ff4d80;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255,128,160,0.3);
}
.form-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 8px;
}
.alert i { margin-right: 5px; }
</style>
</head>
<body>
<!-- Floating Bubbles -->
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-tshirt me-2"></i>LaundryHand
        </a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <li class="nav-item"><a class="nav-link active" href="login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Forgot Password Form -->
<div class="container">
    <div class="form-container">
        <div class="text-center mb-4">
            <i class="fas fa-key fa-3x text-primary mb-3"></i>
            <h2 class="fw-bold text-primary">Forgot Password?</h2>
            <p class="text-muted">Enter your email and we'll send you a reset link.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <i class="fas <?= $success ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Only show form if not a success message (or if limited by 7-day rule) -->
        <?php if (!$success || strpos($message, 'once per week') !== false): ?>
            <form method="POST" action="forgot_password_handler.php">
                <div class="mb-4 text-start">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="Enter your registered email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center">
            <a href="login.php" class="text-primary text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
