<?php
session_start();

// usertype check
if (!isset($_SESSION['username']) || $_SESSION['usertype'] != 0) {
    header('Location: login_page.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard_admin.css">
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="top-bar">
            <a href="logout.php"><button class="logout-btn">Logout</button></a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                <div class="logo-container">
                    <img src="img/logo.png" alt="Logo" class="logo">
                </div>
               
                <p class="text-lg text-gray-300 mt-1"><?php echo htmlspecialchars($_SESSION['uname']); ?></p>
                <p class="text-lg text-gray-300 mt-1"><?php echo htmlspecialchars($_SESSION['fname']); ?> <?php echo htmlspecialchars($_SESSION['lname']); ?></p>
                <a href="edit_profile_page.php"><button class="edit-profile-btn">Edit profile</button></a>
                <hr class="divider">

                <!-- Buttons Section -->
                <div class="buttons-container">
                    <a href="view_clients_page.php"><button class="action-btn">View Clients</button></a>
                    <a href="view_users_admin.php"><button class="action-btn">View Users</button></a>
                    <a href="view_matters_page.php"><button class="action-btn">View Matters</button></a>
                    <a href="audit_log_page.php"><button class="action-btn">View Audit Log</button></a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
