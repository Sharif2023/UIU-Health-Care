<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiu_healthcare";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

$searchTerm = isset($_GET['query']) ? strtolower($conn->real_escape_string($_GET['query'])) : "";
$age = isset($_GET['age']) ? intval($_GET['age']) : null;
$weight = isset($_GET['weight']) ? floatval($_GET['weight']) : null;

if (empty($searchTerm) || $age === null || $weight === null) {
    echo json_encode(["error" => "Missing search term, age, or weight."]);
    exit;
}

// Find nearest lower age & weight
$ageQuery = "SELECT MAX(age) as closest_age FROM medical_diagnoses WHERE age <= $age";
$weightQuery = "SELECT MAX(weight) as closest_weight FROM medical_diagnoses WHERE weight <= $weight";

$ageResult = $conn->query($ageQuery);
$weightResult = $conn->query($weightQuery);

$closestAge = $ageResult->fetch_assoc()['closest_age'] ?? $age;
$closestWeight = $weightResult->fetch_assoc()['closest_weight'] ?? $weight;

// Final query (case-insensitive match + fallback age/weight logic)
$sql = "
SELECT * FROM medical_diagnoses
WHERE (LOWER(disease) LIKE '%$searchTerm%' OR LOWER(symptoms) LIKE '%$searchTerm%')
AND age = $closestAge AND weight = $closestWeight
LIMIT 1
";

$result = $conn->query($sql);
$diseases = [];

while ($row = $result->fetch_assoc()) {
    $diseases[] = $row;
}

echo json_encode($diseases);
$conn->close();
?>
