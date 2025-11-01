<?php
include 'conn.php';


// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $searchTerm = "%{$search}%";
    $stmt = $conn->prepare("
        SELECT 
            dr.id,
            s.student_id,
            u.first_name,
            u.last_name,
            dr.document_type_name,
            dr.purpose,
            dr.request_date,
            dr.release_date
        FROM document_requests dr
        JOIN students s ON dr.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        WHERE 
            s.student_id LIKE ? OR
            u.first_name LIKE ? OR
            u.last_name LIKE ? OR
            dr.document_type_name LIKE ? OR
            dr.purpose LIKE ? OR
            dr.request_date LIKE ? OR
            dr.release_date LIKE ?
        ORDER BY dr.request_date DESC
    ");
    $stmt->bind_param("sssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare("
        SELECT 
            dr.id,
            s.student_id,
            u.first_name,
            u.last_name,
            dr.document_type_name,
            dr.purpose,
            dr.request_date,
            dr.release_date
        FROM document_requests dr
        JOIN students s ON dr.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        ORDER BY dr.request_date DESC
        LIMIT 6
    ");
}
$stmt->execute();
$result = $stmt->get_result();

// Dashboard statistics
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalRequests = $conn->query("SELECT COUNT(*) AS total FROM document_requests")->fetch_assoc()['total'];
$totalProfessors = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'professor'")->fetch_assoc()['total'];

// Get latest 5 instructors
$instructors = $conn->query("SELECT first_name, last_name FROM users WHERE role = 'professor' ORDER BY user_id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

      <!-- Top Navbar -->
  <div class="top-navbar">
  <div class="top-content text-center">
    <span class="top-text">Truth, Excellence, Service</span> 
  </div>
</div>
<input type="checkbox" id="nav-toggle">
<div class="sidebar">
    <div class="sidebar-logo">
        <h2><span class="logo"><ion-icon name="logo-buffer"></ion-icon></span><span>CvSU</span></h2>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li><a href="admin.php"><span><ion-icon name="list-outline"></ion-icon></span><span>Dashboard</span></a></li>
            <li><a href="ad_profile.html"><span><ion-icon name="person-circle-outline"></ion-icon></span><span>Profile</span></a></li>
            <li><a href="requests.php"><span><ion-icon name="file-tray-full-outline"></ion-icon></span><span>Requests</span></a></li>
            <li><a href="ad_grade_req.php"><span><ion-icon name="people-circle-outline"></ion-icon></span><span>Instructors</span></a></li>
            <li><a href="add_document.php"><span><ion-icon name="document-text-outline"></ion-icon></span><span>Add Document</span></a></li>
        </ul>
    </div>
</div>

<div class="content">
    <header>
        <h2><label for="nav-toggle"><span><ion-icon name="menu-outline"></ion-icon></span></label>Admin Dashboard</h2>
        <form method="get" class="search-bar">
            <span><ion-icon name="search-outline"></ion-icon></span>
            <input type="search" name="search" placeholder="Search by student or document" value="<?php echo htmlspecialchars($search); ?>" />
        </form>
        <div class="user">
            <a href="ad_profile.html"><img src="wandaheyhey.jpg" width="40px" height="40px" alt=""></a>
            <div><h4>UserName</h4></div>
        </div>
    </header>

    <main>
            <div class="box">
                <div class="box-single">
                    <h1><?php echo $totalUsers; ?></h1>
                    <p>Users</p>
                    <span><ion-icon name="person-outline"></ion-icon></span>
                </div>
                <div class="box-single">
                    <h1><?php echo $totalRequests; ?></h1>
                    <p>Document Requests</p>
                    <span><ion-icon name="file-tray-stacked-outline"></ion-icon></span>
                </div>
                <div class="box-single">
                    <h1><?php echo $totalProfessors; ?></h1>
                    <p>Professors</p>
                    <span><ion-icon name="people-circle-outline"></ion-icon></span>
                </div>
                <div class="box-single">
                    <h1>--</h1>
                    <p>Reserved</p>
                    <span><ion-icon name="ellipsis-horizontal-outline"></ion-icon></span>
                </div>
            </div>

        <div class="sight-req">
            <div class="link-reqs">
                <div class="card">
                    <div class="req-card">
                        <h3>Requests</h3>
                        <button type="button"><a href="requests.php">View All<ion-icon name="eye-outline"></ion-icon></a></button>
                    </div>
                    <div class="req-body">
                        <div class="table-req">
                            <table width="100%">
                                <thead>
                                    <tr>
                                        <td>Request ID</td>
                                        <td>Student ID</td>
                                        <td>Document Type</td>
                                        <td>Request Date</td>
                                        <td>Release Date</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($row['student_id']); ?><br>
                                                    <small><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['document_type_name']); ?></td>
                                                <td><?php echo $row['request_date']; ?></td>
                                                <td><?php echo $row['release_date']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center;">No recent requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <div class="instructor">
                <div class="card">
                    <div class="req-card">
                        <h3>Instructors</h3>
                        <button type="button"><a href="ad_grade_req.php">View All<ion-icon name="eye-outline"></ion-icon></a></button>
                    </div>
                    <div class="req-body">
                        <?php if ($instructors->num_rows > 0): ?>
                            <?php while ($inst = $instructors->fetch_assoc()): ?>
                                <div class="instructors">
                                    <div class="info">
                                        <div>
                                            <h4><?php echo htmlspecialchars($inst['first_name'] . ' ' . $inst['last_name']); ?></h4>
                                            <small>Professor</small>
                                        </div>
                                    </div>
                                    <div class="contact">
                                        <span><ion-icon name="person-circle-outline"></ion-icon></span>
                                        <span><ion-icon name="call-outline"></ion-icon></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: gray;">No instructors found.</p>
                        <?php endif; ?>
                    </div>
                </div> 
            </div>
        </main>
    </div>

<script src="script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
