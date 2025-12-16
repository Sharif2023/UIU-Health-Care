<?php
session_start();
require_once __DIR__ . '/config.php';

// Ensure the student is logged in
if (!isset($_SESSION['studentID'])) {
    echo json_encode(['error' => 'Student not logged in']);
    exit();
}

$blogID = $_POST['blogID'];
$studentID = $_POST['studentID'];

// Connect to the database
$conn = db_connect();

// Check if the student has already reacted to this blog
$stmt = $conn->prepare("SELECT ReactionID FROM blog_reactions WHERE BlogID = ? AND StudentID = ?");
$stmt->bind_param("ss", $blogID, $studentID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Reaction exists, so remove it
    $stmt = $conn->prepare("DELETE FROM blog_reactions WHERE BlogID = ? AND StudentID = ?");
    $stmt->bind_param("ss", $blogID, $studentID);
    $stmt->execute();
} else {
    // Reaction doesn't exist, so insert it
    $stmt = $conn->prepare("INSERT INTO blog_reactions (BlogID, StudentID, ReactionType) VALUES (?, ?, 'heart')");
    $stmt->bind_param("ss", $blogID, $studentID);
    $stmt->execute();
}

// Get the updated count of reactions for this blog
$countStmt = $conn->prepare("SELECT COUNT(*) AS reactionCount FROM blog_reactions WHERE BlogID = ?");
$countStmt->bind_param("s", $blogID);
$countStmt->execute();
$countResult = $countStmt->get_result();
$data = $countResult->fetch_assoc();

// Return the updated reaction count
echo json_encode(['reactionCount' => $data['reactionCount']]);

$stmt->close();
$countStmt->close();
$conn->close();
?>
