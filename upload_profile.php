<?php
session_start();
include 'db.php'; // connection file

$userId = $_SESSION['user_id']; // logged-in user ID

// Fetch last profile update time
$sql = "SELECT last_profile_update FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$canUpload = true;
$message = "";

if ($user['last_profile_update'] !== null) {
    $lastUpdate = new DateTime($user['last_profile_update']);
    $now = new DateTime();
    $diff = $now->diff($lastUpdate);

    if ($diff->days < 7) {
        $canUpload = false;
        $remaining = 7 - $diff->days;
        $message = "❌ You can only update your profile picture once a week. Please wait $remaining more day(s).";
    }
}

if ($canUpload && isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] === 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
    $targetFile = $targetDir . $fileName;

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES["profile_pic"]["type"], $allowedTypes)) {
        $message = "❌ Only JPG, PNG, and GIF files are allowed.";
    } elseif (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
        // Update DB
        $sql = "UPDATE users SET profile_pic = ?, last_profile_update = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $targetFile, $userId);
        $stmt->execute();

        $message = "✅ Profile picture updated successfully!";
    } else {
        $message = "❌ Error uploading file.";
    }
}

$_SESSION['alert'] = $message;
header("Location: profile.php");
exit();
?>
