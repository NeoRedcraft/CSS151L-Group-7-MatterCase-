<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/audit_log.php");

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch client data to edit
$client_id = $_GET['client_id'] ?? null;
if (!$client_id) {
    echo "Client ID not provided.";
    exit();
}

// Fetch client data
$query = "SELECT * FROM clients WHERE client_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client_data = $result->fetch_assoc();

if (!$client_data) {
    echo "Client not found.";
    exit();
}

// Decrypt client data
$client_name = decryptData($client_data['client_name'], $key, $method);
$email = decryptData($client_data['email'], $key, $method);
$address = decryptData($client_data['address'], $key, $method);
$profile_picture = decryptData($client_data['profile_picture'], $key, $method);

// Handle form submission for updating client data
if (isset($_POST['update'])) {
    $new_client_name = $_POST['client_name'];
    $new_email = $_POST['email'];
    $new_address = $_POST['address'];
    $new_profile_picture = $_POST['profile_picture'];

    // Encrypt new data
    $encrypted_client_name = encryptData($new_client_name, $key, $method);
    $encrypted_email = encryptData($new_email, $key, $method);
    $encrypted_address = encryptData($new_address, $key, $method);
    $encrypted_profile_picture = encryptData($new_profile_picture, $key, $method);

    // Update the client data in the database
    $update_query = "
        UPDATE clients 
        SET client_name = ?, 
            email = ?, 
            address = ?, 
            profile_picture = ? 
        WHERE client_id = ?
    ";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param(
        "ssssi",
        $encrypted_client_name,
        $encrypted_email,
        $encrypted_address,
        $encrypted_profile_picture,
        $client_id
    );

    if ($stmt->execute()) {
        echo "Client updated successfully.";
    } else {
        echo "Error updating client.";
    }

    // Compare old and new data to log changes
    $changes = [];
    if ($new_client_name !== $client_name) {
        $changes[] = "Client Name: '$client_name' to '$new_client_name'";
    }
    if ($new_email !== $email) {
        $changes[] = "Email: '$email' to '$new_email'";
    }
    if ($new_address !== $address) {
        $changes[] = "Address: '$address' to '$new_address'";
    }
    if ($new_profile_picture !== $profile_picture) {
        $changes[] = "Profile Picture: Updated";
    }

    // Log changes if any
    if (!empty($changes)) {
        $action = "Updated client ID $client_id: " . implode(", ", $changes);
        logAction($conn, $user_id, $action, $key, $method);
    }
}

// Handle adding related matters
if (isset($_POST['add_matters'])) {
    if (!empty($_POST['matter_ids'])) {
        foreach ($_POST['matter_ids'] as $matter_id) {
            // Check if the matter is already related to the client
            $check_query = "SELECT * FROM client_matters WHERE client_id = ? AND matter_id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ii", $client_id, $matter_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                // Add the matter to the client
                $insert_query = "INSERT INTO client_matters (client_id, matter_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("ii", $client_id, $matter_id);

                if ($stmt->execute()) {
                    // Log the addition of matters
                    $matter_title = getMatterTitle($conn, $matter_id);
                    $action = "Added matter '$matter_title' (ID: $matter_id) to client ID $client_id";
                    logAction($conn, $user_id, $action, $key, $method);
                } else {
                    echo "Error adding matter ID $matter_id.";
                }
            }
        }
        echo "Selected matters added successfully.";
    } else {
        echo "No matters selected.";
    }
}

// Handle removing related matters
if (isset($_POST['remove_matters'])) {
    if (!empty($_POST['remove_matter_ids'])) {
        foreach ($_POST['remove_matter_ids'] as $matter_id) {
            // Remove the matter from the client
            $delete_query = "DELETE FROM client_matters WHERE client_id = ? AND matter_id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $client_id, $matter_id);

            if ($stmt->execute()) {
                // Log the removal of matters
                $matter_title = getMatterTitle($conn, $matter_id);
                $action = "Removed matter '$matter_title' (ID: $matter_id) from client ID $client_id";
                logAction($conn, $user_id, $action, $key, $method);
            } else {
                echo "Error removing matter ID $matter_id.";
            }
        }
        echo "Selected matters removed successfully.";
    } else {
        echo "No matters selected for removal.";
    }
}

// Function to fetch matter title based on matter ID
function getMatterTitle($conn, $matter_id) {
    $query = "SELECT title FROM matters WHERE matter_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $matter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return decryptData($row['title'], $key, $method);
    }
    return null;
}

?>

