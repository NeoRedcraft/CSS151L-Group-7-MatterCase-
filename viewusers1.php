<?php
/**
Crud operation by: Felipe Ante Jr 2023
**/

// Create database connection using config file
include_once("config.php");

// Fetch all users data from database
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<html>
<head>    
    <title>Homepage</title>
</head>

<body>
    <h2>Welcome Employee</h2>


    <table width='80%' border=1>

    <tr>
        <th>Firstname</th> <th>Lastname</th> <th>Mobile</th> <th>Email</th>
    </tr>
    <?php  
    while($user_data = mysqli_fetch_array($result)) {         
        echo "<tr>";
        echo "<td>".$user_data['first_name']."</td>";
        echo "<td>".$user_data['last_name']."</td>";
        echo "<td>".$user_data['mobile']."</td>";
        echo "<td>".$user_data['email']."</td>";    
           
    }
    ?>
    </table>


<a href="logout.php">Log out </a>


</body>
</html>
