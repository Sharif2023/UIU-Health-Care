<?php
session_start();

// Ensure that the doctor is logged in
if (!isset($_SESSION['doctorID'])) {
    header('Location: login-signup.html');
    exit();
}

$doctorID = $_SESSION['doctorID'];

// Handle the form submission for prescribing a treatment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointmentID = $_POST['appointmentID'];
    $symptoms = $_POST['symptoms'];
    $tests = $_POST['tests'];
    $advice = $_POST['advice'];
    $medicines = isset($_POST['medicines']) ? json_encode($_POST['medicines']) : json_encode([]);
    $medicineSchedules = isset($_POST['medicines_schedule']) ? json_encode($_POST['medicines_schedule']) : json_encode([]);
    $medicineDurations = isset($_POST['medicines_duration']) ? json_encode($_POST['medicines_duration']) : json_encode([]);

    // Database connection
    require_once __DIR__ . '/config.php';
    $conn = db_connect();

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO prescriptions (AppointmentID, DoctorID, Symptoms, Tests, Advice, Medicines, MedicineSchedule, MedicineDuration) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssss",
        $appointmentID,
        $doctorID,
        $symptoms,
        $tests,
        $advice,
        $medicines,
        $medicineSchedules,
        $medicineDurations
    );

    if ($stmt->execute()) {
        $message = "Prescription submitted successfully!";
    } else {
        $message = "Error: Could not submit prescription.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Prescription Form - UIU Health Care</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .pf-title {
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #333;
        }

        .prescription_form {
            background: white;
            padding: 20px;
            width: 80%;
            border: 1px solid #ddd;
        }

        .prescription {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo-uiu img {
            width: 100px;
            height: 100px;
            margin-right: 20px;
            /* Add space between logo and details */
        }

        .credentials {
            text-align: right;
        }

        .credentials h4 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .credentials p,
        .credentials small {
            margin: 5px 0;
            font-size: 14px;
        }

        .d-header {
            background-color: #28a745;
            color: white;
            padding: 5px;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }

        #add_med {
            cursor: pointer;
            margin-top: 10px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
        }

        #add_med:hover {
            background-color: #0056b3;
        }

        .med_name_action {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .med_name_action button {
            padding: 5px 15px;
            margin-top: 10px;
        }

        .container {
            display: flex;
        }

        .left-column,
        .right-column {
            width: 50%;
            padding: 20px;
        }

        .right-column {
            border-left: 2px solid #ddd;
        }

        .btn-primary {
            background-color: #28a745;
            /* Green background */
            color: white;
            /* White text */
            padding: 10px 20px;
            /* Padding inside the button */
            border: none;
            /* Remove the default border */
            border-radius: 5px;
            /* Rounded corners */
            font-size: 16px;
            /* Text size */
            font-weight: bold;
            /* Bold text */
            cursor: pointer;
            /* Pointer cursor on hover */
            transition: all 0.3s ease;
            /* Smooth transition for hover effects */
            width: 100%;
            /* Full width on mobile devices */
        }

        /* Hover effect for the button */
        .btn-primary:hover {
            background-color: #218838;
            /* Darker green for hover effect */
            transform: scale(1.05);
            /* Slightly enlarge the button on hover */
        }

        /* Button focus effect */
        .btn-primary:focus {
            outline: none;
            /* Remove default focus outline */
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.7);
            /* Add a soft green glow on focus */
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
                    <li><a href="#hero">Home</a></li>
                    <li><a href="doc-appointments.php" class="active">Appointments</a></li>
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
    <main class="main">
        <h2 class="pf-title">Prescription Form</h2>
        <div class="wrapper">
            <div class="prescription_form">
                <form method="POST">
                    <input type="hidden" name="appointmentID" value="<?php echo $_GET['appointmentID']; ?>" />

                    <!-- Doctor Details -->
                    <div class="header">
                        <div class="logo-uiu">
                            <img src="https://upload.wikimedia.org/wikipedia/en/thumb/6/6b/United_International_University_Monogram.svg/800px-United_International_University_Monogram.svg.png" alt="Logo">
                        </div>
                        <div class="credentials">
                            <h4>Dr. Shamima Akter</h4>
                            <p>UIU Medical Center</p>
                            <small>Address: UIU Campus, Dhaka</small><br>
                            <small>Mb. 017XXXXXXXXX</small>
                        </div>
                    </div>

                    <!-- Container for left and right columns -->
                    <div class="container">
                        <!-- Left Column: Symptoms, Tests, and Advice -->
                        <div class="left-column">
                            <div class="desease_details">
                                <h4 class="d-header">Symptoms</h4>
                                <textarea class="form-control" name="symptoms" placeholder="Describe the symptoms" required></textarea>

                                <h4 class="d-header">Tests</h4>
                                <textarea class="form-control" name="tests" placeholder="List the recommended tests" required></textarea>

                                <h4 class="d-header">Advice</h4>
                                <textarea class="form-control" name="advice" placeholder="Provide any advice" required></textarea>
                            </div>
                        </div>

                        <!-- Right Column: RX and Medicine Input -->
                        <div class="right-column">
                            <span style="font-size: 2em">R <sub>x</sub></span>
                            <hr>
                            <div class="medicine">
                                <section class="med_list">
                                    <!-- Medicines will be added here dynamically -->
                                </section>
                                <div id="add_med" data-toggle="tooltip" data-placement="right" title="Click anywhere on the blank space to add more.">
                                    Click to add...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Submit Prescription</button>
                </form>
            </div>
        </div>
    </main>
    <!-- Template for new medicine -->
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

    <script id="new_medicine" type="text/template">
        <div class="med" style="border:1px solid #eee;padding:10px;border-radius:6px;margin-bottom:10px;position:relative;">
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input class="form-control" placeholder="Enter medicine name" name="medicines[]" required style="flex:1 1 220px;" />

        <select class="form-control" name="medicines_schedule[]" required style="width:140px;">
            <option value="1+1+1">1+1+1</option>
            <option value="1+0+1">1+0+1</option>
            <option value="1+1+0">1+1+0</option>
            <option value="0+1+1">0+1+1</option>
            <option value="0+0+1">0+0+1</option>
            <option value="0+1+0">0+1+0</option>
            <option value="1+0+0">1+0+0</option>
        </select>

        <input type="number" min="1" step="1" class="form-control"
                name="medicines_duration[]" placeholder="Days" required style="width:110px;"
                oninput="this.value = this.value.replace(/[^0-9]/g,'');" />

        <button type="button" class="btn btn-sm btn-danger remove-med" title="Remove this medicine" style="background:#dc3545;border:none;padding:6px 10px;">
            <i class="fas fa-trash-alt"></i>
        </button>
        </div>
    </div>
    </script>

    <script>
        $(document).ready(function() {
            // Add new medicine
            $("#add_med").on("click", function() {
                const tpl = $("#new_medicine").html();
                $(".med_list").append(tpl);
            });

            // Remove a medicine row
            $(document).on("click", ".remove-med", function() {
                $(this).closest(".med").remove();
            });
        });
    </script>

</body>

</html>