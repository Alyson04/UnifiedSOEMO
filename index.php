<?php
session_start();
require 'config/db_conn.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';

if ($student_id) {
    $sql_student = "SELECT fullName FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();
    if ($result_student->num_rows > 0) {
        $student_name = ucwords(strtolower($result_student->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified SOEMO - Welcome</title>
    <link rel="stylesheet" href="studentdashboard_styles.css">
</head>
<body>
    <header>
        <h1>Unified SOEMO</h1>
        <nav>
            <ul>
                <li><a href="#student-dashboard">Student Dashboard</a></li>
                <li><a href="#organizations">Organizations</a></li>
                <li><a href="#about-us">About Us</a></li>
                <?php if (!$student_id): ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php else: ?>
                    <li>Welcome, <?= htmlspecialchars($student_name) ?></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section id="student-dashboard">
        <h2>Student Dashboard</h2>
        <p>Explore the latest student updates and activities. Available for preview by everyone.</p>
    </section>

    <section id="organizations">
        <h2>Organizations</h2>
        <p>Discover active student organizations and get involved in events and initiatives.</p>
    </section>

    <section id="about-us">
        <h2>About Us</h2>
        <p>We connect students and organizations to promote growth and engagement within the community.</p>
    </section>
</body>
</html>
