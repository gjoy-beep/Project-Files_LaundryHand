<?php
session_start();
require_once 'connection.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmpassword = trim($_POST['confirmpassword']);
    $accountType = $_POST['accountType'];

    // Password validation
    if (!preg_match('/^(?=.*[0-9])(?=.*[\W_]).{6,}$/', $password)) {
        $errors[] = "Password must be at least 6 characters, contain at least 1 number and 1 special character.";
    }

    // Confirm password check
    if ($password !== $confirmpassword) {
        $errors[] = "Passwords do not match.";
    }

    // Email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        // Restrict email domains
        if (!preg_match('/@(gmail\.com|edu\.com)$/i', $email)) {
            $errors[] = "Email must end with @gmail.com or @edu.com.";
        }
    }

    // Check if email or username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email or Username already exists.";
    }

    // If no errors, insert new user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, username, password, account_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $firstname, $lastname, $email, $username, $hashedPassword, $accountType);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LaundryHand - Register</title>
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
.bubble { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.2); animation: float 6s ease-in-out infinite; }
.bubble:nth-child(odd) { animation-delay: -2s; }
.bubble:nth-child(even) { animation-delay: -4s; }
@keyframes float { 0%,100% { transform: translateY(0px);} 50% { transform: translateY(-20px);} }
.bubble-1 { width: 80px; height: 80px; top: 20%; left: 10%; }
.bubble-2 { width: 120px; height: 120px; top: 60%; left: 5%; }
.bubble-3 { width: 60px; height: 60px; top: 40%; right: 15%; }
.bubble-4 { width: 100px; height: 100px; top: 70%; right: 20%; }

.navbar { background: rgba(255,255,255,0.4); backdrop-filter: blur(12px); border-radius: 20px; margin: 10px 20px; }
.navbar-brand { font-weight: bold; font-size: 1.5rem; color: #85c1ff !important; display:flex; align-items:center; }
.navbar-brand i { margin-right: 8px; }
.navbar-nav .nav-link { color: #85c1ff !important; margin:0 10px; transition: all 0.3s; }
.navbar-nav .nav-link.active { font-weight: bold; color: #ff8fab !important; }
.navbar-nav .nav-link:hover { background: rgba(255,255,255,0.2); border-radius:8px; }

.form-container {
    background: rgba(255,255,255,0.95);
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    padding: 2.5rem;
    max-width: 500px;
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
.btn-primary:disabled {
    background: gray !important;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}
.form-label { font-weight: 600; color: #555; margin-bottom: 8px; }
.error-message { color: #ff4d80; margin-bottom: 10px; font-weight: 500; font-size: 0.9rem; }
.success-message { color: green; margin-bottom: 10px; font-weight: 500; font-size: 0.9rem; }
.input-group-text { background: transparent; border-left: none; cursor: pointer; }
.input-group .form-control { border-right: none; }
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
                <li class="nav-item"><a class="nav-link" href="index.php"> Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="register.php"> Register</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php"> Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="form-container p-5">
        <h2 class="text-center fw-bold mb-4" style="color: #85c1ff;"><i class="fas fa-user-plus me-2"></i>Create Account</h2>

        <?php if (!empty($errors)): ?>
            <script>alert('<?php echo implode("\\n", $errors); ?>');</script>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm">
            <div class="mb-3">
                <label for="firstname" class="form-label d-flex align-items-center"><i class="fas fa-user me-2"></i>Firstname</label>
                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Enter firstname" required>
            </div>
            <div class="mb-3">
                <label for="lastname" class="form-label d-flex align-items-center"><i class="fas fa-user me-2"></i>Lastname</label>
                <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Enter lastname" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label d-flex align-items-center"><i class="fas fa-envelope me-2"></i>Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required>
                <small id="emailError" class="error-message" style="display:none;">Email must end with @gmail.com or @edu.com</small>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label d-flex align-items-center"><i class="fas fa-user-circle me-2"></i>Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label d-flex align-items-center"><i class="fas fa-lock me-2"></i>Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                    <span class="input-group-text" onclick="togglePassword('password', this)"><i class="fas fa-eye"></i></span>
                </div>
                <div id="passwordErrors" class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="confirmpassword" class="form-label d-flex align-items-center"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                <div class="input-group">
                    <input type="password" id="confirmpassword" name="confirmpassword" class="form-control" placeholder="Confirm password" required>
                    <span class="input-group-text" onclick="togglePassword('confirmpassword', this)"><i class="fas fa-eye"></i></span>
                </div>
                <div id="matchMessage" class="error-message"></div>
            </div>
            <div class="mb-4">
                <label for="accountType" class="form-label d-flex align-items-center"><i class="fas fa-users me-2"></i>Account Type</label>
                <select class="form-select" id="accountType" name="accountType">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-3" id="submitBtn" disabled><i class="fas fa-user-plus me-1"></i>Register</button>
            <p class="text-center">Already have an account? <a href="login.php" style="color: #85c1ff;"><i class="fas fa-sign-in-alt me-1"></i>Login</a></p>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId, el) {
    const field = document.getElementById(fieldId);
    const icon = el.querySelector("i");
    field.type = field.type === "password" ? "text" : "password";
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");
}

const email = document.getElementById("email");
const emailError = document.getElementById("emailError");
const passwordField = document.getElementById("password");
const confirmField = document.getElementById("confirmpassword");
const passwordErrorsDiv = document.getElementById("passwordErrors");
const matchMessageDiv = document.getElementById("matchMessage");
const submitBtn = document.getElementById("submitBtn");

function validateEmail() {
    const value = email.value.trim();
    const valid = value.endsWith("@gmail.com") || value.endsWith("@edu.com");
    emailError.style.display = valid || value === "" ? "none" : "block";
    toggleSubmit();
}

function validatePasswords() {
    const val = passwordField.value;
    const confirmVal = confirmField.value;
    let errors = [];

    if (val.length < 6) errors.push("Password must be at least 6 characters.");
    if (!/[0-9]/.test(val)) errors.push("At least 1 number required.");
    if (!/[\W_]/.test(val)) errors.push("At least 1 special character required.");

    passwordErrorsDiv.innerHTML = errors.join('<br>');

    if (confirmVal.length > 0) {
        if (val === confirmVal) {
            matchMessageDiv.innerHTML = '<span class="success-message">✅ Passwords match</span>';
        } else {
            matchMessageDiv.innerHTML = '❌ Passwords do not match.';
        }
    } else {
        matchMessageDiv.innerHTML = '';
    }

    toggleSubmit();
}

function toggleSubmit() {
    const emailValid = email.value.endsWith("@gmail.com") || email.value.endsWith("@edu.com");
    const passValid = passwordErrorsDiv.innerHTML === "";
    const match = passwordField.value === confirmField.value && passwordField.value !== "";
    const enable = emailValid && passValid && match;
    submitBtn.disabled = !enable;
    submitBtn.style.backgroundColor = enable ? "#ff80a0" : "gray";
}

email.addEventListener("input", validateEmail);
passwordField.addEventListener("input", validatePasswords);
confirmField.addEventListener("input", validatePasswords);
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
