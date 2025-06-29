<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['studentID'])) {
    header('Location: login-signup.html');
    exit();
}

// Get student's ID and Name from session
$studentID = $_SESSION['studentID'];
$fullName = $_SESSION['fullName'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiu_healthcare";

$conn = new mysqli($servername, $username, $password, $dbname);
// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <title>Students Homepage - UIU HealthCare</title>

    <!-- Favicons -->
    <link href="assets/img/title.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.gomaps.pro" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.gomaps.pro/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        /* Unique styles for this page */
        #map {
            height: 500px;
            width: 100%;
        }

        .hospital-list {
            margin-top: 20px;
        }

        .hospital-item {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }

        .hospital-name {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .hospital-contact {
            margin-top: 10px;
            color: #555;
        }

        .distance {
            font-size: 1.2rem;
            color: #007BFF;
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
                    <li><a href="nearby-hospitals.php" class="active">Hospitals</a></li>
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

        <!--Code have to Starts from Here and id/class name should be unique according to page name-->
        <div class="container">
            <h2 class="text-center">Nearby Hospitals</h2>
            <div id="map"></div>
            <div id="hospitalList" class="hospital-list">
                <!-- Hospital data will be displayed here -->
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
    <script src="https://maps.gomaps.pro/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script>
        let map;
        let markers = [];

        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;
                    initMap(userLat, userLon);
                    fetchNearbyHospitals(userLat, userLon);
                }, function() {
                    alert("Geolocation is not supported or permission denied.");
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function initMap(userLat, userLon) {
            map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: userLat, lng: userLon },
                zoom: 13,
            });

            const userMarker = new google.maps.Marker({
                position: { lat: userLat, lng: userLon },
                map: map,
                title: "Your Location",
            });
        }

        function fetchNearbyHospitals(userLat, userLon) {
            const apiKey = 'AlzaSyVw_neBr6ZqyTpBf6YKJAVSmMOgq-2TsaM';  // Replace with your GoMaps API Key
            const radius = 5000; // Search within 5 km
            const url = `https://maps.gomaps.pro/maps/api/place/nearbysearch/json?location=${userLat},${userLon}&radius=${radius}&type=hospital&key=${apiKey}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const hospitals = data.results;
                    displayHospitals(hospitals, userLat, userLon);
                })
                .catch(error => console.error('Error fetching hospitals:', error));
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the Earth in km
            const dLat = (lat2 - lat1) * (Math.PI / 180);
            const dLon = (lon2 - lon1) * (Math.PI / 180);
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c; // Distance in km
        }

        function displayHospitals(hospitals, userLat, userLon) {
            const hospitalListDiv = document.getElementById('hospitalList');
            hospitalListDiv.innerHTML = ''; // Clear any existing data

            hospitals.forEach(hospital => {
                const hospitalLat = hospital.geometry.location.lat;
                const hospitalLon = hospital.geometry.location.lng;
                const distance = calculateDistance(userLat, userLon, hospitalLat, hospitalLon).toFixed(2);

                const hospitalItem = document.createElement('div');
                hospitalItem.classList.add('hospital-item');
                hospitalItem.onclick = function() {
                    focusMapOnHospital(hospitalLat, hospitalLon, hospital);
                };

                const hospitalName = document.createElement('h3');
                hospitalName.classList.add('hospital-name');
                hospitalName.textContent = hospital.name;

                const hospitalContact = document.createElement('p');
                hospitalContact.classList.add('hospital-contact');
                hospitalContact.textContent = `Phone: ${hospital.international_phone_number || 'N/A'}`;

                const hospitalDistance = document.createElement('p');
                hospitalDistance.classList.add('distance');
                hospitalDistance.textContent = `Distance: ${distance} km`;

                hospitalItem.appendChild(hospitalName);
                hospitalItem.appendChild(hospitalContact);
                hospitalItem.appendChild(hospitalDistance);

                hospitalListDiv.appendChild(hospitalItem);

                const marker = new google.maps.Marker({
                    position: { lat: hospitalLat, lng: hospitalLon },
                    map: map,
                    title: hospital.name,
                });

                markers.push(marker);
            });
        }

        function focusMapOnHospital(lat, lon, hospital) {
            map.setCenter(new google.maps.LatLng(lat, lon));
            map.setZoom(15);

            const infoWindow = new google.maps.InfoWindow({
                content: `<h3>${hospital.name}</h3><p>${hospital.vicinity}</p><p>Phone: ${hospital.international_phone_number || 'N/A'}</p>`,
            });

            infoWindow.open(map, markers.find(marker => marker.getPosition().lat() === lat && marker.getPosition().lng() === lon));
        }

        window.onload = function() {
            getUserLocation();
        };
    </script>
</body>

</html>