<?php
include 'conn.php';

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        $stmt = $conn->prepare("INSERT INTO document_type (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('✅ Document type added successfully!'); window.location.href='add_document.php';</script>";

    } elseif ($action === 'edit') {
        $original_name = $_POST['original_name'];
        $new_name = $_POST['name'];
        $new_description = $_POST['description'];

        $stmt = $conn->prepare("UPDATE document_type SET name = ?, description = ? WHERE name = ?");
        $stmt->bind_param("sss", $new_name, $new_description, $original_name);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('✏️ Document updated successfully!'); window.location.href='add_document.php';</script>";

    } elseif ($action === 'delete') {
        $name = $_POST['name'];

        $stmt = $conn->prepare("DELETE FROM document_type WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('🗑️ Document deleted successfully!'); window.location.href='add_document.php';</script>";
    }
}

// Fetch document types
$document_types = [];
$result = $conn->query("SELECT name, description FROM document_type");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $document_types[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Document Type</title>
    <link rel="stylesheet" href="add_document.css">
</head>
<body>

<input type="checkbox" id="nav-toggle">
<div class="sidebar">
    <div class="sidebar-logo">
        <h2><span class="logo"><ion-icon name="logo-buffer"></ion-icon></span><span>CvSU</span></h2>
    </div>
    <div class="sidebar-menu">
        <ul>
            <li><a href="admin.php"><ion-icon name="list-outline"></ion-icon><span>Dashboard</span></a></li>
            <li><a href="ad_profile.html"><ion-icon name="person-circle-outline"></ion-icon><span>Profile</span></a></li>
            <li><a href="requests.php"><ion-icon name="file-tray-full-outline"></ion-icon><span>Requests</span></a></li>
            <li><a href="ad_grade_req.php"><ion-icon name="people-circle-outline"></ion-icon><span>Instructors</span></a></li>
            <li><a href="add_document.php" class="active"><ion-icon name="document-text-outline"></ion-icon><span>Add Document</span></a></li>
        </ul>
    </div>
</div>

<div class="content">
    <header>
        <h2><label for="nav-toggle"><ion-icon name="menu-outline"></ion-icon></label>Admin Dashboard</h2>
        <div class="search-bar">
            <span><ion-icon name="search-outline"></ion-icon></span>
            <input type="search" placeholder="Search">
        </div>
        <div class="user">
            <a href="ad_profile.html"><img src="wandaheyhey.jpg" width="40px" height="40px" alt=""></a>
            <div><h4>UserName</h4></div>
        </div>
    </header>

    <main class="container">
        <h3>Add New Document Type</h3>
        <form method="POST" action="add_document.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Document Type Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn">Add Document Type</button>
        </form>

        <h3 style="margin-top: 40px;">Available Document Types</h3>
        <?php if (count($document_types) > 0): ?>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($document_types as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['name']) ?></td>
                            <td><?= htmlspecialchars($doc['description']) ?></td>
                            <td>
                                <button class="button edit-btn"
                                    data-name="<?= htmlspecialchars($doc['name']) ?>"
                                    data-description="<?= htmlspecialchars($doc['description']) ?>">
                                    ✏️ Edit
                                </button>
                                <button class="button delete-btn"
                                    data-name="<?= htmlspecialchars($doc['name']) ?>">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No documents have been created yet.</p>
        <?php endif; ?>
    </main>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h3>Edit Document Type</h3>
        <form method="POST" action="add_document.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="original_name" id="edit_original_name">
            <div class="form-group">
                <label for="edit_name">Name:</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit_description">Description:</label>
                <textarea id="edit_description" name="description" required></textarea>
            </div>
            <button type="submit" class="button">Update</button>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('deleteModal')">&times;</span>
        <h3>Confirm Delete</h3>
        <form method="POST" action="add_document.php">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="name" id="delete_name">
            <p>Are you sure you want to delete this document type?</p>
            <button type="submit" class="button">Yes, Delete</button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_original_name').value = btn.dataset.name;
            document.getElementById('edit_name').value = btn.dataset.name;
            document.getElementById('edit_description').value = btn.dataset.description;
            document.getElementById('editModal').style.display = 'block';
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('delete_name').value = btn.dataset.name;
            document.getElementById('deleteModal').style.display = 'block';
        });
    });

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
</script>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
