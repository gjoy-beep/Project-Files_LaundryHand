<?php
session_start();
require_once 'connection.php';

// Ensure user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST: Create new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $service = $_POST['serviceType'];
    $weight = floatval($_POST['estimatedWeight']);
    $soapQty = intval($_POST['soapQty']);
    $condQty = intval($_POST['condQty']);
    $instructions = trim($_POST['instructions']);
    $price = floatval($_POST['totalCost']);
    $status = "Pending";

    $stmt = $conn->prepare("INSERT INTO orders (user_id, service, weight, soap_qty, cond_qty, instructions, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiisds", $user_id, $service, $weight, $soapQty, $condQty, $instructions, $price, $status);
    $stmt->execute();
}

// Handle POST: Update order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $service = $_POST['serviceType'];
    $weight = floatval($_POST['estimatedWeight']);
    $soapQty = intval($_POST['soapQty']);
    $condQty = intval($_POST['condQty']);
    $instructions = trim($_POST['instructions']);
    $price = floatval($_POST['totalCost']);

    $stmt = $conn->prepare("UPDATE orders SET service=?, weight=?, soap_qty=?, cond_qty=?, instructions=?, price=? WHERE id=? AND user_id=?");
    $stmt->bind_param("siiisdii", $service, $weight, $soapQty, $condQty, $instructions, $price, $order_id, $user_id);
    $stmt->execute();
}

// Handle POST: Delete order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $order_id = intval($_POST['order_id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
}

// Fetch customer info
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['username'];
$avatar = $user['profile_pic'] ?? "https://play-lh.googleusercontent.com/ACxlXnn_o1AmwiiWj-Gs2TUdTx27asctBFC8PXOKSP8mIyOLgQmuuCLZ80tPetZnPIY";


// Fetch customer orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();
$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}

// Calculate totals
$totalOrders = count($orders);
$totalSpent = array_sum(array_column($orders, 'price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>LaundryHand - Customer Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* --- Cute Laundry Theme --- */
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
.bubble-5 { width: 125px; height: 125px; top: 30%; left: 50%; } 
.bubble-6 { width: 150px; height: 150px; top: 80%; left: 30%; }   

.navbar { background: linear-gradient(90deg, #a0e7e5 0%, #ffd6e0 100%); padding: 1rem 2rem; border-radius: 20px; margin: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.navbar-brand { font-weight: bold; font-size: 1.8rem; color: #5dade2 !important; display:flex; align-items:center; }
.navbar-brand i { margin-right: 8px; }
.navbar-nav .nav-link { font-size: 1.1rem; margin-left: 15px; color: #5dade2 !important; font-weight: 500; transition: all 0.3s ease; }
.navbar-nav .nav-link.active { font-weight: 600; color: #ff80a0 !important; }
.navbar-nav .nav-link:hover { color: #ff80a0 !important; }

.dashboard-card { background: #ffffffaa; border-radius: 25px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); padding: 40px 20px; text-align: center; }
.dashboard-card h1 { color: #ff80a0; }
.dashboard-card p { color: #555; }

.dashboard-card img {
    transition: transform 0.3s ease;
}
.dashboard-card img:hover {
    transform: scale(1.05);
}


.btn-primary { background: #ff80a0; color: #fff; border-radius: 15px; padding: 12px 30px; font-weight: 600; }
.btn-primary:hover { background: #ff5a82; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,128,160,0.3); }

.summary-card { border-radius: 20px; padding: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: center; font-weight: 600; }
.summary-card i { font-size: 2rem; margin-right: 10px; }
.stats-number { font-size: 1.8rem; font-weight: bold; margin-right: 5px; }
.stats-label { font-size: 0.95rem; color: #555; }

.orders-card { border-radius: 20px; background: #fff0f5; box-shadow: 0 8px 20px rgba(0,0,0,0.08); padding: 20px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.orders-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.12); }
.orders-card h6 { color: #ff80a0; }
.orders-card p { margin-bottom: 5px; }

.badge { font-weight: 600; }

.modal-header { background: #ffb3c6; color: black; border-top-left-radius: 7px; border-top-right-radius: 7px; }

.form-control { border-radius: 10px; }
</style>
</head>
<body>
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="customerdashboard.php">
      <i class="fas fa-tshirt me-2"></i> 
      <span>LaundryHand</span>
    </a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="customerdashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="customerprofile.php">Profile</a></li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

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

<div class="container mt-4">

    <!-- Dashboard -->
    <div class="dashboard-card mb-4">
    <img src="<?= htmlspecialchars($avatar) ?>" 
         alt="Profile Picture" 
         class="rounded-circle mb-3" 
         style="width:120px; height:120px; object-fit:cover; border:4px solid #ff80a0; background:#fff0f5;">
    <h1>Welcome back, <?= htmlspecialchars($username) ?>! 👕</h1>
    <p>Your laundry, your way ✨</p>
    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#orderModal">Create New Order 🧴</button>
</div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="summary-card bg-light text-primary">
                <i class="bi bi-basket-fill"></i>
                <div>
                    <div class="stats-number"><?= $totalOrders ?></div>
                    <div class="stats-label">Total Orders</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="summary-card bg-light text-success">
                <i class="bi bi-cash-stack"></i>
                <div>
                    <div class="stats-number">₱<?= number_format($totalSpent,2) ?></div>
                    <div class="stats-label">Total Spent</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders -->
    <div class="row">
        <?php if(empty($orders)): ?>
            <div class="col-12 text-center py-5">
                <h5 class="text-muted">No orders yet. Create your first order! 🧺</h5>
            </div>
        <?php else: ?>
            <?php foreach($orders as $i => $o): ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card orders-card">
                    <h6>Order #<?= $i+1 ?> 🧺</h6>
                    <p><strong>Service:</strong> <?= htmlspecialchars($o['service']) ?></p>
                    <p><strong>Weight:</strong> <?= htmlspecialchars($o['weight']) ?> kg ⚖️</p>
                    <p><strong>Soap:</strong> <?= htmlspecialchars($o['soap_qty']) ?> pcs 🧴</p>
                    <p><strong>Fabric Conditioner:</strong> <?= htmlspecialchars($o['cond_qty']) ?> pcs 🍃</p>
                    <p><strong>Price:</strong> ₱<?= number_format($o['price'],2) ?></p>
                    <p><strong>Status:</strong>
                        <span class="badge <?= $o['status']=="Pending"?"bg-warning text-dark":($o['status']=="Ready to Pick Up"?"bg-info text-dark":"bg-success") ?>">
                        <?= htmlspecialchars($o['status']) ?>
                        </span>
                    </p>
<?php 
    $isPending = $o['status'] === "Pending";
    $btnClass = "btn-sm w-100 btn-primary"; // keep UI same
    $btnStyle = $isPending ? "" : "background-color: gray; cursor:not-allowed;"; // gray + blocked cursor
    $btnAttr = $isPending ? "onclick='editOrder(".json_encode($o).")'" : "disabled"; // disable button
?>
<button class="<?= $btnClass ?>" style="<?= $btnStyle ?>" <?= $btnAttr ?>>Edit 🖊️</button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="">
    <div class="modal-header">
      <h5 class="modal-title">Create New Order 🧺</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="create_order" value="1">
        <div class="mb-3">
            <label class="form-label">Service Type 🧺</label>
            <select class="form-select" name="serviceType" id="serviceType" required>
              <option value="Wash Only" data-price="50">Wash Only (₱50/kg)</option>
              <option value="Wash & Fold" data-price="70">Wash & Fold (₱70/kg)</option>
              <option value="Wash, Iron & Fold" data-price="100">Wash, Iron & Fold (₱100/kg)</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Estimated Weight (kg) ⚖️</label>
            <input type="number" class="form-control" name="estimatedWeight" id="estimatedWeight" min="1" step="0.5" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Soap 🧴 (₱10 each)</label>
            <input type="number" class="form-control" name="soapQty" id="soapQty" value="0" min="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Fabric Conditioner 🍃 (₱15 each)</label>
            <input type="number" class="form-control" name="condQty" id="condQty" value="0" min="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Special Instructions ✨</label>
            <textarea class="form-control" name="instructions" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Total Cost 💰</label>
            <input type="text" class="form-control" name="totalCost" id="totalCost" readonly required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Place Order 🧺</button>
    </div>
    </form>
  </div></div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="">
    <div class="modal-header">
      <h5 class="modal-title">Edit Order 🖊️</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="update_order" value="1">
        <input type="hidden" name="order_id" id="editOrderId">
        <input type="hidden" name="delete_order" value="1">
        <input type="hidden" name="order_id" id="deleteOrderId">

        <div class="mb-3">
            <label class="form-label">Service Type 🧺</label>
            <select class="form-select" name="serviceType" id="editServiceType" required>
              <option value="Wash Only" data-price="50">Wash Only (₱50/kg)</option>
              <option value="Wash & Fold" data-price="70">Wash & Fold (₱70/kg)</option>
              <option value="Wash, Iron & Fold" data-price="100">Wash, Iron & Fold (₱100/kg)</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Estimated Weight (kg) ⚖️</label>
            <input type="number" class="form-control" name="estimatedWeight" id="editEstimatedWeight" min="1" step="0.5" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Soap 🧴 (₱10 each)</label>
            <input type="number" class="form-control" name="soapQty" id="editSoapQty" value="0" min="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Fabric Conditioner 🍃 (₱15 each)</label>
            <input type="number" class="form-control" name="condQty" id="editCondQty" value="0" min="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Special Instructions ✨</label>
            <textarea class="form-control" name="instructions" id="editInstructions" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Total Cost 💰</label>
            <input type="text" class="form-control" name="totalCost" id="editTotalCost" readonly required>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-2 justify-content-between">
            <button type="submit" name="update_order" class="btn btn-primary flex-grow-1" style="padding: 6px 12px; font-size: 0.9rem;">Update</button>
            <button type="submit" name="delete_order" class="btn btn-danger flex-grow-1" style="padding: 6px 12px; font-size: 0.9rem;" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
        </div>

    </div>
    </form>
  </div></div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
// --- Calculate Total for Create ---
function calculateTotal() {
    const service = document.getElementById("serviceType");
    const weight = parseFloat(document.getElementById("estimatedWeight").value) || 0;
    const soap = parseInt(document.getElementById("soapQty").value) || 0;
    const cond = parseInt(document.getElementById("condQty").value) || 0;
    const pricePerKg = parseFloat(service.selectedOptions[0].dataset.price);
    const total = (pricePerKg * weight) + (soap * 10) + (cond * 15);
    document.getElementById("totalCost").value = total.toFixed(2);
}
document.getElementById("serviceType").addEventListener("change", calculateTotal);
document.getElementById("estimatedWeight").addEventListener("input", calculateTotal);
document.getElementById("soapQty").addEventListener("input", calculateTotal);
document.getElementById("condQty").addEventListener("input", calculateTotal);

// --- Edit Order Modal ---
function editOrder(order) {
    const modal = new bootstrap.Modal(document.getElementById("editOrderModal"));
    document.getElementById("editOrderId").value = order.id;
    document.getElementById("deleteOrderId").value = order.id; // set delete ID
    document.getElementById("editServiceType").value = order.service;
    document.getElementById("editEstimatedWeight").value = order.weight;
    document.getElementById("editSoapQty").value = order.soap_qty;
    document.getElementById("editCondQty").value = order.cond_qty;
    document.getElementById("editInstructions").value = order.instructions;

    const pricePerKg = parseFloat(document.getElementById("editServiceType").selectedOptions[0].dataset.price);
    const total = (pricePerKg * order.weight) + (order.soap_qty * 10) + (order.cond_qty * 15);
    document.getElementById("editTotalCost").value = total.toFixed(2);

    modal.show();
}

document.getElementById("editServiceType").addEventListener("change", function() {
    const weight = parseFloat(document.getElementById("editEstimatedWeight").value) || 0;
    const soap = parseInt(document.getElementById("editSoapQty").value) || 0;
    const cond = parseInt(document.getElementById("editCondQty").value) || 0;
    const pricePerKg = parseFloat(this.selectedOptions[0].dataset.price);
    const total = (pricePerKg * weight) + (soap * 10) + (cond * 15);
    document.getElementById("editTotalCost").value = total.toFixed(2);
});
document.getElementById("editEstimatedWeight").addEventListener("input", function() {
    document.getElementById("editServiceType").dispatchEvent(new Event("change"));
});
document.getElementById("editSoapQty").addEventListener("input", function() {
    document.getElementById("editServiceType").dispatchEvent(new Event("change"));
});
document.getElementById("editCondQty").addEventListener("input", function() {
    document.getElementById("editServiceType").dispatchEvent(new Event("change"));
});
</script>
</body>
</html>

