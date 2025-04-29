<?php
//for keep student logged in
session_start();

// Create a connection
$conn = new mysqli("localhost", "root", "", "uiu_healthcare");

// Check the connection
if ($conn->connect_error) {
    die("<script>
    alert('Database connection failed!'); 
    window.location.href = 'login-signup.html';
    </script>");
}

// Check if form data is received
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentIdOrEmail = $conn->real_escape_string($_POST['studentIdOrEmail']);
    $password = $_POST['password'];

    // Fetch the user based on StudentID or Email
    $sql = "SELECT * FROM students WHERE StudentID = '$studentIdOrEmail' OR Email = '$studentIdOrEmail'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['PasswordHash'])) {

            $_SESSION['studentID'] = $user['StudentID'];
            $_SESSION['fullName'] = $user['FullName'];
            // Login successful
            echo "<script>
            alert('Login successful! Redirecting to homepage...');
            window.location.href = 'homepagestudent.php';
            </script>";
            exit;
        } else {
            echo "<script>
            alert('Error: Incorrect password!');
            window.location.href = 'login-signup.html';
            </script>";
            exit;
        }
    } else {
        echo "<script>
        alert('Error: No account found with that ID or Email!');
        window.location.href = 'login-signup.html';
        </script>";
        exit;
    }
}

// Close connection
$conn->close();
?>
