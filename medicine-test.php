<?php
header('Content-Type: application/json');

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiu_healthcare";

// DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die(json_encode(['error' => 'Database connection failed']));
}

// Get parameters
$type = $_GET['type'] ?? 'medicine'; // default to 'medicine'
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$itemsPerPage = 15;
$offset = ($page - 1) * $itemsPerPage;

// Search term
$searchTerm = "%" . $search . "%";

// Query based on type
if ($type === 'medicine') {
  $stmt = $conn->prepare("SELECT * FROM medicines WHERE medicine_name LIKE ? LIMIT ?, ?");
} else {
  $stmt = $conn->prepare("SELECT * FROM tests WHERE test_name LIKE ? LIMIT ?, ?");
}
$stmt->bind_param("sii", $searchTerm, $offset, $itemsPerPage);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
  $items[] = [
    'name' => $type === 'medicine' ? $row['medicine_name'] : $row['test_name'],
    "description" => "Generic: " . $row["generic_name"] . "<br>Strength: " . $row["strength"],
    'price' => $row['price']
  ];
}

// Get total count for pagination
if ($type === 'medicine') {
  $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM medicines WHERE medicine_name LIKE ?");
} else {
  $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM tests WHERE test_name LIKE ?");
}
$countStmt->bind_param("s", $searchTerm);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Return JSON
echo json_encode([
  'items' => $items,
  'pages' => $totalPages
]);

$conn->close();
?>
