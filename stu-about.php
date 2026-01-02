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
$conn->close();

// Now set the profile picture
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
  <title>Students About - UIU HealthCare</title>

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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: #f5f8ff;
      color: #333;
      line-height: 1.6;
    }

    header {
      background: #4CAF4F;
      color: #fff;
      padding: 20px 40px;
      text-align: center;
      animation: slideIn 1s ease-out;
    }

    .about-h1 {
      font-size: 2.5rem;
      color: white;
      font-weight: bolder;
      text-shadow:
        0 0 2px green,
        0 0 4px green,
        0 0 6px green,
        -2px -2px 0 white,
        2px -2px 0 white,
        -2px 2px 0 white,
        2px 2px 0 white;
    }

    .intro {
      padding: 60px 40px;
      display: flex;
      gap: 40px;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .intro img {
      width: 100%;
      max-width: 500px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .intro-text {
      max-width: 600px;
      animation: fadeInUp 1.2s ease;
    }

    .intro-text h2 {
      font-size: 2rem;
      margin-bottom: 20px;
      color: #4CAF4F;
    }

    .video-section {
      text-align: center;
      padding: 60px 20px;
      background-color: #e6f0ff;
    }

    .video-section h2 {
      margin-bottom: 20px;
      color: #4CAF4F;
    }

    .video-section iframe {
      width: 90%;
      max-width: 800px;
      height: 450px;
      border-radius: 10px;
      border: none;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      padding: 60px 40px;
      background-color: #ffffff;
    }

    .feature-card {
      background: #f0f8ff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-8px);
    }

    .feature-card h3 {
      margin-bottom: 10px;
      color: #4CAF4F;
    }


    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideIn {
      from {
        transform: translateY(-100%);
      }

      to {
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      .intro {
        flex-direction: column;
        text-align: center;
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
          <li><a href="stu-doctor.php">Doctor</a></li>
          <li>
          <li><a href="nearby-hospitals.php">Hospitals</a></li>
          </li>
          <li><a href="stu-medicine-test.php">Medicine & Test</a></li>
          <li><a href="stu-blogs.php">Blogs</a></li>
          <li><a href="stu-about.php" class="active">About</a></li>
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
          <div class="logout-btn">
            <i class="bi bi-box-arrow-right"></i> Log out
          </div>
        </div>
      </div>

    </div>
  </header>

  <?php include 'about-content.php'; ?>


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