<?php
session_start();
if (!isset($_SESSION['doctorID'])) {
  header('Location: login-signup.html');
  exit();
}

// Optional: Fetch doctor profile image from DB
$doctorID = $_SESSION['doctorID'];
$doctorName = $_SESSION['doctorName'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Homepage - UIU HealthCare</title>

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
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

    .dashboard {
      padding: 40px;
      text-align: center;
      background-image: url(https://i.pinimg.com/originals/1f/a8/5e/1fa85e15d783da455ba35665454041fc.gif);
      background-repeat: no-repeat;
      background-size: 100vw;
      background-position: center;
      background-attachment: fixed;
    }

    .dashboard h1 {
      color: #333;
      font-weight: bold;
      font-size: 2.5rem;
      margin-bottom: 10px;
    }

    .dashboard p {
      color: #666;
      font-size: 16px;
    }

    /* Statistics stats-cards */
    .stats-doctor {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
      width: 100%;
    }

    .stats-card {
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 250px;
      text-align: center;
      transition: 0.3s ease-in-out;
    }

    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .stats-card h2 {
      font-size: 18px;
      font-weight: bolder;
      color: #4caf4f;
      margin-bottom: 10px;
    }

    .stats-card p {
      font-size: 16px;
      color: #555;
    }

    /* Quick Actions */
    .quick-actions {
      margin-top: 30px;
    }

    .quick-actions button {
      background: #4caf4f;
      color: white;
      padding: 12px 20px;
      margin: 10px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: 0.3s;
    }

    .quick-actions button:hover {
      background: #388e3c;
      transform: scale(1.05);
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
          <li><a href="#hero" class="active">Home</a></li>
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

  <section class="dashboard">
    <h1>Welcome, Doctor 💖</h1>
    <p>Manage your patients efficiently with our healthcare system.</p>

    <div class="stats-doctor">
      <div class="stats-card">
        <h2>Today's Appointments</h2>
        <p>5 Pending</p>
      </div>
      <div class="stats-card">
        <h2>Active Patients</h2>
        <p>12 Ongoing Treatments</p>
      </div>
      <div class="stats-card">
        <h2>Pending Prescriptions</h2>
        <p>3 to Review</p>
      </div>
    </div>

    <div class="quick-actions">
      <button onclick="alert('View Appointments')">📅 View Appointments</button>
      <button onclick="alert('Search Medicines/Tests')">🔍 Search Medicines/Tests</button>
      <button onclick="alert('Create Prescription')">📝 Share Blogs & Tips</button>
    </div>
  </section>


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
  <script src="assets/js/student.js"></script>

</body>

</html>