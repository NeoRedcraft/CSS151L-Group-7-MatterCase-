<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/edit_user.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/audit_log.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php");

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login_page.php');
    exit();
}
$usertype= $_SESSION['usertype'];
// Connect to the database
$conn = connectToDatabase();

// Determine the user ID to edit
if (isset($_GET['id']) && isAllowed($_SESSION['usertype'])) {
    // Admin can edit any profile
    $edit_user_id = $_GET['id'];
} else {
    // Regular users can only edit their own profile
    $edit_user_id = $_SESSION['id'];
}

// Check if the logged-in user has permission to edit the profile
if (!canEditProfile($_SESSION['id'], $_SESSION['usertype'], $edit_user_id)) {
    echo "You are not authorized to edit this profile.";
    exit();
}

// Fetch the user data to edit
$user_data = fetchUserData($conn, $edit_user_id);
if (!$user_data) {
    echo "User not found.";
    exit();
}

// Decrypt the data
$first_name = decryptData($user_data['first_name'], $key, $method);
$last_name = decryptData($user_data['last_name'], $key, $method);
$email = decryptData($user_data['email'], $key, $method);
$username = $user_data['username'];
$pass = decryptData($user_data['pass'], $key, $method);

// Handle form submission
if (isset($_POST['update'])) {
    $new_first_name = $_POST['first_name'];
    $new_last_name = $_POST['last_name'];
    $new_email = $_POST['email'];
    $new_username = $_POST['username'];
    $new_pass = $_POST['pass'];

    // Check if the new email is unique (if it has changed)
    if ($new_email !== $email && !isEmailUnique($conn, $new_email, $key, $method)) {
        echo "Error: Email already exists. Please use a different email address.";
    } else {
        // Compare old and new data to log changes
        $changes = [];
        if ($new_first_name !== $first_name) {
            $changes[] = "First Name: '$first_name' to '$new_first_name'";
        }
        if ($new_last_name !== $last_name) {
            $changes[] = "Last Name: '$last_name' to '$new_last_name'";
        }
        if ($new_email !== $email) {
            $changes[] = "Email: '$email' to '$new_email'";
        }
        if ($new_username !== $username) {
            $changes[] = "Username: '$username' to '$new_username'";
        }
        if ($new_pass !== $pass) {
            $changes[] = "Password: Updated";
        }

        // Log changes if any
        if (!empty($changes)) {
            $action = "Updated user ID $edit_user_id: " . implode(", ", $changes);
            logAction($conn, $_SESSION['id'], $action, $key, $method);
        }

        // Update the user data in the database
        if (updateUserProfile($conn, $edit_user_id, $new_first_name, $new_last_name, $new_email, $new_username, $new_pass, $key, $method)) {
            echo "Profile updated successfully.";
        } else {
            echo "Error updating profile.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User Data</title>
    <link rel="stylesheet" href="edit_profile_page.css">
</head>
<body>

    <a href="<?php
        switch ($usertype) {
            case 0: echo 'dashboard_admin.php'; break;
            case 1: echo 'dashboard_partner.php'; break;
            case 2: echo 'dashboard_lawyer.php'; break;
            case 3: echo 'dashboard_paralegal.php'; break;
            case 4: echo 'dashboard_messenger.php'; break;
            default: echo 'login_page.php'; break;
        }
    ?>" class="back-link">Back to Dashboard</a>

    <div class="container">
        <img src="img/logo.png" class="logo" alt="Logo">
        <h2>Edit User Profile</h2>

        <form method="post" action="edit_profile_page.php">
            <div class="input-box">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
            </div>

            <div class="input-box">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
            </div>

            <div class="input-box">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="input-box">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="input-box">
                <label for="password">Password</label>
                <input type="password" id="password" name="pass" placeholder="Enter new password (leave blank to keep current)">
            </div>

            <input type="hidden" name="id" value="<?php echo $edit_user_id; ?>">

            <button type="submit" name="update">Update Profile</button>
        </form>
    </div>

</body>
</html>
