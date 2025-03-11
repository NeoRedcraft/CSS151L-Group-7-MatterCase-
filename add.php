<?php
include_once("encryption.php");
include_once("config.php"); // Include the database connection file
include_once("auditlog.php"); // Include the audit log module

//session check
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: login.php'); 
    exit();
}

// Check if the form was submitted
if (isset($_POST['Submit'])) {
    // Retrieve the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $pass = $_POST['pass'];
    $usertype = $_POST['usertype'];
    $username = $_POST['username'];

    // Encrypt the form data using the encryptData function
    $encrypted_first_name = encryptData($first_name, $key, $method);
    $encrypted_last_name = encryptData($last_name, $key, $method);
    $encrypted_email = encryptData($email, $key, $method);
    $encrypted_mobile = encryptData($mobile, $key, $method);
    $encrypted_pass = encryptData($pass, $key, $method);
    $encrypted_usertype = encryptData($usertype, $key, $method);

    // SQL injection prevention using prepared statements
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, mobile, pass, usertype, username) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssis", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $encrypted_mobile, $encrypted_pass, $encrypted_usertype, $username);

    if ($stmt->execute()) {
        $new_user_id = $stmt->insert_id;
        $actor_id = $_SESSION['id'];

        // Log the action in the audit log
        $action = "Added new user with ID: $new_user_id, Username: $username, Usertype: $usertype";
        logAction($conn, $actor_id, $action);

        echo "User added successfully. <a href='viewusers.php'>View Users</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<html>
<head>
    <title>Add Users</title>
</head>
<body>
    <a href="login.php">Home</a>
    <br/><br/>

    <form action="add.php" method="post" name="form1">
        <table width="25%" border="0">
            <tr> 
                <td>FirstName</td>
                <td><input type="text" name="first_name"></td>
            </tr>
            <tr> 
                <td>LastName</td>
                <td><input type="text" name="last_name"></td>
            </tr>
            <tr> 
                <td>UserName</td>
                <td><input type="text" name="username"></td>
            </tr>
            <tr>
                <td>User Type</td>
                <td>
                    <select name="usertype" id="usertype">
                        <option value="0">Administrator</option>
                        <option value="1">Partner</option>
                        <option value="2">Lawyer</option>
                        <option value="3">Paralegal</option>
                        <option value="4">Messenger</option>
                    </select>
                </td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="text" name="email"></td>
            </tr>
            <tr> 
                <td>Mobile</td>
                <td><input type="text" name="mobile"></td>
            </tr>
            <tr> 
                <td>Password</td>
                <td><input type="text" name="pass"></td>
            </tr>
            <tr> 
                <td></td>
                <td><input type="submit" name="Submit" value="Add"></td>
            </tr>
        </table>
    </form>
</body>
</html>