<html>
<head>
	<title>Add Users</title>
</head>

<body>
	<a href="index.php">Home</a>
	<br/><br/>

	<form action="add.php" method="post" name="form1">
		<table width="25%" border="0">
			<tr> 
				<td>FirstName</td>
				<td><input type="text" name="first_name"></td></tr>

				<tr>	
					<td>LastName</td>
				<td><input type="text" name="last_name"></td>
			</tr>

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
				<td></td>
				<td><input type="submit" name="Submit" value="Add"></td>
			</tr>
		</table>
	</form>
	
	<?php

	// Check If form submitted, insert form data into users table.
	if(isset($_POST['Submit'])) {
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$email = $_POST['email'];
		$mobile = $_POST['mobile'];
		
		// include database connection file
		include_once("config.php");
				
		// Insert user data into table
		$result = mysqli_query($conn, "INSERT INTO users(first_name,last_name,email,mobile) VALUES('$first_name','$last_name','$email','$mobile')");
		
		// Show message when user added
		echo "User added successfully. <a href='viewusers.php'>View Users</a>";
	}
	?>
</body>
</html>
