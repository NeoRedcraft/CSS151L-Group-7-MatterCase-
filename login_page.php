<?php
// Include the login logic
include_once($_SERVER['DOCUMENT_ROOT'] . "/Mattercase/Functions/login.php");
?>

<!DOCTYPE html>
<html>
<head>  
    <title>Login</title>
    <link rel="stylesheet" href="login_page.css">
</head>
<body>
  
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    <div class="login-container">
        <div class="logo">
            <img src="img/logo.png" alt="MatterCase Logo">
        </div>
        <div class="login-form">
            <p>Welcome</p>
            <input type="text" name="username" placeholder="Email or phone number">
            <input type="password" name="password" placeholder="Enter password">
            <button type="submit">Sign in</button>
        </div>
    </div>
    <div class="right-panel"></div>
    </form>
</body>
</html>