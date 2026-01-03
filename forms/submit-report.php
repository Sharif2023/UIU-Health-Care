<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['studentID']) && !isset($_SESSION['doctorID'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Validate required fields
if (!isset($_POST['user_id']) || !isset($_POST['user_type']) || !isset($_POST['category']) || 
    !isset($_POST['subject']) || !isset($_POST['description'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$conn = db_connect();

$userID = $_POST['user_id'];
$userType = $_POST['user_type'];
$category = $_POST['category'];
$priority = $_POST['priority'] ?? 'Medium';
$subject = $_POST['subject'];
$description = $_POST['description'];
$screenshot = null;

// Handle file upload
if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/reports/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileExtension = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($fileExtension, $allowedExtensions)) {
        // Check file size (max 5MB)
        if ($_FILES['screenshot']['size'] <= 5 * 1024 * 1024) {
            $fileName = $userID . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $filePath)) {
                $screenshot = 'uploads/reports/' . $fileName;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'File size exceeds 5MB limit']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
        exit();
    }
}

// Insert into database
$sql = "INSERT INTO problem_reports (UserID, UserType, Category, Priority, Subject, Description, Screenshot) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $userID, $userType, $category, $priority, $subject, $description, $screenshot);

if ($stmt->execute()) {
    $reportID = $conn->insert_id();
    
    // Create notification for admins (optional - if you have admin notification system)
    // You can add admin notification logic here
    
    echo json_encode([
        'success' => true,
        'message' => 'Report submitted successfully',
        'report_id' => $reportID
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to submit report: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
