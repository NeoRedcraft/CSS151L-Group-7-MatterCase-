<?php
include_once("decrypt.php");
function isEmailUnique($conn, $email, $key, $method) {
    // Fetch all encrypted emails from the database
    $stmt = $conn->prepare("SELECT email FROM users");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    // Iterate through the results and decrypt each email
    while ($row = $result->fetch_assoc()) {
        $encrypted_email = $row['email'];
        $decrypted_email = decryptData($encrypted_email, $key, $method);

        // Compare the decrypted email with the input email
        if ($decrypted_email === $email) {
            return false; // Email is not unique
        }
    }

    return true; // Email is unique
}
?>

