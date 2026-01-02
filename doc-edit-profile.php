<?php
session_start();
require_once __DIR__ . '/config.php';

// If not logged in, redirect to login
if (!isset($_SESSION['doctorID'])) {
    header('Location: login-signup.html');
    exit();
}

// Get doctor's ID from session
$doctorID = $_SESSION['doctorID'];

// Connect to deployed database
$conn = db_connect();

// Fetch doctor details from database
$sql = "SELECT * FROM doctors WHERE DoctorID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $doctorID);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// Check if form is submitted for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $mobile = $_POST['mobile'];
    $bio = $_POST['bio'];
    $profilePicture = $doctor['ProfilePicture']; // Keep existing picture unless updated

    // Handle Profile Picture Upload
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        $fileName = uniqid() . "_" . basename($_FILES['profilePicture']['name']);
        $uploadFilePath = $uploadDir . $fileName;

        // Move the uploaded file to the server
        if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $uploadFilePath)) {
            // File uploaded successfully, update profile picture path
            $profilePicture = $uploadFilePath;
        } else {
            echo "Error uploading file.";
        }
    }

    // Update doctor details in the database
    $updateSql = "UPDATE doctors SET FullName=?, Email=?, Designation=?, Department=?, Mobile=?, Bio=?, ProfilePicture=? WHERE DoctorID=?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssssss", $fullName, $email, $designation, $department, $mobile, $bio, $profilePicture, $doctorID);
    $updateStmt->execute();
    $updateStmt->close();

    // Redirect to profile page
    header("Location: doctor-profile.php");
    exit();
}

// Close the initial connection after fetching the doctor details
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile Edit - UIU HealthCare</title>

    <!-- Favicons -->
    <link href="assets/img/title.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        .doc-pro-body {
            background-color: #f4f7fa;
            font-family: 'Poppins', sans-serif;
        }

        .doc-pro-container {
            background: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            margin-top: 60px;
            margin-bottom: 60px;
            border-radius: 15px;
        }

        .doc-pro-sidebar {
            background: #e9f5ee;
            padding: 30px 20px;
            border-radius: 15px;
            text-align: center;
        }

        .doc-pro-profile-img {
            object-fit: cover;
            height: 140px;
            width: 140px;
            border-radius: 50%;
            border: 4px solid #4caf50;
            margin-bottom: 15px;
        }

        .doc-pro-name {
            font-size: 26px;
            font-weight: 700;
            color: #333;
        }

        .doc-pro-id,
        .doc-pro-email {
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        }

        .doc-pro-info-title {
            font-size: 24px;
            font-weight: 700;
            color: #4caf50;
            border-bottom: 2px solid #4caf50;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .doc-pro-info-item {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 16px;
        }

        .doc-pro-label {
            font-weight: 600;
            color: #555;
            width: 180px;
        }

        .doc-pro-value {
            color: #333;
            flex-grow: 1;
        }

        .doc-pro-edit-btn {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 30px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 30px;
            transition: background-color 0.3s ease;
        }

        .doc-pro-edit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body class="doc-pro-body">
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-l position-relative d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <img src="assets/img/header-logo.png" alt="header-logo">
                <h1 class="sitename">UIU<span> HealthCare</span></h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero">Home</a></li>
                    <li><a href="doc-appointments.php">Appointments</a></li>
                    <li><a href="patient">Patients</a></li>
                    <li><a href="doc-medicine-test.php">Medicine & Test</a></li>
                    <li><a href="doc-blogs.php">Blogs</a></li>
                    <li><a href="#forum">Forum</a></li>
                    <li><a href="doc-about.php">About</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <!-- Profile Dropdown -->
            <div class="profile-container">
                <div class="profile-btn" id="profileButton">
                    <img src="assets/img/doctor.png" alt="User Avatar">
                    <span>Dr. Shamima Akter</span>
                    <i class="bi bi-caret-down-fill"></i>
                </div>
                <div class="dropdown-menu" id="profileDropdown">
                    <div class="dropdown-header">
                        <img src="assets/img/doctor.png" alt="User Avatar">
                        <h5>Dr. Shamima Akter</h5>
                        <small>Senior Medical Officer, UIU Medical Centre</small>
                    </div>
                    <a href="doctor-profile.php">
                        <div class="dropdown-item">
                            <i class="bi bi-person"></i> My Profile
                        </div>
                    </a>
                    <div class="dropdown-item">
                        <i class="bi bi-exclamation-triangle"></i> Emergency
                    </div>
                    <a href="doc-notifications.php">
                        <div class="dropdown-item">
                            <i class="bi bi-bell"></i> Notification
                        </div>
                    </a>
                    <a href="help-center.php">
                        <div class="dropdown-item">
                            <i class="bi bi-question-circle"></i> Help Center
                        </div>
                    </a>
                    <a href="doc-settings.php">
                        <div class="dropdown-item">
                            <i class="bi bi-gear"></i> Settings and Privacy
                        </div>
                    </a>
                    <a href="report-problem.php">
                        <div class="dropdown-item">
                            <i class="bi bi-exclamation-triangle"></i> Report a problem
                        </div>
                    </a>
                    <a href="forms/logout.php">
                        <div class="logout-btn">
                            <i class="bi bi-box-arrow-right"></i> Log out
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </header>
    <main class="main">
        <div class="container doc-pro-container">
            <div class="row">
                <div class="col-md-4 doc-pro-sidebar">
                    <form method="POST" enctype="multipart/form-data">
                        <img src="<?php echo htmlspecialchars($doctor['ProfilePicture'] ?? ''); ?>" alt="Profile Picture" class="doc-pro-profile-img" id="profilePicturePreview">
                        <input type="file" name="profilePicture" id="profilePictureUpload" accept="image/*">
                        <div class="doc-pro-name"><?php echo htmlspecialchars($doctor['FullName']); ?></div>
                        <div class="doc-pro-email"><?php echo htmlspecialchars($doctor['Email']); ?></div>
                    </form>
                </div>

                <div class="col-md-8">
                    <form method="POST" enctype="multipart/form-data">
                        <h4 class="doc-pro-info-title">Edit Profile</h4>
                        <div class="doc-pro-info-item">
                            <label for="fullName" class="doc-pro-label">Full Name</label>
                            <input type="text" name="fullName" id="fullName" class="form-control" value="<?php echo htmlspecialchars($doctor['FullName']); ?>" required>
                        </div>
                        <div class="doc-pro-info-item">
                            <label for="email" class="doc-pro-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($doctor['Email']); ?>" required>
                        </div>
                        <div class="doc-pro-info-item">
                            <label for="designation" class="doc-pro-label">Designation</label>
                            <input type="text" name="designation" id="designation" class="form-control" value="<?php echo htmlspecialchars($doctor['Designation']); ?>" required>
                        </div>
                        <div class="doc-pro-info-item">
                            <label for="department" class="doc-pro-label">Department</label>
                            <input type="text" name="department" id="department" class="form-control" value="<?php echo htmlspecialchars($doctor['Department']); ?>" required>
                        </div>
                        <div class="doc-pro-info-item">
                            <label for="mobile" class="doc-pro-label">Mobile</label>
                            <input type="text" name="mobile" id="mobile" class="form-control" value="<?php echo htmlspecialchars($doctor['Mobile']); ?>" required>
                        </div>
                        <div class="doc-pro-info-item">
                            <label for="bio" class="doc-pro-label">Bio</label>
                            <textarea name="bio" id="bio" class="form-control" rows="5"><?php echo htmlspecialchars($doctor['Bio']); ?></textarea>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer id="footer" class="footer light-background">

        <div class="container">
            <div class="copyright text-center ">
                <p>© <span>Copyright</span> <strong class="px-1 sitename">UIU HealthCare</strong> <span>All Rights
                        Reserved</span></p>
            </div>
            <div class="social-links d-flex justify-content-center">
                <a href="https://twitter.com/UIU_BD" target="_blank"><i class="bi bi-twitter-x"></i></a>
                <a href="https://www.facebook.com/uiu.ac.bd" target="_blank"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/uiu_bd/" target="_blank"><i class="bi bi-instagram"></i></a>
                <a href="https://www.linkedin.com/school/uiu-bd/" target="_blank"><i class="bi bi-linkedin"></i></a>
            </div>
            <div class="credits">
                <!-- All the links in the footer should remain intact. -->
                <!-- You can delete the links only if you've purchased the pro version. -->
                <!-- Licensing information: https://bootstrapmade.com/license/ -->
                <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
                Designed by <a href="https://github.com/Sharif2023">Shariful Islam</a>
            </div>
        </div>

    </footer>
    <script src="assets/js/student.js"></script>

</body>

</html>