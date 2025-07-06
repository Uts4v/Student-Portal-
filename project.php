<?php
// config.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_portal";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();
?>

<!-- index.php -->
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
                <div class="feature-icon">👤</div>
                <h3>Profile Management</h3>
                <p>Update your personal information, upload profile pictures, and keep your details current.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>Course Information</h3>
                <p>Access your course details and academic information in one convenient location.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure Access</h3>
                <p>Your data is protected with secure login and encrypted information storage.</p>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>

<!-- login.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="login-section">
            <h2>Student Login</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
            </form>
            
            <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>

<!-- dashboard.php -->
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//userdata
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    
    //profile upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_name = time() . '_' . $_FILES['profile_pic']['name'];
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            $profile_pic = $file_name;
        }
    } else {
        $profile_pic = $user['profile_pic'];
    }
    
    // update database
    $stmt = $pdo->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, course = ?, profile_pic = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $phone, $course, $profile_pic, $_SESSION['user_id']]);
    
    $success = "Profile updated successfully!";
    
    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="dashboard">
            <div class="profile-header">
                <div class="profile-pic">
                    <?php if ($user['profile_pic']): ?>
                        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile Picture">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($user['course']); ?></p>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <h3>Update Profile</h3>
            <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="update-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($user['course']); ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="profile_pic">Profile Picture</label>
                    <div class="file-input">
                        <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                        <span>Click to upload new profile picture</span>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <button type="submit" class="update-btn">Update Profile</button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>

<!-- register.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO students (full_name, email, phone, course, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $phone, $course, $password]);
        
        $success = "Account created successfully! You can now login.";
    } catch(PDOException $e) {
        $error = "Email already exists or database error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="login-section">
            <h2>Create Account</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Create Account</button>
            </form>
            
            <p class="register-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>

<!-- header.php -->
<header>
    <nav>
        <a href="index.php" class="logo">Student Portal</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- footer.php  -->
<footer>
    <div class="footer-content">
        <p>&copy; 2025 Student Portal. Created by [Your Name Here]</p>
    </div>
</footer>

<!-- logout.php --->
<?php
session_start();
session_destroy();
header("Location: index.php");
exit();
?>

<!-- styles.css --->
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    color: #333;
}

/* Header Styles */
header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.logo {
    font-size: 1.8rem;
    font-weight: bold;
    color: #5a67d8;
    text-decoration: none;
}

.nav-links {
    display: flex;
    list-style: none;
    gap: 2rem;
}

.nav-links a {
    text-decoration: none;
    color: #4a5568;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-links a:hover {
    background: #5a67d8;
    color: white;
    transform: translateY(-2px);
}

/* Main Content */
main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.hero-section {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    margin-bottom: 3rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.hero-section h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #2d3748;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.hero-section p {
    font-size: 1.2rem;
    color: #718096;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.cta-button {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 50px;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

/* Features Section */
.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.feature-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.feature-card h3 {
    color: #2d3748;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.feature-card p {
    color: #718096;
    line-height: 1.6;
}

/* Login Form Section */
.login-section {
    background: rgba(255, 255, 255, 0.95);
    padding: 3rem;
    border-radius: 20px;
    max-width: 500px;
    margin: 2rem auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.login-section h2 {
    text-align: center;
    color: #2d3748;
    margin-bottom: 2rem;
    font-size: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #4a5568;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #5a67d8;
    box-shadow: 0 0 0 3px rgba(90, 103, 216, 0.1);
}

.login-btn, .update-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.login-btn:hover, .update-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.register-link {
    text-align: center;
    margin-top: 1rem;
    color: #718096;
}

.register-link a {
    color: #5a67d8;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

/* Dashboard Styles */
.dashboard {
    background: rgba(255, 255, 255, 0.95);
    padding: 3rem;
    border-radius: 20px;
    margin: 2rem auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.profile-pic {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin-right: 2rem;
    overflow: hidden;
}

.profile-pic img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info h2 {
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.profile-info p {
    color: #718096;
    margin-bottom: 0.3rem;
}

.update-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-top: 2rem;
}

.update-form .form-group {
    margin-bottom: 1rem;
}

.full-width {
    grid-column: 1 / -1;
}

.file-input {
    position: relative;
    overflow: hidden;
    display: inline-block;
    background: #f7fafc;
    border: 2px dashed #cbd5e0;
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.file-input:hover {
    background: #edf2f7;
    border-color: #5a67d8;
}

.file-input input[type=file] {
    position: absolute;
    left: -9999px;
}

/* Messages */
.error-message {
    background: #fed7d7;
    color: #c53030;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    text-align: center;
}

.success-message {
    background: #c6f6d5;
    color: #25855a;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    text-align: center;
}

/* Footer */
footer {
    background: rgba(255, 255, 255, 0.9);
    padding: 2rem 0;
    margin-top: 3rem;
    text-align: center;
    color: #718096;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .nav-links {
        gap: 1rem;
    }
    
    .hero-section h1 {
        font-size: 2rem;
    }
    
    .features {
        grid-template-columns: 1fr;
    }
    
    .update-form {
        grid-template-columns: 1fr;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-pic {
        margin-right: 0;
        margin-bottom: 1rem;
    }
}

/* SQL Database Schema */
/*
CREATE DATABASE student_portal;
USE student_portal;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    course VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create uploads directory manually
*/