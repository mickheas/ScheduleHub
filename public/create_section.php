<?php
session_start();
include '../database/connection.php';

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    //die("Error: Access denied. Only instructors can create a section.");
    header('Location: access_denied.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $section_name = trim($_POST['section_name']);

    // Check if the section name is filled
    if (empty($section_name)) {
        die("Error: Please fill the section name.");
    }

    try {
        // Prepare the SQL statement
        $sql = "INSERT INTO sections (section_name) VALUES (:section_name)";
        $stmt = $conn->prepare($sql);

        // Bind parameters and execute the statement
        $stmt->bindParam(':section_name', $section_name);
        if ($stmt->execute()) {
            // Redirect to a confirmation page or display a success message
            header('Location: create_section.php?success=1');
            exit();
        } else {
            die("Error: Could not save the section.");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>ScheduleHub - Create Section</title>
<meta name="description" content="">
<meta name="keywords" content="">

<!-- Favicons
<link href="assets/img/favicon.png" rel="icon">
<link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">-->

<!-- Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<!-- Vendor CSS Files -->
<link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/vendor/aos/aos.css" rel="stylesheet">
<link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
<link href="../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

<!-- Main CSS File -->
<link href="../assets/css/main.css" rel="stylesheet">
<style>
        .form-container {
            background-color: rgba(0, 0, 0, 0.7); /* Dark semi-transparent background */
            border-radius: 10px;
            box-shadow: 0 0.5rem 1.7rem rgba(0, 0, 0, 0.25), 0 0.7rem 0.7rem rgba(0, 0, 0, 0.22);
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            color: #ffffff; /* White text color */
        }

        .form-container h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #ffffff; /* White text color */
        }

        .form-container .form-label {
            font-weight: bold;
            color: #ffffff; /* White text color */
        }

        .form-container .form-control {
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: rgba(255, 255, 255, 0.1); /* Light semi-transparent background */
            color: #ffffff; /* White text color */
        }

        .form-container .form-control::placeholder {
            color: #cccccc; /* Light gray placeholder text */
        }

        .form-container .btn {
            padding: 10px 20px;
            background-color: var(--blue);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container .btn:hover {
            background-color: var(--lightblue);
        }
    </style>
</head>

<body class="speaker-details-page">
<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

    <a href="home.html" class="logo d-flex align-items-center me-auto">
        
        <h1 class="sitename">ScheduleHub</h1>
    </a>

    <nav id="navmenu" class="navmenu">
    <ul>
        <li><a style = "color: white;" href="Home.html" class="active">Home<br></a></li>
        <li class="dropdown"><a href="#"><span>Create</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
        <ul>
            <li><a href="create_schedule.php">Schedule Creation</a></li>
            <li><a href="create_section.php">Section Creation</a></li>
            </ul>

        <li><a href="read_schedule.php">View Schedule</a></li>
        <li class="dropdown"><a href="#"><span>Manage Schedule</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
        <ul>
            <li><a href="update_schedule.php">Update Schedule</a></li>
            <li><a href="delete_schedule.php">Delete Schedule</a></li>
            </ul>
        <li class="dropdown"><a class= "cta-btn d-none d-sm-block" href="#"><span>Your Profile</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
        <ul>
            <li><a href="profile.php">Change password</a></li>
            <li><a href="../controllers/logout.php">Log Out</a></li>
            </ul>
    </ul>
    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

    <!--<a class="cta-btn d-none d-sm-block" href="#buy-tickets">Buy Tickets</a>-->

    </div>
</header>

<main class="main">

<div class="page-title" data-aos="fade" style="background-image: url(../assets/img/lib_inside.jpeg);">
    <div class="container position-relative">
        <h1>CREATE A SECTION</h1>
        
        <nav class="breadcrumbs">
        <ol>
        
            <li><a color="white" href="Home.html">Home</a></li>
            <li class="current">Section Creation</li>
        </ol>
        </nav>
    </div>
    </div>
    
    <section id="events" class="events section" style="background-color:rgba(14, 23, 33, 0.04);">
<div class="form-container">
        <h1>Create Section</h1>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Section created successfully!</div>
        <?php endif; ?>
        <form method="POST" action="create_section.php">
            <div class="mb-3">
                <label for="section_name" class="form-label">Section Name</label>
                <input type="text" class="form-control" id="section_name" name="section_name" required>
            </div>
            <button type="submit" class="cta-btn">Create Section</button>
        </form>
    </div>
        </section>
        </main>

<footer id="footer" class="footer dark-background">

    <div class="footer-top">
    <div class="container">
        <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
            <a href="Home.html" class="logo d-flex align-items-center">
            <span class="sitename">ScheduleHub</span>
            </a>
            <div class="footer-contact pt-3">
            <p>Lebu Medhanialem</p>
            <p>Addis Ababa, Nifas-Silk</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+251-923-082-224</span></p>
            <p><strong>Email:</strong> <span>info@schedulehub.com</span></p>
            </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
            <h4>Useful Links</h4>
            <ul>
            <li><a href="Home.html">Home</a></li>
            <li><a href="Home.html">About us</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Terms of service</a></li>
            <li><a href="#">Privacy policy</a></li>
            </ul>
        </div>

    <div class="col-lg-2 col-md-3 footer-links">
            <h4>Our Services</h4>
            <ul>
            <li><a href="#">Schedule Creation</a></li>
            <li><a href="#">Schedule Editiong</a></li>
            <li><a href="#">Schedule Viewing</a></li>
            <li><a href="#">Schedule Managing</a></li>
            </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
            <h4>Computer Science</h4>
            <ul>
                <li><a href="#">Year 3</a></li>
                <li><a href="#">Section 1</a></li>
            </ul>
            </div>

            <div class="col-lg-2 col-md-3 footer-links">
            <h4>Group members</h4>
            <ul>
                <li><a href="#">Amanuel Berihun</a></li>
                <li><a href="#">Mikyas Abebe</a></li>
                <li><a href="#">Mikyas Mebatsion</a></li>
                <li><a href="#">Minen Ali</a></li>
                <li><a href="#">Muaz Sultan</a></li>
                <li><a href="#">Nathaniel Tekalgn</a></li>
            </ul>
            </div>

        </div>
        </div>
    </div>


    <div class="copyright text-center">
        <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">

        <div class="d-flex flex-column align-items-center align-items-lg-start">
            <div>
            © Copyright <strong><span>ScheduleHub</span></strong>. All Rights Reserved
            </div>
            <div class="credits">
            Designed by <a href="#">ScheduleHub</a>
            </div>
        </div>

        <div class="social-links order-first order-lg-last mb-3 mb-lg-0">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
        </div>

        </div>
    </div>

    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/php-email-form/validate.js"></script>
    <script src="../assets/vendor/aos/aos.js"></script>
    <script src="../assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="../assets/vendor/swiper/swiper-bundle.min.js"></script>

    <!-- Main JS File -->
    <script src="../assets/js/main.js"></script>

</body>

</html>