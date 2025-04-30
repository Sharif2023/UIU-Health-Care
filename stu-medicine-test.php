<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['studentID'])) {
  header('Location: login-signup.html');
  exit();
}

$studentID = $_SESSION['studentID'];
$fullName = $_SESSION['fullName'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiu_healthcare";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

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
          <li><a href="#services">Appointments</a></li>
          <li><a href="#doctor">Doctor</a></li>
          <li><li><a href="nearby-hospitals.php">Hospitals</a></li></li>
          <li><a href="stu-medicine-test.php" class="active">Medicine & Test</a></li>
          <li><a href="#contact">Blog</a></li>
          <li><a href="stu-about.php">About</a></li>
          <li onclick="openDiagnoseNav()"><img src="assets/img/diagnose-bot.png" height="40px" width="40px"
              alt="Diagnosis-tool"></li>

        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <!-- Profile Dropdown -->
      <div class="profile-container">
        <div class="profile-btn" id="profileButton">
          <img src="<?php echo htmlspecialchars($dropdownProfilePicture); ?>" alt="User Avatar" style="width: 35px; height: 35px; border-radius: 50%;">
          <span><?php echo htmlspecialchars($studentID); ?></span>
          <i class="bi bi-caret-down-fill"></i>
        </div>
        <div class="dropdown-menu" id="profileDropdown">
          <div class="dropdown-header">
            <img src="<?php echo htmlspecialchars($dropdownProfilePicture); ?>" alt="User Avatar" style="width: 50px; height: 50px; border-radius: 50%;">
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
  <main class="med-test-container">
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
    <div class="med-test-tabs">
      <div class="med-test-search">
        <input type="text" id="searchBox" placeholder="Search..." onkeyup="searchData()">
      </div>
      <button id="medicineButton" class="active" onclick="showContent('medicine')">Medicine</button>
      <button id="testButton" onclick="showContent('test')">Test</button>
    </div>

    <div id="medicineContent" class="med-test-content">
      <!-- Medicine data will be loaded here -->
    </div>

    <div id="testContent" class="med-test-content" style="display: none;">
      <!-- Test data will be loaded here -->
    </div>

    <div class="pagination" id="pagination">
      <!-- Pagination buttons will be loaded here -->
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
    let currentPage = 1;
    let currentType = 'medicine';

    function showContent(type) {
      currentType = type;
      currentPage = 1;

      document.getElementById('medicineContent').style.display = type === 'medicine' ? 'block' : 'none';
      document.getElementById('testContent').style.display = type === 'test' ? 'block' : 'none';

      document.getElementById('medicineButton').classList.toggle('active', type === 'medicine');
      document.getElementById('testButton').classList.toggle('active', type === 'test');

      loadData(); // reload data for new type
    }


    function loadData() {
      const type = currentType;
      const searchQuery = document.getElementById('searchBox').value;

      fetch(`fetch-medicine-test.php?type=${type}&page=${currentPage}&search=${encodeURIComponent(searchQuery)}`)
        .then(response => response.json())
        .then(data => {
          console.log('Data:', data); // Add this line to check the response
          const contentDiv = document.getElementById(`${type}Content`);
          contentDiv.innerHTML = '';

          if (data.items.length === 0) {
            contentDiv.innerHTML = "<p>No data found.</p>";
            document.getElementById('pagination').innerHTML = '';
            return;
          }

          data.items.forEach(item => {
            const card = document.createElement('div');
            card.classList.add('med-test-card');
            card.innerHTML = `
          <h4><strong>${item.name}</strong></h4>
          <p>${item.description}</p>
          <p><strong>Price:</strong> ${item.price} TK</p>
        `;
            contentDiv.appendChild(card);
          });

          updatePagination(data.pages);
        })
        .catch(error => {
          console.error('Error:', error);
        });

    }

    function updatePagination(totalPages) {
      const paginationDiv = document.getElementById('pagination');
      paginationDiv.innerHTML = '';

      const createPageButton = (page, isCurrent = false) => {
        const btn = document.createElement('button');
        btn.innerText = page;
        if (isCurrent) {
          btn.classList.add('disabled');
        } else {
          btn.onclick = () => {
            currentPage = page;
            loadData();
          };
        }
        return btn;
      };

      const appendEllipsis = () => {
        const ellipsis = document.createElement('span');
        ellipsis.innerText = '...';
        ellipsis.style.padding = '5px 10px';
        paginationDiv.appendChild(ellipsis);
      };

      if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) {
          paginationDiv.appendChild(createPageButton(i, i === currentPage));
        }
      } else {
        paginationDiv.appendChild(createPageButton(1, currentPage === 1));
        if (currentPage > 4) appendEllipsis();

        const start = Math.max(2, currentPage - 1);
        const end = Math.min(totalPages - 1, currentPage + 1);

        for (let i = start; i <= end; i++) {
          paginationDiv.appendChild(createPageButton(i, i === currentPage));
        }

        if (currentPage < totalPages - 3) appendEllipsis();
        paginationDiv.appendChild(createPageButton(totalPages, currentPage === totalPages));
      }
    }

    function searchData() {
      currentPage = 1;
      loadData();
    }

    // Initial load
    window.onload = () => {
      showContent('medicine'); // default
    };
  </script>

</body>

</html>