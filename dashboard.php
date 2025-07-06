<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'sidebar.php';

//take user data
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: logout.php");
    exit();
}

// account delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    $confirmation = $_POST['confirmation'];
    
    if ($confirmation === 'DELETE') {
        // Delete user's profile picture if exists
        if ($user['profile_pic'] && file_exists('uploads/' . $user['profile_pic'])) {
            unlink('uploads/' . $user['profile_pic']);
        }
        
        // Delete user from database
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Destroy session and redirect to login
        session_destroy();
        header("Location: login.php?deleted=1");
        exit();
    } else {
        $delete_error = "Please type 'DELETE' exactly to confirm account deletion.";
    }
}

// this will handel profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['change_password']) && !isset($_POST['delete_account'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    $profile_pic = $user['profile_pic']; 

    // for profile pic
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir);
        }
        $file_name = time() . '_' . $_FILES['profile_pic']['name'];
        $upload_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            $profile_pic = $file_name;
        } else {
            $profile_pic = $user['profile_pic'];
        }
    } else {
        $profile_pic = $user['profile_pic'];
    }

    // this will update profiel pic
    $stmt = $pdo->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, course = ?, profile_pic = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $phone, $course, $profile_pic, $_SESSION['user_id']]);
    $success = "Profile updated successfully!";
    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        header("Location: logout.php");
        exit();
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (password_verify($current_password, $user['password'])) {
        // Hash new password and update
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
        $stmt->execute([$new_password_hashed, $_SESSION['user_id']]);
        $password_success = "Password changed successfully!";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } else {
        $password_error = "Current password is incorrect.";
    }
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
            
            <?php if (isset($password_error)): ?>
                <div class="error-message"><?php echo $password_error; ?></div>
            <?php endif; ?>
            <?php if (isset($password_success)): ?>
                <div class="success-message"><?php echo $password_success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($delete_error)): ?>
                <div class="error-message"><?php echo $delete_error; ?></div>
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
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                </div>
                
                <div class="form-group full-width">
                    <button type="submit" class="update-btn">Update Profile</button>
                </div>
            </form>
            
            <h3>Change Password</h3></br>
            <form method="POST" action="dashboard.php" class="change-password-form">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group full-width">
                    <button type="submit" name="change_password" class="update-btn">Change Password</button>
                </div>
            </form>
            
            <h3>Delete Account</h3>
            <div class="danger-zone">
                <p>This action cannot be undone. All your data will be permanently deleted.</p>
                <button type="button" class="delete-account-btn" onclick="showDeletePopup()">Delete Account</button>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <!-- Delete Account Popup -->
    <div id="deletePopup" class="popup-overlay">
        <div class="popup-content">
            <h3>Delete Account</h3>
            <p>This action cannot be undone. All your data will be permanently deleted.</p>
            <p>To confirm, please type <strong>DELETE</strong> in the field below:</p>
            
            <form method="POST" action="dashboard.php" class="delete-form">
                <div class="form-group">
                    <input type="text" id="confirmation" name="confirmation" placeholder="Type DELETE to confirm" required>
                </div>
                <div class="popup-buttons">
                    <button type="submit" name="delete_account" class="confirm-delete-btn">Delete Account</button>
                    <button type="button" class="cancel-btn" onclick="hideDeletePopup()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showDeletePopup() {
            document.getElementById('deletePopup').style.display = 'flex';
            document.getElementById('confirmation').focus();
        }
        
        function hideDeletePopup() {
            document.getElementById('deletePopup').style.display = 'none';
            document.getElementById('confirmation').value = '';
        }
        
        // Close popup when clicking outside
        document.getElementById('deletePopup').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeletePopup();
            }
        });
        
        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideDeletePopup();
            }
        });
    </script>
</body>
</html>
