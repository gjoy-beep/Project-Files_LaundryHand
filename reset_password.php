<?php
require_once 'connection.php';
session_start();

$expired = false;
$user = null;

// ✅ Check token validity
if (!isset($_GET['token'])) {
    $expired = true;
} else {
    $token = $_GET['token'];

    $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $expired = true;
    } else {
        $user = $result->fetch_assoc();
    }
}

// ✅ Handle password reset
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$expired) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // ✅ Update password, clear token and expiry
        // ✅ Update password_updated_at to NOW()
        $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL, last_reset_request = NOW(), password_updated_at = NOW() WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user['id']);
        $update_stmt->execute();

        echo "<script>alert('Password successfully reset! You can now log in.'); window.location.href='login.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LaundryHand - Reset Password</title>
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
    animation: fadeIn 0.8s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
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

/* 🥺 Expired message styles */
.sad-face {
    font-size: 80px;
    animation: bounce 1.5s infinite;
}
@keyframes bounce {
    0%,100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.expired-text {
    color: #ff4d80;
    font-weight: 600;
}
</style>
</head>
<body>
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>

<div class="form-container">
    <?php if ($expired): ?>
        <div class="sad-face mb-3">🥺</div>
        <h3 class="expired-text mb-2">Link Expired</h3>
        <p class="text-muted">Oops! This password reset link is no longer valid.<br>Please request a new one.</p>
        <a href="forgotpassword.php" class="btn btn-primary mt-3">Request New Link</a>
    <?php else: ?>
        <h3 class="mb-3" style="color:#ff4d80;">Reset Your Password</h3>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
