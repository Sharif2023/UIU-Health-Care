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

$dropdownProfilePicture = $user['ProfilePicture'] ?? '';
if (empty($dropdownProfilePicture)) {
    $dropdownProfilePicture = "https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg";
}

// Fetch FAQs from database
$faqSql = "SELECT * FROM help_faq WHERE IsActive = TRUE ORDER BY Category, DisplayOrder";
$faqResult = $conn->query($faqSql);

$faqs = [];
while ($row = $faqResult->fetch_assoc()) {
    $faqs[$row['Category']][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - UIU HealthCare</title>

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
    <link href="assets/css/help-center.css" rel="stylesheet">
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
        <div class="help-container">
            <div class="help-header">
                <h1><i class="bi bi-question-circle-fill"></i> Help Center</h1>
                <p>Find answers to commonly asked questions and get support</p>
            </div>

            <div class="help-search">
                <input type="text" id="searchInput" placeholder="Search for help..." onkeyup="searchFAQ()">
                <i class="bi bi-search"></i>
            </div>

            <div class="help-categories">
                <div class="category-card" onclick="scrollToCategory('getting-started')">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                    <h3>Getting Started</h3>
                    <p>Learn the basics</p>
                </div>
                <div class="category-card" onclick="scrollToCategory('profile')">
                    <i class="bi bi-person-circle"></i>
                    <h3>Profile & Settings</h3>
                    <p>Manage your account</p>
                </div>
                <div class="category-card" onclick="scrollToCategory('appointments')">
                    <i class="bi bi-calendar-check-fill"></i>
                    <h3>Appointments</h3>
                    <p>Book and manage</p>
                </div>
                <div class="category-card" onclick="scrollToCategory('privacy')">
                    <i class="bi bi-shield-lock-fill"></i>
                    <h3>Privacy & Security</h3>
                    <p>Stay secure</p>
                </div>
            </div>

            <div class="faq-section">
                <h2>Frequently Asked Questions</h2>
                
                <?php foreach ($faqs as $category => $questions): ?>
                    <div class="faq-category" id="<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                        <div class="faq-category-title"><?php echo htmlspecialchars($category); ?></div>
                        <?php foreach ($questions as $faq): ?>
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span><?php echo htmlspecialchars($faq['Question']); ?></span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <?php echo nl2br(htmlspecialchars($faq['Answer'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="contact-support">
                <h2>Still Need Help?</h2>
                <p>Can't find the answer you're looking for? Our support team is here to help!</p>
                <a href="report-problem.php" class="btn-contact">Contact Support</a>
            </div>
        </div>

        <?php if ($isStudent): ?>
            <!-- Diagnosis Bot Modal -->
            <div class="diagnosebot-nav-modal" id="diagnosisNav">
                <button class="bot-close-btn" onclick="closeDiagnoseNav()">&times;</button>
                <h2>Diagnosis Tool</h2>
                <input type="text" id="diagnoseSearchInput" placeholder="Enter symptom or disease (e.g., fever, cough)"
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
        function toggleFAQ(element) {
            element.classList.toggle('active');
            const answer = element.nextElementSibling;
            answer.classList.toggle('active');
        }

        function scrollToCategory(categoryId) {
            const element = document.getElementById(categoryId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function searchFAQ() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const faqItems = document.getElementsByClassName('faq-item');

            for (let i = 0; i < faqItems.length; i++) {
                const question = faqItems[i].getElementsByClassName('faq-question')[0];
                const answer = faqItems[i].getElementsByClassName('faq-answer')[0];
                const txtValue = question.textContent || question.innerText;
                const answerText = answer.textContent || answer.innerText;

                if (txtValue.toLowerCase().indexOf(filter) > -1 || answerText.toLowerCase().indexOf(filter) > -1) {
                    faqItems[i].style.display = "";
                    if (filter.length > 2) {
                        answer.classList.add('active');
                        question.classList.add('active');
                    }
                } else {
                    faqItems[i].style.display = "none";
                }
            }
        }
    </script>
</body>

</html>
