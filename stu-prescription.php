<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['studentID'])) {
    header('Location: login-signup.html');
    exit();
}

$studentID = $_SESSION['studentID'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiu_healthcare";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = "No prescriptions found for you."; // Default message

// Fetch prescription details
$sql = "SELECT p.PrescriptionID, p.Symptoms, p.Tests, p.Advice, p.Medicines, p.MedicineSchedule, p.CreatedAt, d.FullName as doctorName
        FROM prescriptions p
        JOIN appointments a ON p.AppointmentID = a.AppointmentID
        JOIN doctors d ON p.DoctorID = d.DoctorID
        WHERE a.StudentID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();

$prescriptions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Decode JSON values
        $medicines = json_decode($row['Medicines'], true);
        $medicineSchedules = json_decode($row['MedicineSchedule'], true);

        // Ensure that the decoded data is an array before proceeding
        if (is_array($medicines) && is_array($medicineSchedules)) {
            // Prepare medicines and their schedules for display
            $medicinesList = [];
            foreach ($medicines as $index => $medicine) {
                $medicinesList[] = $medicine . ' (' . $medicineSchedules[$index] . ')';
            }

            // Add medicines with schedules to the prescription data
            $row['Medicines'] = implode(', ', $medicinesList);
        }
        $prescriptions[] = $row;
    }
} else {
    // If no prescriptions are found, this message will be shown
    $message = "No prescriptions found for you.";
}

$stmt->close();

// Check if the request is for AJAX
if (isset($_GET['PrescriptionID'])) {
    $prescriptionID = $_GET['PrescriptionID'];

    // Fetch prescription details for AJAX request
    $sql = "SELECT p.PrescriptionID, p.Symptoms, p.Tests, p.Advice, p.Medicines, p.MedicineSchedule, p.CreatedAt, d.FullName as doctorName
            FROM prescriptions p
            JOIN doctors d ON p.DoctorID = d.DoctorID
            WHERE p.PrescriptionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $prescriptionID);
    $stmt->execute();
    $result = $stmt->get_result();
    $prescription = $result->fetch_assoc();

    // Check if prescription exists
    if ($prescription) {
        // Decode the medicines and schedules
        $medicines = json_decode($prescription['Medicines'], true);
        $medicineSchedules = json_decode($prescription['MedicineSchedule'], true);

        // Create a formatted list of medicines with schedules
        $medicinesText = '';
        if (is_array($medicines) && is_array($medicineSchedules)) {
            foreach ($medicines as $index => $medicine) {
                $medicinesText .= '<p>' . $medicine . ' (' . $medicineSchedules[$index] . ')</p>';
            }
        }

        // Send prescription data as JSON
        echo json_encode([
            'doctorName' => $prescription['doctorName'],
            'Symptoms' => $prescription['Symptoms'],
            'Tests' => $prescription['Tests'],
            'Advice' => $prescription['Advice'],
            'MedicineSchedule' => $medicinesText,  // Send the medicine schedules here
            'CreatedAt' => $prescription['CreatedAt']
        ]);
    } else {
        echo json_encode([]);  // Empty array if no prescription found
    }
    exit();
}

$conn->close();
$dropdownProfilePicture = $student['ProfilePicture'] ?? 'https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg';
$doctorProfilePicture = $doctor['ProfilePicture'] ?? 'https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg';
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .prescription-list {
            margin: 20px 0;
        }

        .prescription-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .view-details-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .view-details-btn:hover {
            background-color: #218838;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 900px;
            height: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #d1ffbd;
            padding: 30px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .modal-content h4 {
            margin-top: 0;
            text-align: center;
            color: #333;
        }

        .modal-details {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 30px;
        }

        .modal-details p {
            font-size: 16px;
            margin: 8px 0;
            color: #333;
        }

        .modal-details .left-column,
        .modal-details .right-column {
            width: 100%;
        }

        .modal-details .right-column {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 768px) {

            .modal-details .left-column,
            .modal-details .right-column {
                width: 48%;
            }
        }

        .close-btn {
            background-color: red;
            color: white;
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            width: 100%;
            margin-top: 20px;
        }

        .close-btn:hover {
            background-color: #e74c3c;
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
                    <li><a href="stu-doctor.php" class="active">Doctor</a></li>
                    <li>
                    <li><a href="nearby-hospitals.php">Hospitals</a></li>
                    </li>
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
        <h2 style="text-align: center;">My Prescriptions</h2>

        <div class="prescription-list">
            <?php if (isset($prescriptions) && !empty($prescriptions)): ?>
                <?php foreach ($prescriptions as $prescription): ?>
                    <div class="prescription-item">
                        <h4>Prescription ID: <?php echo htmlspecialchars($prescription['PrescriptionID']); ?></h4>
                        <p><strong>Doctor: </strong><?php echo htmlspecialchars($prescription['doctorName']); ?></p>
                        <p><strong>Symptoms: </strong><?php echo htmlspecialchars($prescription['Symptoms']); ?></p>
                        <p><strong>Tests: </strong><?php echo htmlspecialchars($prescription['Tests']); ?></p>
                        <p><strong>Advice: </strong><?php echo htmlspecialchars($prescription['Advice']); ?></p>
                        <p><strong>Prescription Date: </strong><?php echo htmlspecialchars($prescription['CreatedAt']); ?></p>
                        <button class="view-details-btn" data-id="<?php echo $prescription['PrescriptionID']; ?>">View Details</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
        </div>

        <!-- Modal Structure -->
        <div class="modal" id="prescriptionModal">
            <div class="modal-content">
                <h4>Prescription Details</h4>
                <div class="modal-details">
                    <div class="left-column">
                        <p><strong>Doctor: </strong><span id="doctorName"></span></p>
                        <p><strong>Symptoms: </strong><span id="symptoms"></span></p>
                        <p><strong>Tests: </strong><span id="tests"></span></p>
                        <p><strong>Advice: </strong><span id="advice"></span></p>
                    </div>
                    <div class="right-column">
                        <p><strong>Medicines: </strong><span id="medicines"></span></p>
                        <p><strong>Medicine Schedule: </strong><span id="medicineSchedule"></span></p>
                        <p><strong>Prescription Date: </strong><span id="prescriptionDate"></span></p>
                    </div>
                </div>
                <button type="button" class="close-btn" id="closeModal">Close</button>
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
        $(document).ready(function() {
            // Show modal with details when "View Details" button is clicked
            $(".view-details-btn").click(function() {
                var prescriptionID = $(this).data("id");

                // Fetch prescription details using AJAX
                $.ajax({
                    url: "stu-prescription.php", // Call the same page to fetch details
                    type: "GET",
                    data: {
                        PrescriptionID: prescriptionID
                    },
                    success: function(data) {
                        if (data) {
                            // Parse the data received
                            var prescription = JSON.parse(data);

                            // Populate modal with fetched data
                            $("#doctorName").text(prescription.doctorName);
                            $("#symptoms").text(prescription.Symptoms);
                            $("#tests").text(prescription.Tests);
                            $("#advice").text(prescription.Advice);
                            $("#medicineSchedule").html(prescription.MedicineSchedule);
                            $("#prescriptionDate").text(prescription.CreatedAt);

                            // Show the modal
                            $("#prescriptionModal").fadeIn();
                        } else {
                            alert("No data found for this prescription.");
                        }
                    },
                    error: function() {
                        alert("Error fetching prescription details.");
                    }
                });
            });

            // Close modal when close button is clicked
            $("#closeModal").click(function() {
                $("#prescriptionModal").fadeOut();
            });
        });
    </script>
</body>

</html>