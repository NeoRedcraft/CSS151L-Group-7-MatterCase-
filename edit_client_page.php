<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/edit_client.php"); 
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/edit_client.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client</title>
    <link rel="stylesheet" href="edit_client.css"> <!-- Link to external CSS file -->
    <script>
        function addMatters() {
            const selectedMatters = [];
            document.querySelectorAll('input[name="matter_ids[]"]:checked').forEach((checkbox) => {
                selectedMatters.push(checkbox.value);
            });

            if (selectedMatters.length === 0) {
                alert("Please select at least one matter.");
                return;
            }

            selectedMatters.forEach((matterId) => {
                const matterElement = document.createElement('li');
                matterElement.classList.add('matter-item');
                matterElement.setAttribute('data-matter-id', matterId);

                const matterTitle = document.getElementById(`matter-title-${matterId}`).textContent;

                matterElement.innerHTML = `
                    ${matterTitle}
                    <button type="button" onclick="removeMatter(this)">Remove</button>
                `;
                document.getElementById('selected-matters-list').appendChild(matterElement);
            });
        }

        function removeMatter(button) {
            button.parentElement.remove();
        }

        function filterMatters() {
            const searchText = document.getElementById('search').value.toLowerCase();
            const matters = document.querySelectorAll('.matter-list div');

            matters.forEach((matter) => {
                const matterText = matter.textContent.toLowerCase();
                matter.style.display = matterText.includes(searchText) ? 'block' : 'none';
            });
        }
    </script>
</head>
<body>
    <h1>Edit Client</h1>

    <!-- Display Success/Error Messages -->
    <?php
    if (isset($_GET['success'])) {
        echo "<p style='color: green;'>Action completed successfully!</p>";
    } elseif (isset($_GET['error'])) {
        echo "<p style='color: red;'>An error occurred. Please try again.</p>";
    }
    ?>

    <!-- Back to Dashboard Button -->
    <p>
        <a href="<?php
            // Redirect to the appropriate dashboard based on usertype
            switch ($usertype) {
                case 0: // Admin
                    echo 'dashboard_admin.php';
                    break;
                case 1: // Partner
                    echo 'dashboard_partner.php';
                    break;
                default:
                    echo 'login_page.php'; // Fallback to login page
                    break;
            }
        ?>">Back to Dashboard</a>
    </p>

    <!-- Display Related Matters -->
    <div class="related-matters">
        <h2>Related Matters</h2>
        <form method="post" action="edit_client_page.php?client_id=<?php echo $client_id; ?>">
            <ul id="related-matters-list">
                <?php
                // Fetch related matters for the client
                $query = "
                    SELECT m.matter_id, m.title 
                    FROM matters m
                    JOIN client_matters cm ON m.matter_id = cm.matter_id
                    WHERE cm.client_id = ?
                ";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $client_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Decrypt the matter title
                        $decrypted_title = decryptData($row['title'], $key, $method);
                        echo "<li class='matter-item' data-matter-id='" . htmlspecialchars($row['matter_id']) . "'>
                            <input type='checkbox' name='remove_matter_ids[]' value='" . htmlspecialchars($row['matter_id']) . "'> 
                            <span id='matter-title-" . htmlspecialchars($row['matter_id']) . "'>" . htmlspecialchars($decrypted_title) . "</span>
                        </li>";
                    }
                } else {
                    echo "No related matters found.";
                }
                ?>
            </ul>
            <p><button type="submit" name="remove_matters">Remove Selected Matters</button></p>
        </form>
    </div>

        <h2>All Matters</h2>
        <input type="text" id="search" placeholder="Search matters..." onkeyup="filterMatters()">
        <div class="matter-list">
            <?php
            $query = "SELECT matter_id, title FROM matters";
            $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Decrypt the matter title
                $decrypted_title = decryptData($row['title'], $key, $method);

                // Check if the matter is already associated with the client
                $isChecked = in_array($row['matter_id'], $relatedMatterIds) ? 'checked' : '';

                echo "<div>
                    <input type='checkbox' name='matter_ids[]' value='" . htmlspecialchars($row['matter_id']) . "' id='matter-id-" . htmlspecialchars($row['matter_id']) . "' $isChecked>
                    <span id='matter-title-" . htmlspecialchars($row['matter_id']) . "'>" . htmlspecialchars($decrypted_title) . "</span>
                </div>";
            }
        } else {
            echo "No matters found.";
        }
        ?>
    </div>
    <form method="post" action="edit_client_page.php?client_id=<?php echo $client_id; ?>">
        <p><button type="submit" name="add_matters">Add Selected Matters</button></p>
    </form>
</body>
</html>