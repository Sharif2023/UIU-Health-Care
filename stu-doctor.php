<?php
session_start();
require_once __DIR__ . '/config.php';

// If not logged in, redirect to login
if (!isset($_SESSION['studentID'])) {
    header('Location: login-signup.html');
    exit();
}

// Get student's ID and Name from session
$studentID = $_SESSION['studentID'];
$fullName = $_SESSION['fullName'];

// Connect to deployed database
$conn = db_connect();

// Fetch student profile to get ProfilePicture
$sql = "SELECT d.ProfilePicture
        FROM students s
        LEFT JOIN student_details d ON s.StudentID = d.StudentID
        WHERE s.StudentID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$stmt->close();

// Fetch doctor profile based on a specific doctor ID (replace with dynamic value if necessary)
$doctorID = 'D001'; // Example: Replace with actual DoctorID from session or URL
$sql_doctor = "SELECT 
                    DoctorID, FullName, Designation, Department, Mobile, Email, ProfilePicture, Bio 
               FROM doctors 
               WHERE DoctorID = ?";
$stmt_doctor = $conn->prepare($sql_doctor);
$stmt_doctor->bind_param("s", $doctorID);
$stmt_doctor->execute();
$result_doctor = $stmt_doctor->get_result();
$doctor = $result_doctor->fetch_assoc();

// If doctor is not found (optional)
if (!$doctor) {
    die("Doctor profile not found.");
}

$stmt_doctor->close();
$conn->close();

// Set the profile picture for student and doctor
$dropdownProfilePicture = $student['ProfilePicture'] ?? 'https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg';
$doctorProfilePicture = $doctor['ProfilePicture'] ?? 'https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Find Doctor - UIU HealthCare</title>

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

        .text-center.mt-4 {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .doc-pro-btn {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            transition: background-color 0.3s ease;
            min-width: 180px;
            text-align: center;
        }

        .doc-pro-btn:hover {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .doc-pro-btn {
                min-width: 150px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
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
                    <li><a href="stu-doctor.php" class="active">Doctor</a></li>
                    <li>
                    <li><a href="nearby-hospitals.php">Hospitals</a></li>
                    </li>
                    <li><a href="stu-medicine-test.php">Medicine & Test</a></li>
                    <li><a href="stu-blogs.php">Blogs</a></li>
                    <li><a href="stu-about.php">About</a></li>
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
                        <h5>Shariful Islam</h5>
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
                    <a href="login-signup.html">
                        <div class="logout-btn">
                            <i class="bi bi-box-arrow-right"></i> Log out
                        </div>
                    </a>
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

        <div class="container doc-pro-container">
            <div class="row">
                <!-- Sidebar with profile image -->
                <div class="col-md-4 doc-pro-sidebar">
                    <img src="<?php echo htmlspecialchars($doctorProfilePicture); ?>" alt="Profile Picture" class="doc-pro-profile-img">
                    <div class="doc-pro-name"><?php echo htmlspecialchars($doctor['FullName']); ?></div>
                    <div class="doc-pro-email"><?php echo htmlspecialchars($doctor['Email']); ?></div>
                    <div class="doc-pro-id">ID: <?php echo htmlspecialchars($doctor['DoctorID']); ?></div>
                </div>

                <!-- Profile Info Section -->
                <div class="col-md-8">
                    <h4 class="doc-pro-info-title">Profile Details</h4>
                    <div class="doc-pro-info-item">
                        <span class="doc-pro-label">Full Name:</span>
                        <span class="doc-pro-value"><?php echo htmlspecialchars($doctor['FullName']); ?></span>
                    </div>

                    <div class="doc-pro-info-item">
                        <span class="doc-pro-label">Designation:</span>
                        <span class="doc-pro-value"><?php echo htmlspecialchars($doctor['Designation']); ?></span>
                    </div>

                    <div class="doc-pro-info-item">
                        <span class="doc-pro-label">Department:</span>
                        <span class="doc-pro-value"><?php echo htmlspecialchars($doctor['Department']); ?></span>
                    </div>

                    <div class="doc-pro-info-item">
                        <span class="doc-pro-label">Mobile Number:</span>
                        <span class="doc-pro-value"><?php echo htmlspecialchars($doctor['Mobile']); ?></span>
                    </div>

                    <div class="doc-pro-info-item">
                        <span class="doc-pro-label">Bio:</span>
                        <span class="doc-pro-value"><?php echo htmlspecialchars($doctor['Bio']); ?></span>
                    </div>

                    <!-- Add Action Buttons -->
                    <div class="text-center mt-4">
                        <!-- Buttons for the student to interact with the doctor -->
                        <a href="stu-book-appointments.php" class="btn btn-primary doc-pro-btn">
                            <i class="bi bi-calendar-check"></i> Book Appointment
                        </a>
                        <a href="message-doctor.php" class="btn btn-secondary doc-pro-btn">
                            <i class="bi bi-chat"></i> Message
                        </a>
                        <a href="video-consultation.php" class="btn btn-info doc-pro-btn">
                            <i class="bi bi-camera-video"></i> Video Consultation
                        </a>
                        <a href="share-medical-issue.php" class="btn btn-warning doc-pro-btn">
                            <i class="bi bi-file-earmark-medical"></i> Share Medical Issue/Reports
                        </a>
                        <a href="stu-prescription.php" class="btn btn-success doc-pro-btn">
                            <i class="bi bi-file-earmark-medical"></i> Prescription
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