<?php
session_start();
require_once __DIR__ . '/config.php';

// Create a connection to deployed database
$conn = db_connect();

// Check if form data is received
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idOrEmail = $conn->real_escape_string($_POST['studentIdOrEmail']);
    $password = $_POST['password'];

    // First, try to log in as a student
    $studentSQL = "SELECT * FROM students WHERE StudentID = ? OR Email = ?";
    $stmt = $conn->prepare($studentSQL);
    $stmt->bind_param("ss", $idOrEmail, $idOrEmail);
    $stmt->execute();
    $studentResult = $stmt->get_result();

    if ($studentResult->num_rows === 1) {
        $user = $studentResult->fetch_assoc();
        if (password_verify($password, $user['PasswordHash'])) {
            $_SESSION['studentID'] = $user['StudentID'];
            $_SESSION['fullName'] = $user['FullName'];

            echo "<script>
                alert('Student Login successful! Redirecting...');
                window.location.href = 'homepagestudent.php';
            </script>";
            exit;
        } else {
            echo "<script>
                alert('Incorrect student password!');
                window.location.href = 'login-signup.html';
            </script>";
            exit;
        }
    }
    $stmt->close();

    // Then, try to log in as a doctor
    $doctorSQL = "SELECT * FROM doctors WHERE DoctorID = ? OR Email = ?";
    $stmt = $conn->prepare($doctorSQL);
    $stmt->bind_param("ss", $idOrEmail, $idOrEmail);
    $stmt->execute();
    $doctorResult = $stmt->get_result();

    if ($doctorResult->num_rows === 1) {
        $doctor = $doctorResult->fetch_assoc();
        if (password_verify($password, $doctor['PasswordHash'])) {
            $_SESSION['doctorID'] = $doctor['DoctorID'];
            $_SESSION['doctorName'] = $doctor['FullName'];

            echo "<script>
                alert('Doctor Login successful! Redirecting...');
                window.location.href = 'homepagedoctor.php';
            </script>";
            exit;
        } else {
            echo "<script>
                alert('Incorrect doctor password!');
                window.location.href = 'login-signup.html';
            </script>";
            exit;
        }
    }
    $stmt->close();

    // If no user found
    echo "<script>
        alert('No account found with provided ID or Email!');
        window.location.href = 'login-signup.html';
    </script>";
    exit;
}

$conn->close();
