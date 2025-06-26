<?php
// Ensure the request is via GET and contains a valid blog ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Invalid blog ID']);
    exit();
}

$blogID = $_GET['id'];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "uiu_healthcare");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query the blog details based on the blogID
$stmt = $conn->prepare("SELECT * FROM blogs WHERE BlogID = ?");
$stmt->bind_param("s", $blogID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the blog data
    $blog = $result->fetch_assoc();
    
    // Return the blog details as JSON
    echo json_encode([
        'title' => $blog['Title'],
        'content' => $blog['Content'],
        'image' => $blog['Image'],
        'createdAt' => $blog['CreatedAt']
    ]);
} else {
    echo json_encode(['error' => 'Blog not found']);
}

$stmt->close();
$conn->close();
?>
