<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: login.php'); 
    exit();
}

include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/add_user.php");

// Check if the form was submitted
if (isset($_POST['Submit'])) {
    // Retrieve the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $usertype = $_POST['usertype'];
    $username = $_POST['username'];
    $actor_id = $_SESSION['id'];

    // Call the addUser function
    $result = addUser($conn, $first_name, $last_name, $email, $pass, $usertype, $username, $key, $method, $actor_id);

    // Display the result
    echo $result;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Users</title>
    <link rel="stylesheet" href="add_user_page.css">
</head>
<body>

    <a href="login.php" class="home-link">Home</a>
    <img src="img/logo.png" class="logo" alt="">
    <div class="container">
        <h2>Add New User</h2>

        <form class="form-container" action="add_user_page.php" method="post">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="username">User Name</label>
            <input type="text" id="username" name="username" required>

            <label for="usertype">User Type</label>
            <select name="usertype" id="usertype" required>
                <option value="0">Administrator</option>
                <option value="1">Partner</option>
                <option value="2">Lawyer</option>
                <option value="3">Paralegal</option>
                <option value="4">Messenger</option>
            </select>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="pass" required>

            <button type="submit">Add</button>
        </form>
    </div>

</body>
</html>
