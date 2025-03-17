<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Updates</title>
    <link rel="stylesheet" href="view_case_updates.css"> <!-- Link to your CSS file -->
</head>
<body>

    <!-- Logo or Header Section (Optional) -->
    <div class="header">
        <img src="img/logo.png" alt="Logo" class="logo">
    </div>

    <!-- Main Container -->
    <div class="container">
        <h1>Case Updates</h1>
        
        <!-- Button Container -->
        <div class="button-container">
            <!-- Back to Dashboard Button -->
            <a href="view_case_details.php?case_id=<?php echo $case_id; ?>" class="btn back-btn">Back to Dashboard</a>

            <!-- Add New Update Button -->
            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                <a href="add_case_update_page.php?case_id=<?php echo $case_id; ?>" class="btn add-btn">Add New Update</a>
            <?php endif; ?>
        </div>

        <!-- Case Updates Table -->
        <table>
            <thead>
                <tr>
                    <th>Update ID</th>
                    <th>Update Text</th>
                    <th>Updated By</th>
                    <th>Updated At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($case_updates as $update): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($update['update_id']); ?></td>
                        <td><?php echo htmlspecialchars($update['update_text']); ?></td>
                        <td><?php echo htmlspecialchars($update['updated_by']); ?></td>
                        <td><?php echo htmlspecialchars($update['updated_at']); ?></td>
                        <td>
                            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                                <a href="edit_case_update_page.php?update_id=<?php echo $update['update_id']; ?>" class="btn edit-btn">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
