<!-- sidebar.php -->
<aside class="sidebar">
    <div class="logo" style="margin-bottom:2rem; font-size:1.5rem; font-weight:bold; color:#2563eb;">Student Portal</div>
    <ul class="nav-links">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="index.php">Home</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</aside>