<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['studentID'])) {
    header('Location: login-signup.html');
    exit();
}

// Get student's ID and Name from session
$studentID = $_SESSION['studentID'];
$fullName = $_SESSION['fullName'];

// Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiu_healthcare";

$conn = new mysqli($servername, $username, $password, $dbname);
// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student profile from database
$sql = "SELECT 
            s.StudentID, s.Email, s.FullName, s.Age, s.Gender, 
            d.Mobile, d.Height, d.BloodGroup, d.Address, d.EmergencyContact, d.ProfilePicture
        FROM students s
        LEFT JOIN student_details d ON s.StudentID = d.StudentID
        WHERE s.StudentID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Close connection
$stmt->close();
$conn->close();

$dropdownProfilePicture = $student['ProfilePicture'] ?? '';

if (empty($dropdownProfilePicture)) {
    $dropdownProfilePicture = "https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Homepage - UIU HealthCare</title>

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

    <!-- Main CSS -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        /* Unique Style for Profile Page */
        .stu-pro-body {
            background-color: #ccc;
            font-family: 'Poppins', sans-serif;
        }

        .stu-pro-container {
            background: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            margin-top: 60px;
            margin-bottom: 60px;
            border-radius: 15px;
        }

        /* Sidebar style */
        .stu-pro-sidebar {
            background: #E9F5EE;
            padding: 30px 20px;
            border-radius: 15px;
            text-align: center;
        }

        .stu-pro-profile-img {
            object-fit: cover;
            height: 140px;
            width: 140px;
            border-radius: 50%;
            border: 4px solid #4caf50;
            margin-bottom: 15px;
        }

        .stu-pro-name {
            font-size: 26px;
            font-weight: 700;
            color: #333;
        }

        .stu-pro-id,
        .stu-pro-email {
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        }

        /* Profile Info Title */
        .stu-pro-info-title {
            font-size: 24px;
            font-weight: 700;
            color: #4caf50;
            border-bottom: 2px solid #4caf50;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        /* Info item style */
        .stu-pro-info-item {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 16px;
        }

        .stu-pro-label {
            font-weight: 600;
            color: #555;
            width: 180px;
        }

        .stu-pro-value {
            color: #333;
            flex-grow: 1;
        }

        /* Edit button style */
        .stu-pro-edit-btn {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 30px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 30px;
            transition: background-color 0.3s ease;
        }

        .stu-pro-edit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body class="stu-pro-body">
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-l position-relative d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <img src="assets/img/header-logo.png" alt="header-logo">
                <h1 class="sitename">UIU<span> HealthCare</span></h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="homepagestudent.php">Home</a></li>
                    <li><a href="#services">Appointments</a></li>
                    <li><a href="#doctor">Doctor</a></li>
                    <li><a href="#blog-n-tips">Hospitals</a></li>
                    <li><a href="medicine-test.html">Medicine & Test</a></li>
                    <li><a href="#blog">Blog</a></li>
                    <li><a href="about.php">About</a></li>
                    <li onclick="openDiagnoseNav()"><img src="assets/img/diagnose-bot.png" height="40px" width="40px"
                            alt="Diagnosis-tool"></li>

                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <!-- Profile Dropdown -->
            <div class="profile-container">
                <div class="profile-btn" id="profileButton">
                    <img src="<?php echo htmlspecialchars($dropdownProfilePicture); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($studentID); ?></span>
                    <i class="bi bi-caret-down-fill"></i>
                </div>
                <div class="dropdown-menu" id="profileDropdown">
                    <div class="dropdown-header">
                        <img src="<?php echo htmlspecialchars($dropdownProfilePicture); ?>" alt="User Avatar">
                        <h5><?php echo htmlspecialchars($fullName); ?></h5>
                        <small>ID: <?php echo htmlspecialchars($studentID); ?></small>
                    </div>
                    <a href="student-profile.php">
                        <div class="dropdown-item">
                            <i class="bi bi-person"></i> My Profile
                        </div>
                    </a>
                    <div class="dropdown-item">
                        <i class="bi bi-bell"></i> Notification
                    </div>
                    <div class="dropdown-item">
                        <i class="bi bi-question-circle"></i> Help Center
                    </div>
                    <div class="dropdown-item">
                        <i class="bi bi-gear"></i> Settings and Privacy
                    </div>
                    <div class="dropdown-item">
                        <i class="bi bi-exclamation-triangle"></i> Report a problem
                    </div>
                    <div class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i> Log out
                    </div>
                </div>
            </div>

        </div>
    </header>
    <main class="main">
        <!--Code of DiagnoseBot-->
        <div class="diagnosebot-nav-modal" id="diagnosisNav">
            <button class="bot-close-btn" onclick="closeDiagnoseNav()">&times;</button>
            <h2>Diagnosis Tool</h2>
            <input type="text" id="searchInput" placeholder="Enter symptom or disease (e.g., fever, cough)"
                onkeyup="showSuggestions()" class="botInput">
            <input type="number" id="ageInput" placeholder="Enter your age..." class="botInput">
            <input type="number" id="weightInput" placeholder="Enter your weight (kg)..." class="botInput">

            <select id="botGenderInput">
                <option value="">Select Gender...</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>

            <div class="suggestions" id="suggestionsBox"></div>
            <button onclick="getDiagnosis()" class="botButtonProperty">Diagnose</button>
            <div class="result" id="diagnosisResult"></div>
            <br><button id="solutionBtn" onclick="showSolution()" style="display:none;" class="botButtonProperty">First
                Aid</button><br>
            <div class="solution" id="solutionText"></div>
        </div>

        <!--Code have to Starts from Here and id/class name should be unique according to page name-->
        <div class="container stu-pro-container">
            <div class="row">
                <!-- Left Sidebar -->
                <div class="col-md-4 stu-pro-sidebar text-center">
                    <?php
                    // Set default image if no profile picture found
                    $profilePicture = $student['ProfilePicture'] ?? '';

                    if (empty($profilePicture)) {
                        $profilePicture = "https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg";
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" class="rounded-circle stu-pro-profile-img">
                    <div class="stu-pro-name"><?php echo htmlspecialchars($student['FullName'] ?? 'Not Provided'); ?></div>
                    <div class="stu-pro-email"><?php echo htmlspecialchars($student['Email'] ?? 'Not Provided'); ?></div>
                    <div class="stu-pro-id">ID: <?php echo htmlspecialchars($student['StudentID'] ?? ''); ?></div>

                </div>

                <!-- Right Content -->
                <div class="col-md-8">
                    <h4 class="stu-pro-info-title">Profile Details</h4>
                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Full Name:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['FullName'] ?? ''); ?></span>
                    </div>

                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Mobile Number:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['Mobile'] ?? ''); ?></span>
                    </div>
                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Height:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['Height'] ?? ''); ?></span>
                    </div>
                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Age:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['Age'] ?? ''); ?></span>
                    </div>
                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Gender:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['Gender'] ?? ''); ?></span>
                    </div>
                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Blood Group:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['BloodGroup'] ?? ''); ?></span>
                    </div>
                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Address:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['Address'] ?? ''); ?></span>
                    </div>
                    <div class="stu-pro-info-item">
                        <span class="stu-pro-label">Emergency Contact:</span>
                        <span class="stu-pro-value"><?php echo htmlspecialchars($student['EmergencyContact'] ?? 'Not Provided'); ?></span>
                    </div>

                    <div class="text-center mt-4">
                        <a href="edit-profile.php" class="btn btn-success stu-pro-edit-btn">
                            <i class="bi bi-pencil-square"></i> Edit Profile
                        </a>
                    </div>
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
                <a href=""><i class="bi bi-twitter-x"></i></a>
                <a href=""><i class="bi bi-facebook"></i></a>
                <a href=""><i class="bi bi-instagram"></i></a>
                <a href=""><i class="bi bi-linkedin"></i></a>
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
    <!--Javascript-->
    <script src="assets/js/student.js"></script>

</body>

</html>