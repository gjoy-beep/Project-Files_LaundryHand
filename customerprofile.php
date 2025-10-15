<?php
session_start();
require_once 'connection.php';

// Ensure customer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['user_id'];

// Handle profile update (username + email)
if (isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("ssi", $new_username, $new_email, $customer_id);
    if ($stmt->execute()) {
        $_SESSION['alert'] = "Profile updated successfully!";
        $_SESSION['alert_type'] = "success";
    } else {
        $_SESSION['alert'] = "Update failed: " . $stmt->error;
        $_SESSION['alert_type'] = "error";
    }
    header("Location: customerprofile.php");
    exit;
}

// Handle profile picture upload
if (isset($_POST['upload_pic']) && isset($_FILES['profile_pic'])) {
    $stmt = $conn->prepare("SELECT last_profile_update FROM users WHERE id=?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $canUpload = true;
    $remaining = 0;

    if ($user['last_profile_update'] !== null) {
        $lastUpdate = new DateTime($user['last_profile_update']);
        $now = new DateTime();
        $diff = $now->diff($lastUpdate);

        if ($diff->days < 7) {
            $canUpload = false;
            $remaining = 7 - $diff->days;
        }
    }

    if ($canUpload) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);

        $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
            $sql = "UPDATE users SET profile_pic=?, last_profile_update=NOW() WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $targetFile, $customer_id);
            $stmt->execute();

            $_SESSION['alert'] = "Profile picture updated successfully!";
            $_SESSION['alert_type'] = "success";
        } else {
            $_SESSION['alert'] = "Error uploading file.";
            $_SESSION['alert_type'] = "error";
        }
    } else {
        $_SESSION['alert'] = "You can only upload a new profile picture once a week. Please wait $remaining more day(s).";
        $_SESSION['alert_type'] = "warning";
    }

    header("Location: customerprofile.php");
    exit;
}

// Fetch customer info
$stmt = $conn->prepare("SELECT username, email, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$customer_name = $customer['username'];
$customer_email = $customer['email'];
$avatar = $customer['profile_pic'] ?? "https://play-lh.googleusercontent.com/ACxlXnn_o1AmwiiWj-Gs2TUdTx27asctBFC8PXOKSP8mIyOLgQmuuCLZ80tPetZnPIY";

$alertMessage = $_SESSION['alert'] ?? '';
$alertType = $_SESSION['alert_type'] ?? '';
unset($_SESSION['alert'], $_SESSION['alert_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Profile - LaundryHand</title>
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
    background: rgba(255, 255, 255, 0.2);
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
    max-width: 450px;
    margin: 40px auto;
    padding: 40px 30px;
    text-align: center;
    position: relative;
    z-index: 10;
}
.profile-avatar {
    width: 120px;
    height: 120px;
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

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="customerdashboard.php">
      <i class="fas fa-tshirt me-2"></i> 
      <span>LaundryHand</span>
    </a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="customerdashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="customerprofile.php">Profile</a></li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirm Logout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center"><p class="mb-0">Are you sure you want to log out?</p></div>
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
    <img src="<?= htmlspecialchars($avatar) ?>" alt="Customer Avatar" class="profile-avatar">
    <div class="profile-info">
      <h3><?= htmlspecialchars($customer_name) ?></h3>
      <p><?= htmlspecialchars($customer_email) ?></p>
      <p><span class="badge bg-success">Customer</span></p>
    </div>

    <!-- Upload Profile Picture Form -->
    <form method="POST" enctype="multipart/form-data" class="mt-3">
      <div class="input-group">
        <input type="file" class="form-control" name="profile_pic" accept="image/*" required>
        <button type="submit" name="upload_pic" class="btn btn-primary">
          <i class="fas fa-upload"></i> Upload
        </button>
      </div>
    </form>

    <button class="btn edit-btn" onclick="toggleForm()" style="background-color: #85c1ff; border-color: #85c1ff;">
      <i class="fas fa-edit"></i> Edit Profile
    </button>

    <form class="profile-form" method="POST" action="">
      <label>Username</label>
      <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($customer_name) ?>" required>
      <label>Email</label>
      <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($customer_email) ?>" required>
      
      <div class="d-flex justify-content-center gap-2 mt-3">
  <button type="submit" name="update_profile" class="btn btn-success">
    <i class="fas fa-save"></i> Save Changes
  </button>
  <button type="button" class="btn btn-outline-secondary" onclick="toggleForm()">
    <i class="fas fa-times"></i> Cancel
  </button>
</div>

      </div>
    </form>
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
