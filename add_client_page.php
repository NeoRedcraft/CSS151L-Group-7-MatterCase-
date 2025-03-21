<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/decrypt.php"); // Include decryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php"); // Include encryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_clients_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all matters for the multi-select dropdown
$matters_query = "SELECT matter_id, title FROM matters";
$matters_result = $conn->query($matters_query);
$matters = $matters_result->fetch_all(MYSQLI_ASSOC);

// Decrypt matter titles for display
foreach ($matters as &$matter) {
    $matter['title'] = decryptData($matter['title'], $key, $method);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $client_name = $_POST['client_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $profile_picture = $_POST['profile_picture'];
    $matter_ids = $_POST['matter_ids']; // Array of selected matter IDs

    // Encrypt sensitive data
    $encrypted_client_name = encryptData($client_name, $key, $method);
    $encrypted_email = encryptData($email, $key, $method);
    $encrypted_address = encryptData($address, $key, $method);
    $encrypted_profile_picture = encryptData($profile_picture, $key, $method);

    // Insert the new client into the database
    $stmt = $conn->prepare("INSERT INTO clients (client_name, email, address, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $encrypted_client_name, $encrypted_email, $encrypted_address, $encrypted_profile_picture);

    if ($stmt->execute()) {
        $client_id = $stmt->insert_id; // Get the ID of the newly inserted client

        // Insert selected matters into the client_matters table
        if (!empty($matter_ids)) {
            $insert_matters_stmt = $conn->prepare("INSERT INTO client_matters (client_id, matter_id) VALUES (?, ?)");
            foreach ($matter_ids as $matter_id) {
                $insert_matters_stmt->bind_param("ii", $client_id, $matter_id);
                $insert_matters_stmt->execute();
            }
            $insert_matters_stmt->close();
        }

        // Redirect back to the view clients page with a success message
        header('Location: view_clients_page.php?success=1');
        exit();
    } else {
        // Redirect back to the add client page with an error message
        header('Location: add_client_page.php?error=1');
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matter Case</title>
    <link rel="stylesheet" href="add_client.css">
</head>
<body>
    <img src="img/logo.png" alt="Logo" class="logo">
    
    <div class="container">
        <div class="title">Add New Client</div>
        
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Client added successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p style="color: red;">Error adding client. Please try again.</p>
        <?php endif; ?>

        <form action="add_client_page.php" method="POST">
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Full Name</span>
                    <input type="text" name="client_name" placeholder="Enter Your Name" required>
                </div>
                <div class="input-box">
                    <span class="details">Address</span>
                    <input type="text" name="address" placeholder="Enter Your Address" required>
                </div>
                <div class="input-box">
                    <span class="details">Email Address</span>
                    <input type="email" name="email" placeholder="Enter Your Email" required>
                </div>
                <div class="profile-upload">
                    <span class="details">Add Profile</span>
                    <label for="profile-pic" class="upload-box">
                        <input type="file" name="profile_picture" id="profile-pic" accept="image/*" hidden>
                        <span class="upload-text">Upload Photo</span>
                    </label>
                </div>
                <label for="matter_ids">Select Matters:</label>
                <select id="matter_ids" name="matter_ids[]" multiple>
                    <?php foreach ($matters as $matter): ?>
                        <option value="<?php echo $matter['matter_id']; ?>">
                            <?php echo htmlspecialchars($matter['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="button">
                <input type="submit" value="ADD">
            </div>
        </form>
    </div>
</body>
</html>
