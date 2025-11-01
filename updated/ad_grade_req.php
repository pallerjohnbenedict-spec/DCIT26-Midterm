<?php
include 'conn.php';
session_start();

// Accept grade
if (isset($_POST['accept'])) {
    $gradeId = $_POST['grade_id'];
    $adminId = 1; // Change this if you're using session-based admin ID
    $stmt = $conn->prepare("UPDATE grades SET is_verified = 1, verified_by = ? WHERE id = ?");
    $stmt->bind_param("ii", $adminId, $gradeId);
    $stmt->execute();
}

// Decline grade
if (isset($_POST['decline'])) {
    $gradeId = $_POST['grade_id'];
    $stmt = $conn->prepare("DELETE FROM grades WHERE id = ?");
    $stmt->bind_param("i", $gradeId);
    $stmt->execute();
}

// Upload grade (single)
if (isset($_POST['single_submit'])) {
    $student_id = $_POST['student_id'];
    $subject = $_POST['subject'];
    $grade = $_POST['grade'];

    if (!empty($student_id) && !empty($subject) && is_numeric($grade)) {
        $stmt = $conn->prepare("INSERT INTO grades (student_id, subject, grade, date_recorded, is_verified) VALUES (?, ?, ?, NOW(), 0)");
        $stmt->bind_param("ssd", $student_id, $subject, $grade);
        $stmt->execute();
    }
}

// Bulk CSV upload
if (isset($_POST['bulk_submit']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $student_id = $data[0];
            $subject = $data[1];
            $grade = $data[2];
            if (!empty($student_id) && !empty($subject) && is_numeric($grade)) {
                $stmt = $conn->prepare("INSERT INTO grades (student_id, subject, grade, date_recorded, is_verified) VALUES (?, ?, ?, NOW(), 0)");
                $stmt->bind_param("ssd", $student_id, $subject, $grade);
                $stmt->execute();
            }
        }
        fclose($handle);
    }
}

$professor_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; // assuming session stores this

$stmt = $conn->prepare("INSERT INTO grades (student_id, subject, grade, date_recorded, is_verified, professor_name) VALUES (?, ?, ?, NOW(), 0, ?)");
$stmt->bind_param("ssds", $student_id, $subject, $grade, $professor_name);


// Search logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if ($search) {
    $recentGrades = $conn->prepare("
        SELECT * FROM grades
        WHERE student_id LIKE CONCAT('%', ?, '%') 
           OR subject LIKE CONCAT('%', ?, '%')
        ORDER BY date_recorded DESC
        LIMIT 15
    ");
    $recentGrades->bind_param("ss", $search, $search);
    $recentGrades->execute();
    $recentGrades = $recentGrades->get_result();
} else {
    $recentGrades = $conn->query("SELECT * FROM grades ORDER BY date_recorded DESC LIMIT 15");
}

?>


<!-- your unchanged HTML starts here -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width-device-width, initial-scale= 1.0"> 
    <title>Admin Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<input type="checkbox" id="nav-toggle">
<div class="sidebar">
    <div class="sidebar-logo">
        <h2><span class="logo"><ion-icon name="logo-buffer">
        </ion-icon></span><span>CvSU</span></h2>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li>    
                <a href="admin.php" class="active"><span><ion-icon name="list-outline"></ion-icon></span>
                <span>Dashboard</span></a>
            </li>
            <li>
                <a href="ad_profile.html"><span><ion-icon name="person-circle-outline">
                </ion-icon></span>
                <span>Profile</span></a>
            </li>
            <li>
                <a href="requests.php"><span><ion-icon name="file-tray-full-outline">
                </ion-icon></span>
                <span>Requests</span></a>
            </li>
            <li>
                <a href="ad_grade_req.php"><span><ion-icon name="people-circle-outline">
                </ion-icon></span>
                <span>Instructors</span></a>
            </li>
             <li>
                <a href="add_document.php"><span><ion-icon name="document-text-outline">
                </ion-icon></span>
                <span>Add Document</span></a>
            </li>
        </ul>
    </div>
</div>

<div class="content">
    <header>
        <h2><label for="nav-toggle"><span><ion-icon name="menu-outline"></ion-icon></span>
        </label>Admin Dashboard</h2>
       
            <form method="GET" class="search-bar">
                <span><ion-icon name="search-outline"></ion-icon></span>
                <input type="search" name="search" placeholder="Search student or subject..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </form>
     
        <div class="user">
            <a href="ad_profile.html"><img src="wandaheyhey.jpg" width="40px"
            height="40px" alt=""></a>
            <div>
                <h4>Admin</h4>
            </div>
        </div>
    </header>

    <section class="inst-section">
        <div class="inst-div">
            <div class="inst-card">
                <div class="inst-header">
                    <h2>COMPILATION OF GRADES</h2>
                    
                    <!-- GRADE UPLOAD FORM -->
                    <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px;">
                        <input type="text" name="student_id" placeholder="Student Name" required>
                        <input type="text" name="subject" placeholder="Subject" required>
                        <input type="number" step="0.01" name="grade" placeholder="Grade" required>
                        <button type="submit" name="single_submit">Upload Grade</button>
                    </form>

                    <!-- BULK UPLOAD FORM -->
                    <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px;">
                        <input type="file" name="csv_file" accept=".csv" required>
                        <button type="submit" name="bulk_submit">Upload CSV</button>
                    </form>

                    <!-- TABLE -->
                    <div class="inst-table">
                        <table width="100%">
    <thead>
        <tr>
            <td>STUDENT ID</td>
            <td>SUBJECT</td>
            <td>GRADE</td>
            <td>DATE</td>
            <td>PROFESSOR</td>
            <td>STATUS</td>
            <td>ACTION</td>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $recentGrades->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td><?php echo htmlspecialchars($row['grade']); ?></td>
                <td><?php echo date('M d, Y', strtotime($row['date_recorded'])); ?></td>
                <td><?php echo htmlspecialchars($row['professor_name']); ?></td>
                <td>
                    <?php echo $row['is_verified'] ? '<span style="color: green;">Verified</span>' : '<span style="color: orange;">Pending</span>'; ?>
                </td>
                <td>
                    <?php if (!$row['is_verified']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="grade_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="accept" style="background-color:green;color:white;border:none;padding:4px 8px;">Accept</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="grade_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="decline" style="background-color:red;color:white;border:none;padding:4px 8px;">Decline</button>
                        </form>
                    <?php else: ?>
                        <em>N/A</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($recentGrades->num_rows === 0): ?>
            <tr><td colspan="6" style="text-align: center;">No grades found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
                    </div> <!-- .inst-table -->

                </div> <!-- .inst-header -->
            </div> <!-- .inst-card -->
        </div> <!-- .inst-div -->
    </section>
</div>

<script src="script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
