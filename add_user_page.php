<?php
//include_once ("MATTER_CASE_FEnd/html/Add_newusers.html");
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/config.php"); 
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/auditlog.php"); 

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
    $pass = $_POST['pass'];
    $usertype = $_POST['usertype'];
    $username = $_POST['username'];

    // Encrypttion
    $encrypted_first_name = encryptData($first_name, $key, $method);
    $encrypted_last_name = encryptData($last_name, $key, $method);
    $encrypted_email = encryptData($email, $key, $method);
    $encrypted_pass = encryptData($pass, $key, $method);
    $encrypted_usertype = encryptData($usertype, $key, $method);

    // SQL injection prevention
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, pass, usertype, username) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $encrypted_pass, $encrypted_usertype, $username);

    if ($stmt->execute()) {
        $new_user_id = $stmt->insert_id;
        $actor_id = $_SESSION['id'];

        // Log the action in the audit log
        $action = "Added new user with ID: $new_user_id, Username: $username, Usertype: $usertype";
        logAction($conn, $actor_id, $action);

        echo "User added successfully. <a href='viewusers.php'>View Users</a>";
    } else {
        // Encrypttion
        $encrypted_first_name = encryptData($first_name, $key, $method);
        $encrypted_last_name = encryptData($last_name, $key, $method);
        $encrypted_email = encryptData($email, $key, $method);
        $encrypted_pass = encryptData($pass, $key, $method);

        // SQL injection prevention
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, pass, usertype, username) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssis", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $encrypted_pass, $usertype, $username);

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

