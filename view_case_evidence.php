<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evidence</title>
    <link rel="stylesheet" href="view_case_evidence.css"> 
</head>
<body>

    <!-- Logo -->
    <img src="img/logo.png" class="logo">

    <div class="container">
        <h1>Evidence</h1>
        
        <!-- Buttons for Back to Dashboard and Add New Evidence -->
        <div class="button-container">
            <a href="view_case_details.php?case_id=<?php echo $case_id; ?>" class="btn back-btn">Back to Dashboard</a>
            
            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                <a href="add_evidence_page.php?case_id=<?php echo $case_id; ?>" class="btn add-btn">Add New Evidence</a>
            <?php endif; ?>
        </div>

        <!-- Evidence Table -->
        <table>
            <thead>
                <tr>
                    <th>Evidence ID</th>
                    <th>Evidence Type</th>
                    <th>File</th>
                    <th>Description</th>
                    <th>Submission Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evidence as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['evidence_id']); ?></td>
                        <td><?php echo htmlspecialchars($item['evidence_type']); ?></td>
                        <td>
                            <?php if (!empty($item['file_path'])): ?>
                                <a href="view_file.php?file=<?php echo urlencode($item['file_path']); ?>" target="_blank">View File</a>
                            <?php else: ?>
                                No file uploaded
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo htmlspecialchars($item['submission_status']); ?></td>
                        <td>
                            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                                <a href="edit_evidence_page.php?evidence_id=<?php echo $item['evidence_id']; ?>" class="btn edit-btn">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
