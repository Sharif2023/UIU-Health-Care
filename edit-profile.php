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

// Fetch email from database
$servername = "localhost"; // Change if your servername is different
$username = "root"; // Your DB username
$password = ""; // Your DB password
$database = "uiu_healthcare"; // Your DB name

// Create DB connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the student's email
$email = "Not Provided"; // Default value if not found
$sql = "SELECT email FROM students WHERE studentID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$stmt->bind_result($fetchedEmail);

if ($stmt->fetch()) {
    $email = $fetchedEmail;
}

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile - UIU HealthCare</title>

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
        /* Container */
        .profile-page-wrapper {
            background-color: #f5f7fa;
            min-height: 100vh;
            padding: 40px;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Sidebar */
        .profile-sidebar {
            background: #e9f5ee;
            text-align: center;
            padding: 30px 20px;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
        }

        .profile-upload-btn {
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
            cursor: pointer;
            color: #28a745;
        }

        .profile-name {
            font-size: 22px;
            font-weight: bold;
            margin-top: 10px;
        }

        .profile-email,
        .profile-studentid {
            font-size: 14px;
            color: #6c757d;
        }

        /* Form */
        .profile-form-section {
            padding: 30px;
        }

        .profile-form-title {
            font-weight: 700;
            color: #28a745;
            margin-bottom: 20px;
        }

        .profile-input-label {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .profile-input-field {
            background-color: #f1f1f1;
        }

        .profile-input-field:focus {
            background-color: #ffffff;
            border-color: #28a745;
            box-shadow: none;
        }

        .profile-save-btn {
            margin-top: 30px;
            padding: 10px 30px;
            font-weight: bold;
            border-radius: 25px;
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
                    <li><a href="homepagestudent.php" class="active">Home</a></li>
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
                    <img src="assets/img/me.jpg" alt="User Avatar">
                    <span><?php echo htmlspecialchars($studentID); ?></span>
                    <i class="bi bi-caret-down-fill"></i>
                </div>
                <div class="dropdown-menu" id="profileDropdown">
                    <div class="dropdown-header">
                        <img src="assets/img/me.jpg" alt="User Avatar">
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
        <section class="profile-page-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 profile-card">
                        <div class="row g-0">

                            <!-- Sidebar -->
                            <div class="col-md-4 profile-sidebar">
                                <img src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg"
                                    alt="Profile Picture" class="profile-avatar" id="profilePicturePreview">
                                <br>
                                <label for="profilePictureUpload" class="profile-upload-btn">Change Photo</label>
                                <input type="file" id="profilePictureUpload" accept="image/*" class="d-none">

                                <div class="profile-name"><?php echo htmlspecialchars($fullName); ?></div>
                                <div class="profile-email">Email: <?php echo htmlspecialchars($email); ?></div>
                                <div class="profile-studentid">ID: <?php echo htmlspecialchars($studentID); ?></div>
                            </div>

                            <!-- Form Section -->
                            <div class="col-md-8 profile-form-section">
                                <h4 class="profile-form-title">Edit Profile</h4>
                                <form>
                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="profile-input-label">Full Name</label>
                                            <input type="text" class="form-control profile-input-field"
                                                value="<?php echo htmlspecialchars($fullName); ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="profile-input-label">Mobile Number</label>
                                            <input type="text" class="form-control profile-input-field"
                                                placeholder="Mobile Number">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="profile-input-label">Height (ft.)</label>
                                            <input type="text" class="form-control profile-input-field"
                                                placeholder="Height">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="profile-input-label">Age</label>
                                            <input type="number" class="form-control profile-input-field"
                                                value="25">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="profile-input-label">Blood Group</label>
                                            <select class="form-control profile-input-field">
                                                <option disabled selected>Select Blood Group</option>
                                                <option>A+</option>
                                                <option>A-</option>
                                                <option>B+</option>
                                                <option>B-</option>
                                                <option>O+</option>
                                                <option>O-</option>
                                                <option>AB+</option>
                                                <option>AB-</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="profile-input-label">Address</label>
                                        <input type="text" class="form-control profile-input-field"
                                            placeholder="Enter Address">
                                    </div>

                                    <div class="mb-3">
                                        <label class="profile-input-label">Emergency Contact</label>
                                        <input type="text" class="form-control profile-input-field"
                                            placeholder="Emergency Contact Number">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="profile-input-label">Email</label>
                                            <input type="email" class="form-control profile-input-field"
                                                value="<?php echo htmlspecialchars($email); ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="profile-input-label">Student ID</label>
                                            <input type="text" class="form-control profile-input-field"
                                                value="<?php echo htmlspecialchars($studentID); ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success profile-save-btn">Save
                                            Changes</button>
                                    </div>

                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- JS for changing profile picture -->
        <script>
            const profilePictureUpload = document.getElementById('profilePictureUpload');
            const profilePicturePreview = document.getElementById('profilePicturePreview');

            profilePictureUpload.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    profilePicturePreview.src = URL.createObjectURL(file);
                }
            });
        </script>
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