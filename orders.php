<?php
session_start();
require_once 'connection.php';

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch admin info
$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$admin_name = $admin['username'];

// Handle AJAX status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $order_id);
    echo json_encode($stmt->execute() ? ['success'=>true] : ['success'=>false,'error'=>$conn->error]);
    exit;
}

// Handle AJAX delete order
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id=?");
    $stmt->bind_param("i", $order_id);
    echo json_encode($stmt->execute() ? ['success'=>true] : ['success'=>false,'error'=>$conn->error]);
    exit;
}

// Fetch all orders with customer name
$orders = [];
$res1 = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
if($res1){
    while($row = $res1->fetch_assoc()) $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Orders - LaundryHand</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
/* --- Cute Pastel Theme --- */
body {
    background: linear-gradient(135deg, #a0e7e5 0%, #ffd6e0 50%, #cdb4db 100%);
    min-height: 100vh;
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

.page-title {
    text-align: center;
    margin-bottom: 30px;
    color: #ff80a0;
    font-weight: bold;
    font-size: 2rem;
}

.card-order {
    border-radius: 20px;
    background: #fff0f5;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    padding: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-order:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.12);
}
.card-order h6 { color: #ff80a0; }
.card-order p { margin-bottom: 5px; }

.btn-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 5px;
    margin-top: 10px;
}
.btn-status, .btn-delete {
    font-size: 0.7rem;
    padding: 0.25rem 0.3rem;
}

.badge { font-weight: 600; }
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
    <a class="navbar-brand" href="admindashboard.php"><i class="fas fa-tshirt me-2"></i>LaundryHand</a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="admindashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="view_customer.php">Customers</a></li>
        <li class="nav-item"><a class="nav-link" href="adminprofile.php">Profile</a></li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="page-title mb-0">📋 All Orders</h2>
    <a href="admindashboard.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back
    </a>
  </div>

  <div class="row">
    <?php if(empty($orders)): ?>
      <div class="col-12 text-center py-5">
        <h5 class="text-muted">No orders found.</h5>
      </div>
    <?php else: ?>
      <?php foreach($orders as $o): ?>
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="card card-order" id="order-<?= $o['id'] ?>">
          <h6>#<?= $o['id'] ?> (<?= htmlspecialchars($o['username']) ?>)</h6>
          <p><strong>Service:</strong> <?= htmlspecialchars($o['service']) ?></p>
          <p><strong>Weight:</strong> <?= htmlspecialchars($o['weight']) ?> kg ⚖️</p>
          <?php if (!empty($o['soap_qty']) && $o['soap_qty'] > 0): ?>
            <p><strong>Soap:</strong> <?= htmlspecialchars($o['soap_qty']) ?> 🧼</p>
          <?php endif; ?>
          <?php if (!empty($o['cond_qty']) && $o['cond_qty'] > 0): ?>
            <p><strong>Fabric Conditioner:</strong> <?= htmlspecialchars($o['cond_qty']) ?> 🧴</p>
          <?php endif; ?>
          <p><strong>Price:</strong> ₱<?= number_format($o['price'],2) ?></p>
          <p><strong>Status:</strong>
            <span class="badge <?= 
                $o['status']=="Pending" ? "bg-warning text-dark" : 
                ($o['status']=="In-progress" ? "btn btn-outline-primary bg-primary text-light" : 
                ($o['status']=="Ready to Pick Up" ? "bg-info text-dark" : "bg-success")) 
            ?>" id="status-<?= $o['id'] ?>"><?= htmlspecialchars($o['status']) ?></span>
          </p>
          <div class="btn-grid">
            <button class="btn btn-sm btn-outline-primary btn-status" onclick="updateStatus(<?= $o['id'] ?>,'In-progress')">In-progress</button>
            <button class="btn btn-sm btn-outline-info btn-status" onclick="updateStatus(<?= $o['id'] ?>,'Ready to Pick Up')">Ready</button>
            <button class="btn btn-sm btn-outline-success btn-status" onclick="updateStatus(<?= $o['id'] ?>,'Completed')">Completed</button>
            <button class="btn btn-sm btn-outline-danger btn-delete" onclick="deleteOrder(<?= $o['id'] ?>)">Delete</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

<!-- Logout Modal -->
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(orderId, newStatus){
    fetch('orders.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'update_status=1&order_id=' + orderId + '&status=' + encodeURIComponent(newStatus)
    }).then(res => res.json()).then(data => {
        if(data.success){
            const badge = document.getElementById('status-'+orderId);
            badge.textContent = newStatus;
            badge.className = 'badge ' + (
                newStatus==="Pending" ? "bg-warning text-dark" : 
                (newStatus==="In-progress" ? "btn btn-outline-primary bg-primary text-light" : 
                (newStatus==="Ready to Pick Up" ? "bg-info text-dark" : "bg-success"))
            );
        } else { alert('Failed to update status: '+data.error); }
    });
}

function deleteOrder(orderId){
    if(!confirm("Are you sure you want to delete this order?")) return;
    fetch('orders.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'delete_order=1&order_id=' + orderId
    }).then(res => res.json()).then(data => {
        if(data.success){ document.getElementById('order-'+orderId).remove(); }
        else { alert('Failed to delete order: '+data.error); }
    });
}
</script>
</body>
</html>
