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

// Get and sanitize parameters
$type = $_GET['type'] ?? 'medicine';
$type = ($type === 'test') ? 'test' : 'medicine';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$itemsPerPage = 15;
$offset = ($page - 1) * $itemsPerPage;

// Prepare search term
$searchTerm = "%" . $search . "%";

// Query and fetch data
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
  if ($type === 'medicine') {
    $items[] = [
      'name' => $row['medicine_name'],
      'description' => "Generic: " . $row['generic_name'] . "<br>Strength: " . $row['strength'],
      'price' => $row['price']
    ];
  } else {
    $items[] = [
      'name' => $row['test_name'],
      'description' => "Normal Range: " . $row['normal_range'] . "<br>Details: " . $row['details'],
      'price' => $row['cost_in_tk']
    ];
  }
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
