<?php
include_once("config.php");
include_once("encryption.php");
include_once("decrypt.php");

// Check if form is submitted for user update
if (isset($_POST['update'])) {
    $id = $_POST['id'];

    // Retrieve and sanitize form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $mobile = $_POST['mobile'];
    $pass = $_POST['pass'];

    // Encrypt the updated data
    $encrypted_first_name = encryptData($first_name, $key, $method);
    $encrypted_last_name = encryptData($last_name, $key, $method);
    $encrypted_email = encryptData($email, $key, $method);
    $encrypted_mobile = encryptData($mobile, $key, $method);
    $encrypted_pass = encryptData($pass, $key, $method);

    // SQL injection prevention code baby google prepared statements
    $stmt = $conn->prepare("UPDATE users SET 
        first_name=?, 
        last_name=?, 
        email=?, 
        username=?, 
        mobile=?, 
        pass=? 
        WHERE id=?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssssi", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $username, $encrypted_mobile, $encrypted_pass, $id);

    if ($stmt->execute()) {
        header("Location: viewusers.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

// Display selected user data based on id
$id = $_GET['id'];

// Fetch user data based on id
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_array($result);

    // Decrypt the data
    $first_name = decryptData($user_data['first_name'], $key, $method);
    $last_name = decryptData($user_data['last_name'], $key, $method);
    $email = decryptData($user_data['email'], $key, $method);
    $username = $user_data['username'];
    $mobile = decryptData($user_data['mobile'], $key, $method);
    $pass = decryptData($user_data['pass'], $key, $method);
} else {
    echo "User not found.";
    exit();
}
?>

<html>
<head>	
    <title>Edit User Data</title>
</head>
<body>
    <a href="viewusers.php">Home</a>
    <br/><br/>
    
    <form name="update_user" method="post" action="edit.php">
        <table border="0">
            <tr> 
                <td>First Name</td>
                <td><input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>"></td>
            </tr>
            <tr> 
                <td>Last Name</td>
                <td><input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>"></td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"></td>
            </tr>
            <tr> 
                <td>Username</td>
                <td><input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>"></td>
            </tr>
            <tr> 
                <td>Mobile</td>
                <td><input type="text" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>"></td>
            </tr>
            <tr> 
                <td>Password</td>
                <td><input type="text" name="pass" value="<?php echo htmlspecialchars($pass); ?>"></td>
            </tr>
            <tr>
                <td><input type="hidden" name="id" value="<?php echo $_GET['id']; ?>"></td>
                <td><input type="submit" name="update" value="Update"></td>
            </tr>
        </table>
    </form>
</body>
</html>