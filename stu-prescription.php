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

$studentName = '';
$dropdownProfilePicture = 'https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg';

$ps = $conn->prepare("
    SELECT s.FullName, COALESCE(sd.ProfilePicture, '') AS ProfilePicture
    FROM students s
    LEFT JOIN student_details sd ON sd.StudentID = s.StudentID
    WHERE s.StudentID = ?
");
$ps->bind_param("s", $studentID);
$ps->execute();
$pr = $ps->get_result();
if ($row = $pr->fetch_assoc()) {
    $studentName = $row['FullName'] ?: $studentID;
    if (!empty($row['ProfilePicture'])) {
        $dropdownProfilePicture = $row['ProfilePicture'];
    }
}
$ps->close();

// Initialize message variable
$message = "No prescriptions found for you."; // Default message

// Fetch prescription details
$sql = "SELECT p.PrescriptionID, p.Symptoms, p.Tests, p.Advice, p.Medicines, p.MedicineSchedule,
        p.MedicineDuration, p.CreatedAt, d.FullName AS doctorName
        FROM prescriptions p
        JOIN appointments a ON p.AppointmentID = a.AppointmentID
        JOIN doctors d ON p.DoctorID = d.DoctorID
        WHERE a.StudentID = ?
        ORDER BY p.CreatedAt DESC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();

$prescriptions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Decode JSON values
        $medicines         = json_decode($row['Medicines'] ?? '[]', true);
        $medicineSchedules = json_decode($row['MedicineSchedule'] ?? '[]', true);
        $medicineDurations = json_decode($row['MedicineDuration'] ?? '[]', true);

        if (is_array($medicines)) {
            $medicinesList = [];
            $max = max(count($medicines), count($medicineSchedules), count($medicineDurations));
            for ($i = 0; $i < $max; $i++) {
                $m = $medicines[$i] ?? '';
                $s = $medicineSchedules[$i] ?? '';
                $d = $medicineDurations[$i] ?? '';

                if ($m === '' && $s === '' && $d === '') continue;

                $label = htmlspecialchars($m);
                if ($s !== '') $label .= ' (' . htmlspecialchars($s) . ')';
                if ($d !== '') $label .= ' — ' . htmlspecialchars($d) . ' days';

                $medicinesList[] = $label;
            }
            // show joined list in one field
            $row['Medicines']        = implode(', ', $medicinesList);
            $row['MedicineSchedule'] = ''; // optional: we’ll show combined only
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
    // Return JSON and prevent any stray output
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', '0');                 // don't print notices in response
    while (ob_get_level()) {
        ob_end_clean();
    }      // clear any previous output buffers

    $prescriptionID = (int)$_GET['PrescriptionID'];

    $sql = "SELECT p.PrescriptionID, p.Symptoms, p.Tests, p.Advice, p.Medicines, p.MedicineSchedule,
            p.MedicineDuration, p.CreatedAt, d.FullName AS doctorName
            FROM prescriptions p
            JOIN doctors d ON p.DoctorID = d.DoctorID
            WHERE p.PrescriptionID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed']);
        exit();
    }
    $stmt->bind_param("i", $prescriptionID);
    $stmt->execute();
    $result = $stmt->get_result();
    $prescription = $result->fetch_assoc();

    if (!$prescription) {
        echo json_encode([]);
        exit();
    }

    $meds = json_decode($prescription['Medicines'] ?? '[]', true);
    $sch  = json_decode($prescription['MedicineSchedule'] ?? '[]', true);
    $dur  = json_decode($prescription['MedicineDuration'] ?? '[]', true);

    $medicinesText = '';
    if (is_array($meds)) {
        $max = max(count($meds), count($sch), count($dur));
        for ($i = 0; $i < $max; $i++) {
            $m = $meds[$i] ?? '';
            $s = $sch[$i]  ?? '';
            $d = $dur[$i]  ?? '';

            if ($m === '' && $s === '' && $d === '') continue;

            $line = htmlspecialchars($m);
            if ($s !== '') $line .= ' (' . htmlspecialchars($s) . ')';
            if ($d !== '') $line .= ' — ' . htmlspecialchars($d) . ' days';

            $medicinesText .= '<p>' . $line . '</p>';
        }
    }

    echo json_encode([
        'doctorName'       => $prescription['doctorName'] ?? '',
        'Symptoms'         => $prescription['Symptoms'] ?? '',
        'Tests'            => $prescription['Tests'] ?? '',
        'Advice'           => $prescription['Advice'] ?? '',
        'MedicineSchedule' => $medicinesText,
        'CreatedAt'        => $prescription['CreatedAt'] ?? ''
    ]);
    exit();
}

// AJAX: fetch previous prescriptions list (HTML snippet only, excludes latest)
if (isset($_GET['list']) && $_GET['list'] === 'previous') {
    header('Content-Type: text/html; charset=utf-8');

    $sql = "SELECT p.PrescriptionID, p.Symptoms, p.Tests, p.Advice, p.CreatedAt, d.FullName AS doctorName
            FROM prescriptions p
            JOIN appointments a ON p.AppointmentID = a.AppointmentID
            JOIN doctors d ON p.DoctorID = d.DoctorID
            WHERE a.StudentID = ?
            ORDER BY p.CreatedAt DESC
            LIMIT 18446744073709551615 OFFSET 1"; // skip latest
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo "<p>No previous prescriptions.</p>";
        exit();
    }

    while ($row = $res->fetch_assoc()) {
        echo '<div class="prescription-item">';
        echo '<h4>Prescription ID: ' . htmlspecialchars($row['PrescriptionID']) . '</h4>';
        echo '<p><strong>Doctor: </strong>' . htmlspecialchars($row['doctorName']) . '</p>';
        echo '<p><strong>Symptoms: </strong>' . htmlspecialchars($row['Symptoms']) . '</p>';
        echo '<p><strong>Tests: </strong>' . htmlspecialchars($row['Tests']) . '</p>';
        echo '<p><strong>Advice: </strong>' . htmlspecialchars($row['Advice']) . '</p>';
        echo '<p><strong>Prescription Date: </strong>' . htmlspecialchars($row['CreatedAt']) . '</p>';
        echo '<button class="view-details-btn" data-id="' . (int)$row['PrescriptionID'] . '">View Details</button>';
        echo '</div>';
    }
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Prescription - UIU HealthCare</title>

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
            inset: 0;
            background-color: rgba(0, 0, 0, 0.45);
            z-index: 1000;
        }

        .modal-sheet {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: min(1000px, 92vw);
            background: #fff;
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
        }

        /* reuse doctor UI look */
        .prescription_form {
            background: white;
            padding: 0;
            width: 100%;
            border: none;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo-uiu img {
            width: 80px;
            height: 80px;
            margin-right: 20px;
        }

        .p-credentials {
            text-align: right;
        }

        .p-credentials h4 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .p-credentials p,
        .p-credentials small {
            margin: 4px 0;
            font-size: 14px;
        }

        .d-header {
            background-color: #28a745;
            color: #fff;
            padding: 6px 8px;
            font-weight: bold;
            border-radius: 4px;
            margin: 12px 0 6px;
        }

        .p-container {
            display: flex;
            gap: 20px;
        }

        .left-column,
        .right-column {
            width: 50%;
            padding: 0 10px;
        }

        .right-column {
            border-left: 2px solid #ddd;
        }

        .modal-block {
            background: #f8fdf6;
            border: 1px solid #e4f3e0;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 46px;
        }

        .med_list p {
            margin: 6px 0;
        }

        .btn-primary {
            background-color: #28a745;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all .2s ease;
        }

        .btn-primary:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .close-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 16px;
            cursor: pointer;
        }

        .close-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid p-container-l position-relative d-flex align-items-center justify-content-between">
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
                    <span><?php echo htmlspecialchars($studentName ?: $studentID); ?></span>
                    <i class="bi bi-caret-down-fill"></i>
                </div>
                <div class="dropdown-menu" id="profileDropdown">
                    <div class="dropdown-header">
                        <img src="<?php echo htmlspecialchars($dropdownProfilePicture); ?>" alt="User Avatar">
                        <h5><?php echo htmlspecialchars($studentName ?: $studentID); ?></h5>
                        <small>ID: <?php echo htmlspecialchars($studentID); ?></small>
                    </div>
                    <a href="student-profile.php">
                        <div class="dropdown-item"><i class="bi bi-person"></i> My Profile</div>
                    </a>
                    <div class="dropdown-item"><i class="bi bi-bell"></i> Notification</div>
                    <div class="dropdown-item"><i class="bi bi-question-circle"></i> Help Center</div>
                    <div class="dropdown-item"><i class="bi bi-gear"></i> Settings and Privacy</div>
                    <a href="login-signup.html">
                        <div class="logout-btn"><i class="bi bi-box-arrow-right"></i> Log out</div>
                    </a>
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
                        <p><strong>Medicines: </strong><?php echo $prescription['Medicines']; ?></p>
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

        <button class="view-previous-prescriptions-btn" onclick="togglePreviousPrescriptions()">View Previous Prescriptions</button>

        <div class="previous-prescriptions" style="display:none;">
            <!-- List of previous prescriptions -->
        </div>
        <!-- Modal Structure -->
        <div class="modal" id="prescriptionModal">
            <div class="modal-sheet">
                <!-- header (same vibe as prescribe.php) -->
                <div class="pf-title" style="margin:0 0 10px;">Prescription</div>

                <div class="prescription_form" style="border:none; box-shadow:none; width:100%; padding:0;">
                    <div class="header" style="margin-bottom:16px;">
                        <div class="logo-uiu">
                            <img src="https://upload.wikimedia.org/wikipedia/en/thumb/6/6b/United_International_University_Monogram.svg/800px-United_International_University_Monogram.svg.png" alt="Logo">
                        </div>
                        <div class="p-credentials">
                            <h4 id="modalDoctorName">Doctor</h4>
                            <p>UIU Medical Center</p>
                            <small>Address: UIU Campus, Dhaka</small><br>
                            <small>Mb. 017XXXXXXXXX</small>
                        </div>
                    </div>

                    <!-- two columns like prescribe.php -->
                    <div class="p-container" style="padding:0;">
                        <!-- Left Column -->
                        <div class="left-column" style="padding-left:0;">
                            <div class="desease_details">
                                <h4 class="d-header">Symptoms</h4>
                                <div class="modal-block" id="symptoms" style="white-space:pre-wrap;"></div>

                                <h4 class="d-header">Tests</h4>
                                <div class="modal-block" id="tests" style="white-space:pre-wrap;"></div>

                                <h4 class="d-header">Advice</h4>
                                <div class="modal-block" id="advice" style="white-space:pre-wrap;"></div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="right-column" style="padding-right:0;">
                            <span style="font-size: 2em">R <sub>x</sub></span>
                            <hr>
                            <div class="medicine">
                                <section class="med_list" id="rxList">
                                    <!-- server-rendered <p> lines go here -->
                                </section>
                            </div>

                            <div style="margin-top:12px; font-size:14px;">
                                <strong>Prescription Date: </strong><span id="prescriptionDate"></span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:18px; display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" class="btn btn-primary" id="printModalBtn" style="width:auto;">Print</button>
                        <button type="button" class="close-btn" id="closeModal" style="width:auto;">Close</button>
                    </div>
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
        $(document).ready(function() {
            // Delegate so dynamically added buttons work too
            $(document).on("click", ".view-details-btn", function() {
                var prescriptionID = $(this).data("id");
                $.ajax({
                    url: "stu-prescription.php",
                    type: "GET",
                    dataType: "json", // <— ADD THIS
                    data: {
                        PrescriptionID: prescriptionID
                    },
                    success: function(p) {
                        if (!p || p.error) {
                            alert("No data found for this prescription.");
                            return;
                        }

                        // header p-credentials: doctor name
                        $("#modalDoctorName").text(p.doctorName || "Doctor");

                        // left column
                        $("#symptoms").text(p.Symptoms || "");
                        $("#tests").text(p.Tests || "");
                        $("#advice").text(p.Advice || "");

                        // right column Rx lines (server already built <p> items with schedule + days)
                        $("#rxList").html(p.MedicineSchedule || "");

                        // footer meta
                        $("#prescriptionDate").text(p.CreatedAt || "");

                        // open modal
                        $("#prescriptionModal").fadeIn();
                    },
                    error: function(xhr) {
                        console.error("Server replied:", xhr.responseText);
                        alert("Error fetching prescription details.");
                    }
                });
            });

            // Close modal
            $("#closeModal").on("click", function() {
                $("#prescriptionModal").fadeOut();
            });
        });

        $("#printModalBtn").on("click", function() {
            // quick print of modal contents
            const $sheet = $(".modal-sheet").clone(true);
            const w = window.open("", "_blank");
            w.document.write(`
    <html>
      <head>
        <title>Prescription</title>
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <style>
          body { padding: 20px; font-family: Arial, sans-serif; }
        </style>
      </head>
      <body>${$sheet.prop('outerHTML')}</body>
    </html>
  `);
            w.document.close();
            w.focus();
            w.print();
            // w.close(); // uncomment if you want the window to auto-close after printing
        });


        function togglePreviousPrescriptions() {
            var prevDiv = document.querySelector('.previous-prescriptions');
            var btn = document.querySelector('.view-previous-prescriptions-btn'); // the button
            var visible = prevDiv.style.display === 'block';

            if (!visible) {
                $.ajax({
                    url: "stu-prescription.php",
                    type: "GET",
                    data: {
                        list: "previous"
                    },
                    success: function(html) {
                        prevDiv.innerHTML = html;
                        prevDiv.style.display = 'block';
                        btn.textContent = "Hide Previous Prescriptions"; // 🔄 change label
                    },
                    error: function() {
                        prevDiv.innerHTML = "<p>Failed to load previous prescriptions.</p>";
                        prevDiv.style.display = 'block';
                        btn.textContent = "Hide Previous Prescriptions"; // still update
                    }
                });
            } else {
                prevDiv.style.display = 'none';
                btn.textContent = "View Previous Prescriptions"; // 🔄 back to default
            }
        }
    </script>
</body>

</html>