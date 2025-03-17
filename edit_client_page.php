<?php
    include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/edit_client.php"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Client</title>
    <style>
        .matter-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }
        .related-matters {
            margin-bottom: 20px;
        }
        .related-matters ul {
            list-style-type: none;
            padding: 0;
        }
        .related-matters li {
            padding: 5px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
        }
        .matter-item {
            display: flex;
            justify-content: space-between;
        }
    </style>
    <script>
        // JavaScript to handle adding matters
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
                // Create an entry in the list of selected matters
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

        // JavaScript to handle removing a matter from the UI
        function removeMatter(button) {
            button.parentElement.remove();
        }

        // JavaScript to filter matters based on search input
        function filterMatters() {
            const searchText = document.getElementById('search').value.toLowerCase();
            const matters = document.querySelectorAll('.matter-list div');

            matters.forEach((matter) => {
                const matterText = matter.textContent.toLowerCase();
                if (matterText.includes(searchText)) {
                    matter.style.display = 'block';
                } else {
                    matter.style.display = 'none';
                }
            });
        }
    </script>
</head>
<body>
    <h1>Edit Client</h1>

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
                            <button type='button' onclick='removeMatter(this)'>Remove</button>
                        </li>";
                    }
                } else {
                    echo "No related matters found.";
                }
                ?>
            </ul>
        </form>
    </div>

    <!-- Edit Client Form -->
    <form name="update_client" method="post" action="edit_client_page.php?client_id=<?php echo $client_id; ?>">
        <table border="0">
            <tr> 
                <td>Client Name</td>
                <td><input type="text" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"></td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"></td>
            </tr>
            <tr> 
                <td>Address</td>
                <td><input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>"></td>
            </tr>
            <tr> 
                <td>Profile Picture</td>
                <td><input type="text" name="profile_picture" value="<?php echo htmlspecialchars($profile_picture); ?>"></td>
            </tr>
            <tr>
                <td><input type="hidden" name="client_id" value="<?php echo $client_id; ?>"></td>
                <td><input type="submit" name="update" value="Update"></td>
            </tr>
        </table>
    </form>

    <!-- Searchable List of All Matters -->
    <h2>All Matters</h2>
    <input type="text" id="search" placeholder="Search matters..." onkeyup="filterMatters()">
    <div class="matter-list">
        <?php
        // Fetch all matters
        $query = "SELECT matter_id, title FROM matters";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Decrypt the matter title
                $decrypted_title = decryptData($row['title'], $key, $method);
                echo "<div>
                    <input type='checkbox' name='matter_ids[]' value='" . htmlspecialchars($row['matter_id']) . "' id='matter-id-" . htmlspecialchars($row['matter_id']) . "'>
                    <span id='matter-title-" . htmlspecialchars($row['matter_id']) . "'>" . htmlspecialchars($decrypted_title) . "</span>
                </div>";
            }
        } else {
            echo "No matters found.";
        }
        ?>
    </div>
    <p><button onclick="addMatters()">Add Selected Matters</button></p>
</body>
</html>
