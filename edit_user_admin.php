<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/audit_log.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/email_unique.php");

// Check if ID is provided in the URL
if (!isset($_GET['id'])) {
    echo "User ID not provided.";
    exit();
}

$id = $_GET['id'];

// Fetch user data based on ID
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);

    // Decrypt the data
    $first_name = decryptData($user_data['first_name'], $key, $method);
    $last_name = decryptData($user_data['last_name'], $key, $method);
    $email = decryptData($user_data['email'], $key, $method);
    $username = $user_data['username'];
    $pass = decryptData($user_data['pass'], $key, $method);
} else {
    echo "User not found.";
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $new_first_name = $_POST['first_name'];
    $new_last_name = $_POST['last_name'];
    $new_email = $_POST['email'];
    $new_username = $_POST['username'];
    $new_pass = $_POST['pass'];

    // Check if email is unique
    if (!isEmailUnique($conn, $new_email, $key, $method)) {
        echo "Error: Email already exists. Please use a different email address.";
    } else {
        // Encrypt new data
        $encrypted_first_name = encryptData($new_first_name, $key, $method);
        $encrypted_last_name = encryptData($new_last_name, $key, $method);
        $encrypted_email = encryptData($new_email, $key, $method);
        $encrypted_pass = encryptData($new_pass, $key, $method);

        // Log changes
        $changes = [];
        if ($new_first_name !== $first_name) $changes[] = "First Name updated";
        if ($new_last_name !== $last_name) $changes[] = "Last Name updated";
        if ($new_email !== $email) $changes[] = "Email updated";
        if ($new_username !== $username) $changes[] = "Username changed from '$username' to '$new_username'";
        if ($new_pass !== $pass) $changes[] = "Password updated";

        if (!empty($changes)) {
            $action = "Updated user ID $id: " . implode(", ", $changes);
            logAction($conn, $_SESSION['id'], $action);
        }

        // Update the database
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=?, pass=? WHERE id=?");
        $stmt->bind_param("sssssi", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $new_username, $encrypted_pass, $id);

        if ($stmt->execute()) {
            header("Location: view_users_admin.php");
            exit();
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Data</title>
    <link rel="stylesheet" href="edit_user_admin.css">
</head>
<body>

    <!-- Home Link -->
    <a href="view_users_admin.php" class="home-link">Home</a>
    <img src="img/logo.png" class="logo" alt="Logo">

    <!-- Form Container -->
    <div class="container">
        <h2>Edit User Data</h2>
        <form name="update_user" method="post" action="edit_user.php?id=<?php echo $id; ?>" class="form-container">
            
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

            <label for="pass">Password</label>
            <input type="password" id="pass" name="pass" value="<?php echo htmlspecialchars($pass); ?>" required>

            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <button type="submit" name="update">Update</button>
        </form>
    </div>

</body>
</html>
