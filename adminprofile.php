<?php
session_start();
require_once 'connection.php';

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $admin_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("ssi", $new_username, $new_email, $admin_id);
    if ($stmt->execute()) {
        $_SESSION['alert'] = "Profile updated successfully!";
        $_SESSION['alert_type'] = "success";
    } else {
        $_SESSION['alert'] = "Update failed: " . $stmt->error;
        $_SESSION['alert_type'] = "error";
    }
    header("Location: adminprofile.php");
    exit;
}

// Fetch admin info
$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$admin_name = $admin['username'];
$admin_email = $admin['email'];

// Default avatar
$avatar = "https://play-lh.googleusercontent.com/ACxlXnn_o1AmwiiWj-Gs2TUdTx27asctBFC8PXOKSP8mIyOLgQmuuCLZ80tPetZnPIY";
$alertMessage = $_SESSION['alert'] ?? '';
$alertType = $_SESSION['alert_type'] ?? '';
unset($_SESSION['alert'], $_SESSION['alert_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Profile - LaundryHand</title>
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
@keyframes float { 0%,100% { transform: translateY(0px);} 50% { transform: translateY(-20px);} }
.bubble-1 { width: 80px; height: 80px; top: 20%; left: 10%; }
.bubble-2 { width: 120px; height: 120px; top: 60%; left: 5%; }
.bubble-3 { width: 60px; height: 60px; top: 40%; right: 15%; }
.bubble-4 { width: 100px; height: 100px; top: 70%; right: 20%; }
.bubble-5 { width: 125px; height: 125px; top: 30%; left: 50%; } 
.bubble-6 { width: 150px; height: 150px; top: 80%; left: 30%; }   

/* Navbar */
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

/* Profile Card */
.profile-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    max-width: 400px;
    margin: 40px auto;
    padding: 40px 30px;
    text-align: center;
    position: relative;
    z-index: 10;
}
.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
    border: 4px solid #ff80a0;
    background: #fff0f5;
}
.profile-info h3 { margin-bottom: 10px; color: #ff80a0; }
.profile-info p { margin-bottom: 5px; color: #555; }
.edit-btn { margin-top: 20px; }

/* Form inside card */
.profile-form { display: none; margin-top: 15px; text-align: left; }
.profile-form label { font-weight: 500; }
.profile-form input { margin-bottom: 10px; }
.alert { margin-bottom: 15px; }
</style>
</head>
<body>
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>
<div class="bubble bubble-5"></div>
<div class="bubble bubble-6"></div>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="admindashboard.php"><i class="fas fa-tshirt me-2"></i>LaundryHand </a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="admindashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="view_customer.php">Customers</a></li>
                <li class="nav-item"><a class="nav-link active" href="adminprofile.php">Profile</a></li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
            Logout
          </a>
      </ul>
    </div>
  </div>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Logout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-0">Are you sure you want to log out?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="index.php" class="btn btn-danger">Yes, Logout</a>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <?php if($alertMessage): ?>
      <div class="alert alert-<?= $alertType ?>"><?= htmlspecialchars($alertMessage) ?></div>
  <?php endif; ?>

  <div class="profile-card">
    <img src="<?= $avatar ?>" alt="Admin Avatar" class="profile-avatar">
    <div class="profile-info">
      <h3 id="adminName"><?= htmlspecialchars($admin_name) ?></h3>
      <p id="adminEmail"><?= htmlspecialchars($admin_email) ?></p>
<p><span class="badge" style="background-color: #85c1ff; color: #fff;">Admin</span></p>
    </div>
<button class="btn edit-btn" onclick="toggleForm()" style="background-color: #85c1ff; border-color: #85c1ff; color: #ffffff;">
    <i class="fas fa-edit"></i> Edit Profile
</button>


    <form class="profile-form" method="POST" action="">
      <label>Username</label>
      <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($admin_name) ?>" required>
      <label>Email</label>
      <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin_email) ?>" required>
      <div class="d-flex justify-content-center gap-2 mt-3">
  <button type="submit" name="update_profile" class="btn btn-success">
    <i class="fas fa-save"></i> Save Changes
  </button>
  <button type="button" class="btn btn-outline-secondary" onclick="toggleForm()">
    <i class="fas fa-times"></i> Cancel
  </button>
</div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function toggleForm() {
    const form = document.querySelector('.profile-form');
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>