<?php
session_start();
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="hero-section">
            <h1>Welcome to Student Portal</h1>
            <p>Your gateway to academic excellence and personal growth. Manage your profile, track your progress, and stay connected with your educational journey.</p>
            <a href="login.php" class="cta-button">Get Started</a>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3>Profile Management</h3>
                <p>Update your personal information, upload profile pictures, and keep your details current.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3>Course Information</h3>
                <p>Access your course details and academic information in one convenient location.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3>Secure Access</h3>
                <p>Your data is protected with secure login and encrypted information storage.</p>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>
