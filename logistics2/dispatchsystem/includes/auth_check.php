<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connections/auth/login.php');
    exit();
}

$user_id = (string)$_SESSION['user_id'];
$isAdmin = false;

try {
    $stmt = $conn->prepare("SELECT 1 FROM admin WHERE firebase_uid = ? LIMIT 1");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $isAdmin = true;
    $stmt->close();
} catch (Exception $e) {
    error_log("Admin check failed: " . $e->getMessage());
}
