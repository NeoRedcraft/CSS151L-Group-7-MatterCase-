<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Fees</title>
    <link rel="stylesheet" href="view_case_fees.css"> <!-- Link to the updated CSS -->
</head>
<body>

    <!-- Logo -->
    <img src="img/logo.png" class="logo">

    <div class="container">
        <h1>Case Fees</h1>
        
        <!-- Button Container for Back and Add New Fee -->
        <div class="button-container">
            <a href="view_case_details.php?case_id=<?php echo $case_id; ?>" class="btn back-btn">Back to Dashboard</a>
            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                <a href="add_case_fee_page.php?case_id=<?php echo $case_id; ?>" class="btn add-btn">Add New Fee</a>
            <?php endif; ?>
        </div>

        <!-- Fees Table -->
        <table>
            <thead>
                <tr>
                    <th>Fee ID</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Payment Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($case_fees as $fee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fee['fee_id']); ?></td>
                        <td><?php echo htmlspecialchars($fee['amount']); ?></td>
                        <td><?php echo htmlspecialchars($fee['fee_description']); ?></td>
                        <td><?php echo htmlspecialchars($fee['payment_status']); ?></td>
                        <td><?php echo htmlspecialchars($fee['due_date']); ?></td>
                        <td>
                            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                                <a href="edit_case_fee_page.php?fee_id=<?php echo $fee['fee_id']; ?>" class="btn edit-btn">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
