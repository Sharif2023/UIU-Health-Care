<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['doctorID'])) {
    header('Location: login-signup.html');
    exit();
}

// Get doctor's ID and Name from session
$doctorID = $_SESSION['doctorID'];
$doctorName = $_SESSION['doctorName'];

// Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiu_healthcare";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctor profile from database
$sql = "SELECT 
            d.DoctorID, d.Email, d.FullName, d.Gender, d.Designation, 
            d.Department, d.Mobile, d.ProfilePicture, d.Bio
        FROM doctors d
        WHERE d.DoctorID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $doctorID);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// Close connection
$stmt->close();
$conn->close();

$dropdownProfilePicture = $doctor['ProfilePicture'] ?? '';

if (empty($dropdownProfilePicture)) {
    $dropdownProfilePicture = "https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments List - UIU HealthCare</title>

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
        .btn{
            background-color: green;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover{
            background-color: #4caf4f;
        }
    </style>
</head>

<body class="doc-pro-body">
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-l position-relative d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <img src="assets/img/header-logo.png" alt="header-logo">
                <h1 class="sitename">UIU<span> HealthCare</span></h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="homepagedoctor.php">Home</a></li>
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
                        <h5><small>Senior Medical Officer, UIU Medical Centre</small></h5>
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
        <section id="doctor-appointments" class="container mt-5">
            <h2 class="text-center mb-4">Upcoming Appointments</h2>
            <table class="table table-bordered" id="appointmentsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Date</th>
                        <th>Symptoms</th>
                        <th>Actions</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    $sql = "SELECT a.AppointmentID, s.FullName, s.StudentID, a.AppointmentDate, a.Symptoms, a.SymptomImage
        FROM appointments a
        JOIN students s ON a.StudentID = s.StudentID
        WHERE a.DoctorID = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $doctorID);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        $symptomImage = $row['SymptomImage'] ? $row['SymptomImage'] : 'https://thumb.ac-illust.com/b1/b170870007dfa419295d949814474ab2_t.jpeg'; // Set default if no image exists
                        echo "<tr>
                            <td>" . htmlspecialchars($row['FullName']) . "</td>
                            <td>" . htmlspecialchars($row['StudentID']) . "</td>
                            <td>" . htmlspecialchars($row['AppointmentDate']) . "</td>
                            <td>" . htmlspecialchars($row['Symptoms']) . "</td>
                            <td><img src='" . htmlspecialchars($symptomImage) . "' alt='Symptom Image' style='max-width: 100px;'></td>
                            <td><a href='prescribe.php?appointmentID=" . $row['AppointmentID'] . "' class='btn btn-primary'>Prescribe</a></td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
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
    <script src="assets/js/student.js"></script>
    <script>
        // Appointment form submission
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("appointmentForm");
            const successMessage = document.getElementById("appointmentSuccess");

            form.addEventListener("submit", function(e) {
                e.preventDefault();

                const appointment = {
                    studentName: document.getElementById("studentName").value,
                    studentID: document.getElementById("studentID").value,
                    doctorName: document.getElementById("doctorName").value,
                    appointmentDate: document.getElementById("appointmentDate").value,
                    symptoms: document.getElementById("symptoms").value,
                };

                fetch("save_appointment.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(appointment),
                    })
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.status === "success") {
                            successMessage.classList.remove("d-none");
                            form.reset();
                        } else {
                            alert("Error: " + data.message);
                        }
                    })
                    .catch((error) => {
                        console.error("Error submitting form:", error);
                        alert("An error occurred. Please try again.");
                    });
            });
        });

        function openDiagnoseNav() {
            document.getElementById("diagnosisNav").classList.add("open");
        }

        function closeDiagnoseNav() {
            document.getElementById("diagnosisNav").classList.remove("open");
        }
    </script>
</body>

</html>