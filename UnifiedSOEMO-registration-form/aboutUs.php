<?php
// About Us Page in PHP - Unified SOEMO
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Unified SOEMO</title>
    <link rel="stylesheet" href="css/aboutUs.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <nav>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Organization</a></li>
                    <li><a href="#">Events</a></li>
                    <li><a href="#">About Us</a></li>
                </ul>
            </nav>
        </header>

        <section class="about-section">
            <h1>About Us</h1>
            <div class="about-text">
                <p><?php echo "Unified SOEMO is a dynamic platform designed to connect students and organizations, fostering collaboration and engagement. Our goal is to empower individuals to discover opportunities, participate in meaningful activities, and build lasting connections within their academic and social communities."; ?></p>
            </div>
            <div class="images">
                <img src="image51.png" alt="PUP Campus" class="about-image">
                <img src="studentorg.png" alt="Students" class="about-image">
            </div>
        </section>

        <section class="mission-vision">
            <div class="mission">
                <h2>Mission</h2>
                <p><?php echo "At Unified SOEMO, our mission is to empower students by connecting them with organizations that foster growth, learning, and community involvement. We strive to create an inclusive platform where every student can easily discover and engage with groups that align with their passions, helping them maximize their potential and enrich their campus experience."; ?></p>
            </div>
            <div class="vision">
                <h2>Vision</h2>
                <p><?php echo "Our vision is to be the leading platform for student engagement, fostering a vibrant and interconnected campus community. We aim to inspire students to build meaningful connections, develop lifelong skills, and contribute to a culture of collaboration and inclusivity within their universities and beyond."; ?></p>
            </div>
        </section>

        <section class="contact-section">
            <h2>Contact Us</h2>
            <p><?php echo "We'd love to hear from you! Whether you have questions, feedback, or need assistance, the Unified SOEMO team is here to help. Feel free to reach out to us through any of the following channels:"; ?></p>
            <p>Email: <a href="mailto:support@unsoemo.com">support@unsoemo.com</a></p>
            <p>Social Media:</p>
            <ul>
                <li>Facebook: <a href="#">UnifiedSoemo Official</a></li>
                <li>Twitter: <a href="#">@UnifiedSoemo</a></li>
                <li>Instagram: <a href="#">@UnifiedSoemo</a></li>
            </ul>
        </section>

    </div>
</body>
</html> 