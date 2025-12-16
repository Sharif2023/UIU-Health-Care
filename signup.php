<?php
require_once __DIR__ . '/config.php';

// Create a connection to deployed database
$conn = db_connect();

// Check if form data is received
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentID = $conn->real_escape_string($_POST['studentID']);
    $email = $conn->real_escape_string($_POST['email']);
    $fullName = $conn->real_escape_string($_POST['fullName']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "<script>
        alert('Error: Passwords do not match!'); 
        window.location.href = 'login-signup.html';
        </script>";
        exit;
    }

    // Check if Student ID or Email already exists
    $checkQuery = "SELECT * FROM students WHERE StudentID = '$studentID' OR Email = '$email'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        echo "<script>
        alert('Error: Student ID or Email already registered!'); 
        window.location.href = 'login-signup.html';
        </script>";
        exit;
    }

    // Hash the password before storing
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    $sql = "INSERT INTO students (StudentID, Email, FullName, Age, Gender, PasswordHash) 
            VALUES ('$studentID', '$email', '$fullName', $age, '$gender', '$passwordHash')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
    alert('Registration successful! Redirecting to login...'); 
    window.location.href = 'login-signup.html';
    </script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); 
    window.location.href = 'login-signup.html';
    </script>";
    }
}

// Close connection
$conn->close();
