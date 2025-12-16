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

// Now set the profile picture
$dropdownProfilePicture = $student['ProfilePicture'] ?? '';

if (empty($dropdownProfilePicture)) {
    $dropdownProfilePicture = "https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg";
}

// Fetch blogs and reaction counts
$sql = "SELECT b.BlogID, b.Title, b.Content, b.Image, b.CreatedAt, 
               IFNULL(COUNT(br.ReactionID), 0) AS reactionCount
        FROM blogs b
        LEFT JOIN blog_reactions br ON b.BlogID = br.BlogID
        GROUP BY b.BlogID
        ORDER BY b.CreatedAt DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Blogs - UIU HealthCare</title>

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
    <link href="assets/css/blogs.css" rel="stylesheet">
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
                    <li><a href="stu-doctor.php">Doctor</a></li>
                    <li>
                    <li><a href="nearby-hospitals.php">Hospitals</a></li>
                    </li>
                    <li><a href="stu-medicine-test.php">Medicine & Test</a></li>
                    <li><a href="stu-blogs.php" class="active">Blogs</a></li>
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
            <br><button id="solutionBtn" onclick="showSolution()" style="display:none;" class="botButtonProperty">First
                Aid</button><br>
            <div class="solution" id="solutionText"></div>
        </div>

        <!--Blog-->
        <div class="blog-card-deck">
            <?php
            while ($row = $result->fetch_assoc()) {
                $blogID = $row['BlogID'];
                $title = $row['Title'];
                $content = $row['Content'];
                $image = $row['Image'];
                $createdAt = $row['CreatedAt'];
                $reactionCount = $row['reactionCount']; // Reaction count for each blog

                // Display each blog card
                echo '<div class="blog-card">';
                echo '<img class="blog-card-img" src="blogs_img/' . $image . '" alt="' . $title . '">';
                echo '<div class="blog-card-body">';
                echo '<h5 class="blog-card-title">' . $title . '</h5>';
                echo '<p class="blog-card-text">' . substr($content, 0, 100) . '...</p>';
                echo '</div>';
                echo '<div class="blog-modal-link"><a href="#" onclick="openBlogModal(' . $blogID . ')">Read More</a></div>';
                echo '<div class="blog-card-footer"><small class="blog-card-footer-text">posted ' . $createdAt . '</small></div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Blog Modal -->
        <div id="blogModal" class="blog-modal">
            <div class="blog-modal-content">
                <button onclick="closeBlogModal()" class="blog-close-btn">&times;</button>
                <h2 id="modalTitle">Blog Title</h2>
                <img id="modalImage" src="" alt="Blog Image">
                <p id="modalContent">Full blog content goes here...</p>
                <div id="modalTime" class="blog-modal-time">Posted just now</div>
                <div class="modal-footer">
                    <button class="love-btn" onclick="toggleLove(<?php echo $blogID; ?>)">💚 <span id="loveCount"><?php echo $reactionCount; ?></span></button>
                    <button class="blog-close-modal-btn" onclick="closeBlogModal()">Close</button>
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
    <script>
        function openBlogModal(blogID) {
            fetch(`get-blog.php?id=${blogID}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    document.getElementById('modalTitle').textContent = data.title;
                    document.getElementById('modalContent').textContent = data.content;
                    document.getElementById('modalContent').style.whiteSpace = "pre-wrap";
                    document.getElementById('modalImage').src = 'blogs_img/' + data.image;
                    document.getElementById('modalTime').textContent = 'Posted: ' + data.createdAt;
                    document.getElementById('blogModal').style.display = 'flex';
                });
        }

        function closeBlogModal() {
            document.getElementById('blogModal').style.display = 'none';
        }

        // Optional: close modal on outside click
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('blogModal');
            if (e.target === modal) {
                closeBlogModal();
            }
        });

        // Optional: ESC key to close modal
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBlogModal();
            }
        });

        function toggleLove(blogID) {
            const studentID = <?php echo json_encode($studentID); ?>; // Use PHP to dynamically insert studentID

            fetch('add-reaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `blogID=${blogID}&studentID=${studentID}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    document.getElementById('loveCount').textContent = data.reactionCount; // Update love count
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error processing your reaction.');
                });
        }
    </script>

</body>

</html>