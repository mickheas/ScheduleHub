<?php
session_start();
include '../database/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if all fields are filled
    if (empty($name) || empty($email)) {
        $error = "Error: Please fill all the fields.";
    } else {
        try {
            // Update user details
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET name = :name, email = :email, password = :password WHERE id = :user_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':password', $hashed_password);
            } else {
                $sql = "UPDATE users SET name = :name, email = :email WHERE id = :user_id";
                $stmt = $conn->prepare($sql);
            }
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                $success = "Profile updated successfully!";
            } else {
                $error = "Error: Could not update profile.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
} else {
    // Fetch user details
    try {
        $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die("Error: User not found.");
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
<title>ScheduleHub - View Schedule</title>
<meta name="description" content="">
<meta name="keywords" content="">
    <title>View Schedules - ScheduleHub</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: rgba(0, 0, 0, 0.7); /* Dark semi-transparent background */
            color: #ffffff; /* White text color */
        }
        .schedule-table th, .schedule-table td {
            border: 1px solid rgba(255, 255, 255, 0.3); /* Thin border with transparency */
            padding: 8px;
        }
        .schedule-table th {
            background-color: var(--blue);
            color: white;
            text-align: left;
        }
        .font-weight-bold {
            font-weight: bold;
        }
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
            color: #ffffff;
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
        .form-container select.form-control {
            background-color: rgba(255, 255, 255, 0.1); /* Light semi-transparent background */
            color: #ffffff; /* White text color */
        }
        .form-container select.form-control option {
            background-color: #000000; /* Dark background for options */
            color: #ffffff; /* White text color */
        }
        .schedule-table th {
            background-color: var(--blue);
            color: white;
            text-align: left;
        }
        .schedule-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .schedule-table tr:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .schedule-table td {
            color: #ffffff; /* White text color */
        } /* White text color */
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
        <li><a href="Home.html" class="active">Home<br></a></li>
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

    </div>
</header>

<main class="main">

<div class="page-title" data-aos="fade" style="background-image: url(../assets/img/lib_inside.jpeg);">
    <div class="container position-relative">
        <h2>Change Password</h2>
        <nav class="breadcrumbs">
        <ol>
            <li><a color="white" href="Home.html">Home</a></li>
            <li class="current">Change Password</li>
        </ol>
        </nav>
    </div>
</div>

    <section id="events" class="events section" style="background-color:rgba(14, 23, 33, 0.04);">
        <div class="form-container">
            <h1>Profile</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" action="profile.php">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password (leave blank to keep current password)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <button type="submit" class="cta-btn">Update Profile</button>
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