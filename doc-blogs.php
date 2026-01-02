<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['doctorID'])) {
    header('Location: login-signup.html');
    exit();
}

// Optional: Fetch doctor profile image from DB
$doctorID = $_SESSION['doctorID'];
$doctorName = $_SESSION['doctorName'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title'], $_POST['content'], $_FILES['image'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $image = $_FILES['image']['name'];

        // Save image to the server
        $uploadDir = 'blogs_img/';
        $imagePath = $uploadDir . basename($image);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo json_encode(['error' => 'Failed to upload image.']);
            exit();
        }

        // Connect to the database
        $conn = db_connect();

        // Insert the blog into the database
        $stmt = $conn->prepare("INSERT INTO blogs (DoctorID, Title, Content, Image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $_SESSION['doctorID'], $title, $content, $image);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to insert blog into database.']);
        }

        $stmt->close();
        $conn->close();
        exit();
    } else {
        echo json_encode(['error' => 'Missing required fields.']);
        exit();
    }
}

$conn = db_connect();
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
    <title>Doctor Blogs - UIU HealthCare</title>

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

    <style>
        /*CSS Code have to Starts from Here and id/class name should be unique according to page name */
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
                    <li><a href="#hero">Home</a></li>
                    <li><a href="doc-appointments.php">Appointments</a></li>
                    <li><a href="patient">Patients</a></li>
                    <li><a href="doc-medicine-test.php">Medicine & Test</a></li>
                    <li><a href="doc-blogs.php" class="active">Blogs</a></li>
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
        <div class="blog-top-actions">
            <button onclick="openAddBlogModal()" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Blog
            </button>
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

        <!-- Add Blog Modal -->
        <div id="addBlogModal" class="blog-modal" style="display: none;">
            <div class="blog-modal-content">
                <button onclick="closeAddBlogModal()" class="blog-close-btn">&times;</button>
                <h2>Add New Blog</h2>
                <form id="addBlogForm" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Blog Title" required class="form-control mb-2">
                    <textarea name="content" rows="4" placeholder="Write your blog content here..." required class="form-control mb-2"></textarea>
                    <input type="file" name="image" accept="image/*" class="form-control mb-2">
                    <button type="submit" class="btn btn-success">Publish</button>
                </form>
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
    <script>
        document.getElementById('addBlogForm').addEventListener('submit', function(event) {
            event.preventDefault();

            let formData = new FormData(this);

            fetch('doc-blogs.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show the success popup
                        alert('Blog successfully added!');
                        // Optionally, clear the form fields
                        document.getElementById('addBlogForm').reset();
                        // Optionally, close the modal after success
                        closeAddBlogModal();
                    } else {
                        // Show error message
                        alert('Error: ' + (data.error || 'An unknown error occurred.'));
                    }
                })
                .catch(error => {
                    alert('Error: ' + error);
                });
            // Optionally, close the modal after success
            closeAddBlogModal();
        });

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

        function openAddBlogModal() {
            document.getElementById('addBlogModal').style.display = 'flex';
        }

        function closeAddBlogModal() {
            document.getElementById('addBlogModal').style.display = 'none';
        }
    </script>
</body>

</html>