<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Database credentials
    $host = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $database = 'mattercase';
    
    // Connect to the database
    $conn = mysqli_connect($host, $db_username, $db_password, $database);
    
    // Check for errors
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }
    
    // Check if user exists in database
    $query = "SELECT * FROM users WHERE email = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($row["is_admin"] == 1) {
        $_SESSION['username'] = $username;
        header('Location: viewusers.php');
    } else {
        echo "User is not an administrator.";
        $_SESSION['username'] = $username;
        header('Location: INSERT DASHBOARD HERE');
    }
} else {
    echo "Invalid username or password.";
}
// Close the database connection
mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>  
    <title>Login</title>
</head>
<body>
    <h1>Login Now</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label>Username (must be email):</label>
        <input type="text" name="username"><br><br>
        <label>Password:</label>
        <input type="password" name="password"><br><br>
        <input type="submit" value="Login"><br><br>
    </form>
</body>
</html>
