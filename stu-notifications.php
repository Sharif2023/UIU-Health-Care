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

// Set the profile picture
$dropdownProfilePicture = $student['ProfilePicture'] ?? '';
if (empty($dropdownProfilePicture)) {
    $dropdownProfilePicture = "https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg";
}

// Fetch notifications for this student
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sql = "SELECT NotificationID, Title, Message, IsRead, CreatedAt 
        FROM notifications 
        WHERE UserID = ? AND UserType = 'student'";

if ($filter === 'unread') {
    $sql .= " AND IsRead = FALSE";
} elseif ($filter === 'read') {
    $sql .= " AND IsRead = TRUE";
}

$sql .= " ORDER BY CreatedAt DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();

// Get unread count
$unreadSql = "SELECT COUNT(*) as unread_count FROM notifications 
              WHERE UserID = ? AND UserType = 'student' AND IsRead = FALSE";
$unreadStmt = $conn->prepare($unreadSql);
$unreadStmt->bind_param("s", $studentID);
$unreadStmt->execute();
$unreadResult = $unreadStmt->get_result();
$unreadData = $unreadResult->fetch_assoc();
$unreadCount = $unreadData['unread_count'];
$unreadStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - UIU HealthCare</title>

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
    
    <!-- Main CSS Files -->
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/notifications.css" rel="stylesheet">
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
                    <li><a href="homepagestudent.php">Home</a></li>
                    <li><a href="stu-doctor.php">Doctor</a></li>
                    <li><a href="nearby-hospitals.php">Hospitals</a></li>
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
                        <h5><?php echo htmlspecialchars($fullName); ?></h5>
                        <small>ID: <?php echo htmlspecialchars($studentID); ?></small>
                    </div>
                    <a href="student-profile.php">
                        <div class="dropdown-item">
                            <i class="bi bi-person"></i> My Profile
                        </div>
                    </a>
                    <a href="stu-notifications.php">
                        <div class="dropdown-item">
                            <i class="bi bi-bell"></i> Notification
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <a href="help-center.php">
                        <div class="dropdown-item">
                            <i class="bi bi-question-circle"></i> Help Center
                        </div>
                    </a>
                    <a href="stu-settings.php">
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
        <div class="notification-container">
            <div class="notification-header">
                <h2><i class="bi bi-bell-fill"></i> Notifications</h2>
                <div class="notification-actions">
                    <?php if ($unreadCount > 0): ?>
                        <button class="mark-all-read-btn" onclick="markAllAsRead()">
                            <i class="bi bi-check-all"></i> Mark All as Read
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="notification-filter">
                <button class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                        onclick="filterNotifications('all')">
                    All (<?php echo $notifications->num_rows; ?>)
                </button>
                <button class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>" 
                        onclick="filterNotifications('unread')">
                    Unread (<?php echo $unreadCount; ?>)
                </button>
                <button class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>" 
                        onclick="filterNotifications('read')">
                    Read
                </button>
            </div>

            <div class="notification-list">
                <?php if ($notifications->num_rows > 0): ?>
                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                        <div class="notification-item <?php echo $notif['IsRead'] ? '' : 'unread'; ?>" 
                             onclick="markAsRead(<?php echo $notif['NotificationID']; ?>)">
                            <div class="notification-icon">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title"><?php echo htmlspecialchars($notif['Title']); ?></div>
                                <div class="notification-message"><?php echo htmlspecialchars($notif['Message']); ?></div>
                                <div class="notification-time">
                                    <i class="bi bi-clock"></i>
                                    <?php 
                                        $time = strtotime($notif['CreatedAt']);
                                        $diff = time() - $time;
                                        if ($diff < 60) echo "Just now";
                                        elseif ($diff < 3600) echo floor($diff/60) . " minutes ago";
                                        elseif ($diff < 86400) echo floor($diff/3600) . " hours ago";
                                        elseif ($diff < 604800) echo floor($diff/86400) . " days ago";
                                        else echo date('M d, Y', $time);
                                    ?>
                                </div>
                            </div>
                            <?php if (!$notif['IsRead']): ?>
                                <span class="notification-badge">New</span>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-bell-slash"></i>
                        <h3>No Notifications</h3>
                        <p>You're all caught up! Check back later for updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Diagnosis Bot Modal -->
        <div class="diagnosebot-nav-modal" id="diagnosisNav">
            <button class="bot-blog-close-btn" onclick="closeDiagnoseNav()">&times;</button>
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
    </main>

    <footer id="footer" class="footer light-background">
        <div class="container">
            <div class="copyright text-center">
                <p>© <span>Copyright</span> <strong class="px-1 sitename">UIU HealthCare</strong> <span>All Rights Reserved</span></p>
            </div>
            <div class="social-links d-flex justify-content-center">
                <a href="https://twitter.com/UIU_BD"><i class="bi bi-twitter-x"></i></a>
                <a href="https://www.facebook.com/uiu.ac.bd"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/uiu_bd/"><i class="bi bi-instagram"></i></a>
                <a href="https://www.linkedin.com/school/uiu-bd/"><i class="bi bi-linkedin"></i></a>
            </div>
            <div class="credits">
                Designed by <a href="https://github.com/Sharif2023">Shariful Islam</a>
            </div>
        </div>
    </footer>

    <script src="assets/js/student.js"></script>
    <script>
        function markAsRead(notificationId) {
            fetch('forms/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function markAllAsRead() {
            fetch('forms/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'mark_all=true&user_id=<?php echo $studentID; ?>&user_type=student'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function filterNotifications(filter) {
            window.location.href = 'stu-notifications.php?filter=' + filter;
        }
    </script>
</body>

</html>
