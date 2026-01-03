<?php
session_start();
require_once __DIR__ . '/config.php';

// Determine if user is student or doctor
$isStudent = isset($_SESSION['studentID']);
$isDoctor = isset($_SESSION['doctorID']);

if (!$isStudent && !$isDoctor) {
    header('Location: login-signup.html');
    exit();
}

$userID = $isStudent ? $_SESSION['studentID'] : $_SESSION['doctorID'];
$fullName = $_SESSION['fullName'];
$userType = $isStudent ? 'student' : 'doctor';

// Connect to database
$conn = db_connect();

// Fetch profile picture
if ($isStudent) {
    $sql = "SELECT d.ProfilePicture FROM students s 
            LEFT JOIN student_details d ON s.StudentID = d.StudentID 
            WHERE s.StudentID = ?";
} else {
    $sql = "SELECT d.ProfilePicture FROM doctors doc 
            LEFT JOIN doctor_details d ON doc.DoctorID = d.DoctorID 
            WHERE doc.DoctorID = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$dropdownProfilePicture = $user['ProfilePicture'] ?? '';
if (empty($dropdownProfilePicture)) {
    $dropdownProfilePicture = "https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report a Problem - UIU HealthCare</title>

    <!-- Favicons -->
    <link href="assets/img/title.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Main CSS Files -->
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/report-problem.css" rel="stylesheet">
    <link href="assets/css/responsive-mobile.css" rel="stylesheet">
</head>

<body>
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-l position-relative d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="assets/img/header-logo.png" alt="header-logo">
                <h1 class="sitename">UIU<span> HealthCare</span></h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="<?php echo $isStudent ? 'homepagestudent.php' : 'homepagedoctor.php'; ?>">Home</a></li>
                    <?php if ($isStudent): ?>
                        <li><a href="stu-doctor.php">Doctor</a></li>
                        <li><a href="nearby-hospitals.php">Hospitals</a></li>
                        <li><a href="stu-medicine-test.php">Medicine & Test</a></li>
                        <li><a href="stu-blogs.php">Blogs</a></li>
                        <li><a href="stu-about.php">About</a></li>
                    <?php else: ?>
                        <li><a href="doc-appointments.php">Appointments</a></li>
                        <li><a href="doc-medicine-test.php">Medicine & Test</a></li>
                        <li><a href="doc-blogs.php">Blogs</a></li>
                        <li><a href="doc-about.php">About</a></li>
                    <?php endif; ?>
                    <?php if ($isStudent): ?>
                        <li onclick="openDiagnoseNav()"><img src="assets/img/diagnose-bot.png" height="40px" width="40px" alt="Diagnosis-tool"></li>
                    <?php endif; ?>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <!-- Profile Dropdown -->
            <div class="profile-container">
                <div class="profile-btn" id="profileButton">
                    <img src="<?php echo htmlspecialchars($dropdownProfilePicture); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($userID); ?></span>
                    <i class="bi bi-caret-down-fill"></i>
                </div>
                <div class="dropdown-menu" id="profileDropdown">
                    <div class="dropdown-header">
                        <img src="<?php echo htmlspecialchars($dropdownProfilePicture); ?>" alt="User Avatar">
                        <h5><?php echo htmlspecialchars($fullName); ?></h5>
                        <small>ID: <?php echo htmlspecialchars($userID); ?></small>
                    </div>
                    <a href="<?php echo $isStudent ? 'student-profile.php' : 'doctor-profile.php'; ?>">
                        <div class="dropdown-item">
                            <i class="bi bi-person"></i> My Profile
                        </div>
                    </a>
                    <a href="<?php echo $isStudent ? 'stu-notifications.php' : 'doc-notifications.php'; ?>">
                        <div class="dropdown-item">
                            <i class="bi bi-bell"></i> Notification
                        </div>
                    </a>
                    <a href="help-center.php">
                        <div class="dropdown-item">
                            <i class="bi bi-question-circle"></i> Help Center
                        </div>
                    </a>
                    <a href="<?php echo $isStudent ? 'stu-settings.php' : 'doc-settings.php'; ?>">
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
        <div class="report-container">
            <div class="report-header">
                <h1><i class="bi bi-exclamation-triangle-fill"></i> Report a Problem</h1>
                <p>Help us improve by reporting bugs, requesting features, or sharing issues</p>
            </div>

            <div class="success-message" id="successMessage">
                <i class="bi bi-check-circle-fill"></i>
                <h3>Report Submitted Successfully!</h3>
                <p>Thank you for your feedback. Our team will review your report and get back to you soon.</p>
            </div>

            <div class="report-form-container" id="reportForm">
                <form action="forms/submit-report.php" method="POST" enctype="multipart/form-data" onsubmit="handleSubmit(event)">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userID); ?>">
                    <input type="hidden" name="user_type" value="<?php echo $userType; ?>">
                    
                    <div class="form-section">
                        <h3><i class="bi bi-tag-fill"></i> Problem Category</h3>
                        <div class="category-grid">
                            <div class="category-option">
                                <input type="radio" name="category" id="cat-bug" value="Bug" required>
                                <label for="cat-bug" class="category-label">
                                    <i class="bi bi-bug-fill"></i>
                                    <div class="category-info">
                                        <span class="category-name">Bug</span>
                                        <span class="category-desc">Something isn't working</span>
                                    </div>
                                </label>
                            </div>
                            <div class="category-option">
                                <input type="radio" name="category" id="cat-feature" value="Feature Request">
                                <label for="cat-feature" class="category-label">
                                    <i class="bi bi-lightbulb-fill"></i>
                                    <div class="category-info">
                                        <span class="category-name">Feature Request</span>
                                        <span class="category-desc">Suggest an improvement</span>
                                    </div>
                                </label>
                            </div>
                            <div class="category-option">
                                <input type="radio" name="category" id="cat-account" value="Account Issue">
                                <label for="cat-account" class="category-label">
                                    <i class="bi bi-person-fill-exclamation"></i>
                                    <div class="category-info">
                                        <span class="category-name">Account Issue</span>
                                        <span class="category-desc">Problem with your account</span>
                                    </div>
                                </label>
                            </div>
                            <div class="category-option">
                                <input type="radio" name="category" id="cat-other" value="Other">
                                <label for="cat-other" class="category-label">
                                    <i class="bi bi-question-circle-fill"></i>
                                    <div class="category-info">
                                        <span class="category-name">Other</span>
                                        <span class="category-desc">Something else</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="bi bi-flag-fill"></i> Priority Level</h3>
                        <div class="priority-chips">
                            <div class="priority-chip">
                                <input type="radio" name="priority" id="pri-low" value="Low" checked>
                                <label for="pri-low" class="priority-label">Low</label>
                            </div>
                            <div class="priority-chip">
                                <input type="radio" name="priority" id="pri-medium" value="Medium">
                                <label for="pri-medium" class="priority-label">Medium</label>
                            </div>
                            <div class="priority-chip">
                                <input type="radio" name="priority" id="pri-high" value="High">
                                <label for="pri-high" class="priority-label">High</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="bi bi-pencil-square"></i> Problem Details</h3>
                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-control" 
                                   placeholder="Brief description of the problem" required maxlength="255">
                        </div>
                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" class="form-control" 
                                      placeholder="Please provide detailed information about the problem, including steps to reproduce if applicable..." 
                                      required></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="bi bi-image-fill"></i> Screenshot (Optional)</h3>
                        <div class="form-group">
                            <div class="file-upload">
                                <input type="file" id="screenshot" name="screenshot" accept="image/*" onchange="updateFileName(this)">
                                <label for="screenshot" class="file-upload-label">
                                    <i class="bi bi-cloud-upload-fill"></i>
                                    <div class="file-info">
                                        <span class="file-title" id="fileName">Click to upload screenshot</span>
                                        <span class="file-hint">PNG, JPG or GIF (Max 5MB)</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send-fill"></i> Submit Report
                    </button>
                </form>
            </div>
        </div>

        <?php if ($isStudent): ?>
            <!-- Diagnosis Bot Modal -->
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
                <br><button id="solutionBtn" onclick="showSolution()" style="display:none;" class="botButtonProperty">First Aid</button><br>
                <div class="solution" id="solutionText"></div>
            </div>
        <?php endif; ?>
    </main>

    <footer id="footer" class="footer light-background">
        <div class="container">
            <div class="copyright text-center">
                <p>© <span>Copyright</span> <strong class="px-1 sitename">UIU HealthCare</strong> <span>All Rights Reserved</span></p>
            </div>
            <div class="social-links d-flex justify-content-center">
                <a href="https://twitter.com/UIU_BD" target="_blank"><i class="bi bi-twitter-x"></i></a>
                <a href="https://www.facebook.com/uiu.ac.bd" target="_blank"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/uiu_bd/" target="_blank"><i class="bi bi-instagram"></i></a>
                <a href="https://www.linkedin.com/school/uiu-bd/" target="_blank"><i class="bi bi-linkedin"></i></a>
            </div>
            <div class="credits">
                Designed by <a href="https://github.com/Sharif2023">Shariful Islam</a>
            </div>
        </div>
    </footer>

    <script src="assets/js/<?php echo $isStudent ? 'student' : 'doctor'; ?>.js"></script>
    <script>
        function updateFileName(input) {
            const fileName = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                fileName.textContent = input.files[0].name;
            } else {
                fileName.textContent = 'Click to upload screenshot';
            }
        }

        function handleSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('forms/submit-report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('successMessage').classList.add('show');
                    document.getElementById('reportForm').style.display = 'none';
                    setTimeout(() => {
                        window.location.href = '<?php echo $isStudent ? "homepagestudent.php" : "homepagedoctor.php"; ?>';
                    }, 3000);
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your report. Please try again.');
            });
        }
    </script>
</body>

</html>
