<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['studentID']) && !isset($_SESSION['doctorID'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$conn = db_connect();

// Mark all notifications as read
if (isset($_POST['mark_all']) && $_POST['mark_all'] === 'true') {
    $userID = $_POST['user_id'];
    $userType = $_POST['user_type'];
    
    $sql = "UPDATE notifications SET IsRead = TRUE WHERE UserID = ? AND UserType = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $userID, $userType);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update notifications']);
    }
    
    $stmt->close();
}
// Mark single notification as read
elseif (isset($_POST['notification_id'])) {
    $notificationId = intval($_POST['notification_id']);
    
    $sql = "UPDATE notifications SET IsRead = TRUE WHERE NotificationID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notificationId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update notification']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?>
