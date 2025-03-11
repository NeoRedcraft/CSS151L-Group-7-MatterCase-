<?php

include_once("config.php");
include_once("encryption.php");
include_once("decrypt.php");
// Check if form is submitted for user update, then redirect to homepage after update
if(isset($_POST['update']))
{	
	$id = $_POST['id'];
	
	$first_name=$_POST['first_name'];
	$last_name=$_POST['last_name'];
	$email=$_POST['email'];
	$username=$_POST['username'];
	$mobile=$_POST['mobile'];
	$pass=$_POST['pass'];
		
	// update user data
	$result = mysqli_query($conn, "UPDATE users SET 
		first_name='$first_name',
		last_name='$last_name', 
		email='$email',
		username='$username',
		mobile='$mobile',
		pass='$pass' 
	WHERE id=$id");
	
	// Redirect to homepage to display updated user in list
	header("Location: viewusers.php");
}
?>
<?php
// Display selected user data based on id
// Getting id from url
$id = $_GET['id'];

// Fetech user data based on id
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");

while($user_data = mysqli_fetch_array($result))
{
	$first_name = decryptData($user_data['first_name'], $key, $method);
	$last_name = decryptData($user_data['last_name'], $key, $method);
	$email = decryptData($user_data['email'], $key, $method);
	$username = $user_data['username'];
	$mobile = decryptData($user_data['mobile'], $key, $method);
	$pass = decryptData($user_data['pass'], $key, $method);


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
				<td><input type="text" name="first_name" value=<?php echo $first_name;?>></td>
			</tr>
			<tr> 
				<td>Last Name</td>
				<td><input type="text" name="last_name" value=<?php echo $last_name;?>></td>
			</tr>
			<tr> 
				<td>Email</td>
				<td><input type="text" name="email" value=<?php echo $email;?>></td>
			</tr>
			<tr> 
				<td>Username</td>
				<td><input type="text" name="username" value=<?php echo $username;?>></td>
			</tr>
			<tr> 
				<td>Mobile</td>
				<td><input type="text" name="mobile" value=<?php echo $mobile;?>></td>
			</tr>
			<tr> 
				<td>pass</td>
				<td><input type="text" name="pass" value=<?php echo $pass;?>></td>
			</tr>
			<tr>
				<td><input type="hidden" name="id" value=<?php echo $_GET['id'];?>></td>
				<td><input type="submit" name="update" value="Update"></td>
			</tr>
		</table>
	</form>
</body>
</html>

