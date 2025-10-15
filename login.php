<?php
session_start();
require_once 'connection.php';

$errorMessage = "";

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $accountType = $_POST['account_type'];

    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND account_type = ?");
    $stmt->bind_param("ss", $email, $accountType);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Login success, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['account_type'] = $user['account_type'];

            if ($accountType === 'admin') {
                header("Location: admindashboard.php");
            } else {
                header("Location: customerdashboard.php");
            }
            exit;
        } else {
            $errorMessage = "Invalid password!";
        }
    } else {
        $errorMessage = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LaundryHand - Login</title>
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
    @keyframes float {
        0%, 100% { transform: translateY(0px);}
        50% { transform: translateY(-20px);}
    }
    .bubble-1 { width: 80px; height: 80px; top: 20%; left: 10%; }
    .bubble-2 { width: 120px; height: 120px; top: 60%; left: 5%; }
    .bubble-3 { width: 60px; height: 60px; top: 40%; right: 15%; }
    .bubble-4 { width: 100px; height: 100px; top: 70%; right: 20%; }

    .navbar { 
    background: rgba(255,255,255,0.4); 
    backdrop-filter: blur(12px); 
    border-radius: 20px; 
    margin: 10px 20px; 
}
.navbar-brand { 
    font-weight: bold; 
    font-size: 1.5rem; 
    color: #85c1ff !important; 
    display:flex; 
    align-items:center; 
}
.navbar-brand i { margin-right: 8px; }
.navbar-nav .nav-link { color: #85c1ff !important; margin:0 10px; transition: all 0.3s; }
.navbar-nav .nav-link.active { font-weight: bold; color: #ff8fab !important; }
.navbar-nav .nav-link:hover { background: rgba(255,255,255,0.2); border-radius:8px; }

    .form-container {
        background: rgba(255,255,255,0.95);
        border-radius: 25px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        padding: 2.5rem;
        max-width: 450px;
        margin: 50px auto;
        position: relative;
        z-index: 10;
        text-align: center;
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
    .form-label { font-weight: 600; color: #555; margin-bottom: 8px; }
    .error-message { color: #ff4d80; margin-bottom: 15px; font-weight: 500; }
    .options { font-size: 0.9rem; }
</style>
</head>
<body>
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

<div class="container">
    <div class="form-container p-5">
        <h2 class="text-center fw-bold mb-4" style="color: #85c1ff;"><i class="fas fa-sign-in-alt me-2"></i>Login</h2>

        <?php if($errorMessage): ?>
            <div class="error-message"><i class="fas fa-exclamation-triangle me-1"></i><?= $errorMessage ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label d-flex align-items-center">
                    <i class="fas fa-envelope me-2"></i>
                    <span>Email</span>
                </label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter email" required>
            </div>

            <div class="mb-3 position-relative">
                <label for="password" class="form-label d-flex align-items-center">
                    <i class="fas fa-lock me-2"></i>
                    <span>Password</span>
                </label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4 text-start">
                <label for="account_type" class="form-label"><i class="fas fa-users me-2"></i>Account Type</label>
                <select class="form-select" id="account_type" name="account_type">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 options">
                <label class="remember mb-0">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="forgotpassword.php" style="color: #85c1ff;"><i class="fas fa-key me-1"></i>Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary mb-3"><i class="fas fa-paper-plane me-1"></i>Login</button>
            <p class="text-center">Don't have an account? <a href="register.php" style="color: #85c1ff;"><i class="fas fa-user-plus me-1"></i>Register</a></p>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    const togglePassword = document.querySelector("#togglePassword");
    const passwordInput = document.querySelector("#password");

    togglePassword.addEventListener("click", function () {
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);

        // Toggle the eye icon
        this.querySelector("i").classList.toggle("fa-eye");
        this.querySelector("i").classList.toggle("fa-eye-slash");
    });
</script>
</body>
</html>
