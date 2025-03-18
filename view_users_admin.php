<?php
session_start();
// Create database connection using config file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php");

// Fetch all users data from database
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
$usertype = $_SESSION['usertype'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Users</title>
    <link rel="stylesheet" href="view_users_admin.css">
</head>
<body>
    <img src="img/logo.png" class="logo">
    <div class="container">
        <h1>View Users</h1>
        <form method="POST" action="add_user_page.php">
            <input type="submit" name="submit" value="Add New User" class="btn add-btn"> <br /><br />
        </form>

        <table>
            <thead>
                <tr>
                    <th>Firstname</th> 
                    <th>Lastname</th> 
                    <th>Username</th> 
                    <th>Email</th> 
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php  
                while($user_data = mysqli_fetch_array($result)) {         
                    // Decrypt the data
                    $firstname = decryptData($user_data['first_name'], $key, $method);
                    $lastname = decryptData($user_data['last_name'], $key, $method);
                    $username = $user_data['username'];
                    $email = decryptData($user_data['email'], $key, $method);

                    echo "<tr>";
                    echo "<td>".$firstname."</td>";
                    echo "<td>".$lastname."</td>";
                    echo "<td>".$username."</td>";
                    echo "<td>".$email."</td>";    
                    echo "<td>
                            <a href='edit_profile_page.php?id={$user_data['id']}' class='edit-btn'>Edit</a> | 
                            <a href='delete_user.php?id={$user_data['id']}' class='edit-btn'>Delete</a>
                          </td></tr>";        
                }
                ?>
            </tbody>
        </table>
        
        <a href="<?php
            // Redirect to the appropriate dashboard based on usertype
            switch ($usertype) {
                case 0: echo 'dashboard_admin.php'; break;
                case 1: echo 'dashboard_partner.php'; break;
                case 2: echo 'dashboard_lawyer.php'; break;
                case 3: echo 'dashboard_paralegal.php'; break;
                case 4: echo 'dashboard_messenger.php'; break;
                default: echo 'login_page.php'; break;
            }
        ?>" class="btn back-btn">Back to Dashboard</a>
        <a href="logout.php" class="btn back-btn">Log out</a>
    </div>
</body>
</html>