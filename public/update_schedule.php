<?php
session_start();
include '../database/connection.php';

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    die("Error: Access denied. Only instructors can update a schedule.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['schedule_id'])) {
    $schedule_id = $_POST['schedule_id'];

    if (isset($_POST['schedule_id']) && isset($_POST['swap_subject'])) {
        // Get the input values
        $schedule_id = $_POST['schedule_id'];
        $swap_schedule_id = $_POST['swap_subject'];
    
        try {
            // Begin a transaction to ensure atomicity
            $conn->beginTransaction();
    
            // Fetch the current subjects for the given schedule IDs
            $stmt = $conn->prepare("SELECT schedule_id, subject FROM schedules WHERE schedule_id IN (:schedule_id, :swap_schedule_id)");
            $stmt->bindValue(':schedule_id', $schedule_id, PDO::PARAM_INT);
            $stmt->bindValue(':swap_schedule_id', $swap_schedule_id, PDO::PARAM_INT);
            $stmt->execute();
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (count($subjects) == 2) {
                // Map schedule IDs to subjects
                $subject_map = [];
                foreach ($subjects as $row) {
                    $subject_map[$row['schedule_id']] = $row['subject'];
                }
    
                // Perform the swap
                $update_stmt = $conn->prepare("
                    UPDATE schedules
                    SET subject = CASE
                        WHEN schedule_id = :schedule_id THEN :subject2
                        WHEN schedule_id = :swap_schedule_id THEN :subject1
                    END
                    WHERE schedule_id IN (:schedule_id, :swap_schedule_id)
                ");
                $update_stmt->bindValue(':schedule_id', $schedule_id, PDO::PARAM_INT);
                $update_stmt->bindValue(':swap_schedule_id', $swap_schedule_id, PDO::PARAM_INT);
                $update_stmt->bindValue(':subject1', $subject_map[$schedule_id]);
                $update_stmt->bindValue(':subject2', $subject_map[$swap_schedule_id]);
    
                if ($update_stmt->execute()) {
                    // Commit the transaction
                    $conn->commit();
                    $success = "Subjects swapped successfully!";
                } else {
                    // Rollback the transaction on failure
                    $conn->rollBack();
                    $error = "Failed to update database.";
                }
            } else {
                $conn->rollBack();
                $error = "Invalid schedule IDs or schedules not found.";
            }
        } catch (Exception $e) {
            // Rollback on exception
            $conn->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }else {
        $day = trim($_POST['day']);
        $period = trim($_POST['period']);
        $subject = trim($_POST['subject']);
        $classroom = trim($_POST['classroom']);

        // Check if all fields are filled
        if (empty($day) || empty($period) || empty($subject) || empty($classroom)) {
            die("Error: Please fill all the fields.");
        }

        try {
            // Check for conflicts
            $conflict_sql = "SELECT COUNT(*) FROM schedules WHERE day = :day AND period = :period AND classroom = :classroom AND schedule_id != :schedule_id";
            $conflict_stmt = $conn->prepare($conflict_sql);
            $conflict_stmt->bindParam(':day', $day);
            $conflict_stmt->bindParam(':period', $period);
            $conflict_stmt->bindParam(':classroom', $classroom);
            $conflict_stmt->bindParam(':schedule_id', $schedule_id);
            $conflict_stmt->execute();
            $conflict_count = $conflict_stmt->fetchColumn();

            if ($conflict_count > 0) {
                die("Error: The room is already occupied for the selected period on this day.");
            }

            // Update the schedule
            $update_sql = "UPDATE schedules SET day = :day, period = :period, subject = :subject, classroom = :classroom WHERE schedule_id = :schedule_id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':day', $day);
            $update_stmt->bindParam(':period', $period);
            $update_stmt->bindParam(':subject', $subject);
            $update_stmt->bindParam(':classroom', $classroom);
            $update_stmt->bindParam(':schedule_id', $schedule_id);
            if ($update_stmt->execute()) {
                $success = "Schedule updated successfully!";
            } else {
                $error = "Error: Could not update the schedule.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

try {
    // Fetch all sections for the filter dropdown
    $sections_stmt = $conn->query("SELECT id, section_name FROM sections");
    $sections = $sections_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize schedules array
    $schedules = [];

    if (isset($_GET['section_id'])) {
        $section_id = $_GET['section_id'];
        // Fetch schedules for the selected section
        $stmt = $conn->prepare("SELECT schedules.schedule_id, sections.section_name, schedules.day, schedules.period, schedules.subject, rooms.room_no
                                FROM schedules
                                JOIN sections ON schedules.section_id = sections.id
                                JOIN rooms ON schedules.classroom = rooms.room_id
                                WHERE schedules.section_id = :section_id
                                ORDER BY FIELD(schedules.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), schedules.period");
        $stmt->bindParam(':section_id', $section_id);
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error fetching schedules: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>ScheduleHub - Update Schedule</title>
<meta name="description" content="">
<meta name="keywords" content="">
    <title>Delete Schedules - ScheduleHub</title>
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
        .schedule-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .schedule-table tr:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .schedule-table td {
            color: #ffffff; /* White text color */
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
        .form-container select.form-control {
            background-color: rgba(255, 255, 255, 0.1); /* Light semi-transparent background */
            color: #ffffff; /* White text color */
        }
        .form-container select.form-control option {
            background-color: #000000; /* Dark background for options */
            color: #ffffff; /* White text color */
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
                    <li><a href="Home.html" class="active">Home<br></a></li>
                    <li class="dropdown"><a href="#"><span>Create</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="create_schedule.php">Schedule Creation</a></li>
                            <li><a href="create_section.php">Section Creation</a></li>
                        </ul>
                    </li>
                    <li><a href="read_schedule.php">View Schedule</a></li>
                    <li class="dropdown"><a href="#"><span>Manage Schedule</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="update_schedule.php">Update Schedule</a></li>
                            <li><a href="delete_schedule.php">Delete Schedule</a></li>
                        </ul>
                    </li>
                    <li class="dropdown"><a class= "cta-btn d-none d-sm-block" href="#"><span>Your Profile</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="profile.php">Change password</a></li>
                            <li><a href="../controllers/logout.php">Log Out</a></li>
                        </ul>
                    </li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="page-title" data-aos="fade" style="background-image: url(../assets/img/lib_inside.jpeg);">
            <div class="container position-relative">
                <h2>Update Schedule</h2>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a color="white" href="Home.html">Home</a></li>
                        <li class="current">Update Schedule</li>
                    </ol>
                </nav>
            </div>
        </div>
        <section id="events" class="events section" style="background-color:rgba(14, 23, 33, 0.04);">
            <div class="form-container">
                <h1>Select Section</h1>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="GET" action="update_schedule.php">
                    <div class="mb-3">
                        <label for="section_id" class="form-label">Select Section</label>
                        <select class="form-control" id="section_id" name="section_id" required>
                            <option value="">Select a section</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo htmlspecialchars($section['id']); ?>" <?php echo isset($section_id) && $section_id == $section['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['section_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="cta-btn">Filter</button>
                </form>

                <?php if (isset($section_id)): ?>
                    <?php if ($schedules): ?>
                        <h4>Schedules for Section <?php echo htmlspecialchars($section_id); ?></h4>
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>Day</th>
                                    <th>Period</th>
                                    <th>Subject</th>
                                    <th>Room</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>













<tbody>
    <?php foreach ($schedules as $schedule): ?>
        <tr>
            <td><?php echo htmlspecialchars($schedule['section_name']); ?></td>
            <td><?php echo htmlspecialchars($schedule['day']); ?></td>
            <td><?php echo htmlspecialchars($schedule['period']); ?></td>
            <td><?php echo htmlspecialchars($schedule['subject']); ?></td>
            <td><?php echo htmlspecialchars($schedule['room_no']); ?></td>
            <td>
                <button type="button" class="cta-btn btn-primary btn-sm edit-btn" data-schedule-id="<?php echo $schedule['schedule_id']; ?>">Edit</button>
                <button type="button" class="cta-btn btn-secondary btn-sm swap-btn" data-schedule-id="<?php echo $schedule['schedule_id']; ?>">Swap</button>
            </td>
        </tr>
        <tr class="edit-form-row" id="edit-form-<?php echo $schedule['schedule_id']; ?>" style="display: none;">
            <td colspan="6">
                <form method="POST" action="update_schedule.php">
                    <input type="hidden" name="schedule_id" value="<?php echo $schedule['schedule_id']; ?>">
                    <div class="mb-3">
                        <label for="day" class="form-label">Day</label>
                        <select class="form-control" id="day" name="day" required>
                            <option value="Monday" <?php echo $schedule['day'] == 'Monday' ? 'selected' : ''; ?>>Monday</option>
                            <option value="Tuesday" <?php echo $schedule['day'] == 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                            <option value="Wednesday" <?php echo $schedule['day'] == 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                            <option value="Thursday" <?php echo $schedule['day'] == 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                            <option value="Friday" <?php echo $schedule['day'] == 'Friday' ? 'selected' : ''; ?>>Friday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="period" class="form-label">Period</label>
                        <select class="form-control" id="period" name="period" required>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $schedule['period'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <select class="form-control" id="subject" name="subject" required>
                            <option value="oose" <?php echo $schedule['subject'] == 'oose' ? 'selected' : ''; ?>>OOSE</option>
                            <option value="na" <?php echo $schedule['subject'] == 'na' ? 'selected' : ''; ?>>NA</option>
                            <option value="ip2" <?php echo $schedule['subject'] == 'ip2' ? 'selected' : ''; ?>>IP2</option>
                            <option value="spm" <?php echo $schedule['subject'] == 'spm' ? 'selected' : ''; ?>>SPM</option>
                            <option value="os" <?php echo $schedule['subject'] == 'os' ? 'selected' : ''; ?>>OS</option>
                            <option value="vp" <?php echo $schedule['subject'] == 'vp' ? 'selected' : ''; ?>>VP</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="classroom" class="form-label">Classroom</label>
                        <select class="form-control" id="classroom" name="classroom" required>
                            <?php
                            try {
                                $rooms_stmt = $conn->query("SELECT room_id, room_no FROM rooms");
                                while ($room = $rooms_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$room['room_id']}'" . ($schedule['room_no'] == $room['room_no'] ? 'selected' : '') . ">{$room['room_no']}</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error fetching rooms: " . $e->getMessage() . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="cta-btn btn-primary">Update</button>
                </form>
            </td>
        </tr>
        <tr class="swap-form-row" id="swap-form-<?php echo $schedule['schedule_id']; ?>" style="display: none;">
            <td colspan="6">
                <form method="POST" action="update_schedule.php">
                    <input type="hidden" name="schedule_id" value="<?php echo $schedule['schedule_id']; ?>">
                    <div class="mb-3">
                        <label for="swap_subject" class="form-label">Swap Subject With</label>
                        <select class="form-control" id="swap_subject" name="swap_subject" required>
                            <?php foreach ($schedules as $swap_schedule): ?>
                                <?php if ($swap_schedule['schedule_id'] != $schedule['schedule_id']): ?>
                                    <option value="<?php echo $swap_schedule['schedule_id']; ?>">
                                        <?php echo htmlspecialchars($swap_schedule['day']) . ' - Period ' . htmlspecialchars($swap_schedule['period']) . ' - ' . htmlspecialchars($swap_schedule['subject']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="cta-btn btn-secondary">Swap</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

<script>
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-schedule-id');
            const formRow = document.getElementById('edit-form-' + scheduleId);
            formRow.style.display = formRow.style.display === 'none' ? 'table-row' : 'none';
        });
    });

    document.querySelectorAll('.swap-btn').forEach(button => {
        button.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-schedule-id');
            const formRow = document.getElementById('swap-form-' + scheduleId);
            formRow.style.display = formRow.style.display === 'none' ? 'table-row' : 'none';
        });
    });
</script>








                        </table>
                    <?php else: ?>
                        <p>No schedules found for this section.</p>
                    <?php endif; ?>
                <?php endif; ?>
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
                            <li><a href="#">Schedule Editing</a></li>
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