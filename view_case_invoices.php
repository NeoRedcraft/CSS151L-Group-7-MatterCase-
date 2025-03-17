<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices</title>
    <link rel="stylesheet" href="view_case_invoices.css"> <!-- Link to the updated CSS -->
</head>
<body>

    <!-- Logo -->
    <img src="img/logo.png" class="logo">

    <div class="container">
        <h1>Invoices</h1>

        <!-- Button Container for Back and Add New Invoice -->
        <div class="button-container">
            <a href="view_case_details.php?case_id=<?php echo $case_id; ?>" class="btn back-btn">Back to Dashboard</a>
            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                <a href="add_invoice_page.php?case_id=<?php echo $case_id; ?>" class="btn add-btn">Add New Invoice</a>
            <?php endif; ?>
        </div>

        <!-- Invoices Table -->
        <table>
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['amount']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['payment_status']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['due_date']); ?></td>
                        <td>
                            <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                                <a href="edit_invoice_page.php?invoice_id=<?php echo $invoice['invoice_id']; ?>" class="btn edit-btn">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
