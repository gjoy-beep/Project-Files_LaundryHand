<?php
session_start();
require_once 'connection.php'; // database connection

// Assume user is logged in and user ID is stored in session
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

// Check POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = $_POST['service'] ?? '';
    $weight = $_POST['weight'] ?? 0;
    $soapQty = $_POST['soapQty'] ?? 0;
    $condQty = $_POST['condQty'] ?? 0;
    $instructions = $_POST['instructions'] ?? '';
    $price = $_POST['price'] ?? 0;

    $stmt = $conn->prepare("INSERT INTO orders (user_id, service, weight, soap_qty, cond_qty, instructions, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isiiisd", $user_id, $service, $weight, $soapQty, $condQty, $instructions, $price);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Order placed successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>