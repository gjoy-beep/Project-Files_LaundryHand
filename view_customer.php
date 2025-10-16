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

// Fetch all registered customers with their order count
$customers = [];
$sql = "
    SELECT 
        u.id, 
        u.firstname, 
        u.lastname, 
        u.email,
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS total_orders
    FROM users u
    WHERE u.account_type = 'customer'
    ORDER BY u.id DESC
";
$result = $conn->query($sql);
if(!$result){
    die("Query failed: " . $conn->error);
}
while($row = $result->fetch_assoc()){
    $customers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registered Customers</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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

/* Cards and table container */
.table-container { 
    background: rgba(255,255,255,0.95); 
    border-radius:20px; 
    padding:2rem; 
    box-shadow:0 10px 30px rgba(0,0,0,0.1); 
    margin-top:2rem;
}

/* Table styling */
.table th, .table td {
    vertical-align: middle;
}
.btn-info {
    background: #ff80a0; 
    border: none;
}
.btn-info:hover { background: #ff5a82; }
.text-primary { color: #ff80a0 !important; }

/* Modal */
.modal-content {
    border-radius: 20px;
    backdrop-filter: blur(5px);
}
.modal-header { border-bottom: none; }
.modal-footer { border-top: none; }
</style>
</head>
<body>

<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="admindashboard.php"><i class="fas fa-tshirt me-2"></i>LaundryHand </a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="admindashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="view_customer.php">Customers</a></li>
        <li class="nav-item"><a class="nav-link" href="adminprofile.php">Profile</a></li>
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

<div class="container table-container">
    <h2 class="mb-4 text-primary"><i class="fas fa-users"></i> Registered Customers</h2>

    <?php if(empty($customers)): ?>
        <div class="alert alert-warning">No customers found.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Orders</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($customers as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['firstname']) ?></td>
                    <td><?= htmlspecialchars($c['lastname']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['total_orders']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick='viewDetails(<?= json_encode($c) ?>)'>
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Customer Details Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-primary" id="customerName"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="customerDetails"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function viewDetails(customer){
    document.getElementById('customerName').textContent = customer.firstname + ' ' + customer.lastname;
    document.getElementById('customerDetails').innerHTML = `
        <p><strong>ID:</strong> ${customer.id}</p>
        <p><strong>First Name:</strong> ${customer.firstname}</p>
        <p><strong>Last Name:</strong> ${customer.lastname}</p>
        <p><strong>Email:</strong> ${customer.email}</p>
        <p><strong>Orders:</strong> ${customer.total_orders}</p>
    `;
    var modal = new bootstrap.Modal(document.getElementById('customerModal'));
    modal.show();
}
</script>

</body>
</html>
