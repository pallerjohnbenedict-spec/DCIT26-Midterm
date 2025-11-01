<?php
include 'conn.php';

// Handle AJAX requests for verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] === 'approve') {
        $request_id = intval($_POST['request_id']);
        $sql = "UPDATE document_requests SET status = 'approved', processed_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $request_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Request approved successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'Error approving request.'];
        }
        
    } elseif ($_POST['action'] === 'reject') {
        $request_id = intval($_POST['request_id']);
        $reason = $_POST['reason'] ?? '';
        
        $sql = "UPDATE document_requests SET status = 'rejected', rejection_reason = ?, processed_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $reason, $request_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Request rejected successfully.'];
        } else {
            $response = ['success' => false, 'message' => 'Error rejecting request.'];
        }
    }
    
    echo json_encode($response);
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'pending';
$search = $_GET['search'] ?? '';

// Build SQL query with filters
$sql = "
SELECT 
    id,
    student_id,
    document_type_name,
    purpose,
    request_date,
    release_date,
    status,
    rejection_reason
FROM 
    document_requests
WHERE 1=1
";

$params = [];
$types = "";

if ($status_filter !== 'all') {
    $sql .= " AND (status = ? OR status IS NULL)";
    $params[] = $status_filter === 'pending' ? 'pending' : $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (student_id LIKE ? OR document_type_name LIKE ? OR purpose LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$sql .= " ORDER BY request_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get counts for stats
$stats_sql = "
SELECT 
    SUM(CASE WHEN status = 'pending' OR status IS NULL THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
    COUNT(*) as total_count
FROM document_requests
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin Requests - Verification Center</title>
  
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Ubuntu:wght@400;500;700&display=swap');
/* CvSU-Themed Requests Page Styles */
/* CvSU-Themed Requests Page Styles */

:root {
  --primary-color: #00693e;
  --secondary-color: #ffffff;
  --accent-color: #e2f5e8;
  --text-dark: #1a1a1a;
  --text-light: #f5f5f5;
  --gray: #ccc;
  --border-radius: 12px;
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

body {
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f6f9f7;
  color: var(--text-dark);
}

.sidebar {
  position: fixed;
  width: 240px;
  height: 100vh;
  background-color: var(--primary-color);
  color: var(--text-light);
  padding: 20px;
  box-shadow: var(--shadow);
}

.sidebar-logo h2 {
  font-size: 24px;
  margin-bottom: 30px;
  color: var(--secondary-color);
  display: flex;
  align-items: center;
  gap: 10px;
}

.sidebar-menu ul {
  list-style: none;
  padding: 0;
}

.sidebar-menu li {
  margin-bottom: 20px;
}

.sidebar-menu a {
  color: var(--text-light);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 16px;
  transition: 0.3s;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
  color: var(--accent-color);
}

.content {
  margin-left: 260px;
  padding: 20px;
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.user {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user img {
  border-radius: 50%;
}

.req-section {
  margin-top: 20px;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: var(--secondary-color);
  padding: 20px;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  text-align: center;
}

.stat-number {
  font-size: 28px;
  font-weight: bold;
  color: var(--primary-color);
}

.stat-label {
  font-size: 14px;
  color: #555;
}

.filters-section {
  background: var(--secondary-color);
  padding: 15px 20px;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.filter-group {
  display: flex;
  gap: 10px;
  align-items: center;
}

.filter-btn {
  padding: 6px 14px;
  border: none;
  border-radius: 20px;
  background: var(--accent-color);
  cursor: pointer;
  text-decoration: none;
  color: var(--text-dark);
  font-size: 13px;
  transition: 0.3s;
}

.filter-btn.active,
.filter-btn:hover {
  background: var(--primary-color);
  color: white;
}

.search-input {
  padding: 6px 12px;
  border: 1px solid var(--gray);
  border-radius: var(--border-radius);
  font-size: 14px;
  width: 280px;
}

.requests-table table {
  width: 100%;
  border-collapse: collapse;
  background: var(--secondary-color);
  box-shadow: var(--shadow);
  border-radius: var(--border-radius);
  overflow: hidden;
}

.requests-table th,
.requests-table td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--gray);
  font-size: 14px;
}

.requests-table thead {
  background-color: var(--accent-color);
}

.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-approved {
  background: #d4edda;
  color: #155724;
}

.status-rejected {
  background: #f8d7da;
  color: #721c24;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.btn {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: 0.3s;
}

.btn-approve {
  background: #28a745;
  color: white;
}

.btn-reject {
  background: #dc3545;
  color: white;
}

.btn-view {
  background: #007bff;
  color: white;
}

.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: var(--border-radius);
  width: 90%;
  max-width: 500px;
  box-shadow: var(--shadow);
}

.modal-content textarea {
  width: 100%;
  height: 100px;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid var(--gray);
  margin-top: 10px;
  resize: vertical;
  font-size: 14px;
}

.modal-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.modal-buttons .btn {
  min-width: 100px;
}

        
        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 1001;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .notification.error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
        }
    </style>
</head>
<body>

<input type="checkbox" id="nav-toggle">
<div class="sidebar">
    <div class="sidebar-logo">
        <h2><span class="logo"><ion-icon name="logo-buffer"></ion-icon></span><span>CvSU</span></h2>
    </div>
    <div class="sidebar-menu">
        <ul>
            <li><a href="admin.php"><span><ion-icon name="list-outline"></ion-icon></span><span>Dashboard</span></a></li>
            <li><a href="ad_profile.html"><span><ion-icon name="person-circle-outline"></ion-icon></span><span>Profile</span></a></li>
            <li><a href="requests.php" class="active"><span><ion-icon name="file-tray-full-outline"></ion-icon></span><span>Requests</span></a></li>
            <li><a href="ad_grade_req.php"><span><ion-icon name="people-circle-outline"></ion-icon></span><span>Instructors</span></a></li>
            <li><a href="add_document.php"><span><ion-icon name="document-text-outline"></ion-icon></span><span>Add Document</span></a></li>
        </ul>
    </div>
</div>

<div class="content">
    <header>
        <h2><label for="nav-toggle"><span><ion-icon name="menu-outline"></ion-icon></span></label>Document Verification Center</h2>
        <div class="user">
            <a href="ad_profile.html"><img src="wandaheyhey.jpg" width="40px" height="40px" alt=""></a>
            <div>
                <h4>UserName</h4>
            </div>
        </div>
    </header>

    <section class="req-section">
        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['pending_count'] ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['approved_count'] ?></div>
                <div class="stat-label">Approved Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['rejected_count'] ?></div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_count'] ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filter-group">
                <strong>Status:</strong>
                <a href="?status=pending&search=<?= urlencode($search) ?>" 
                   class="filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">
                   Pending (<?= $stats['pending_count'] ?>)
                </a>
                <a href="?status=approved&search=<?= urlencode($search) ?>" 
                   class="filter-btn <?= $status_filter === 'approved' ? 'active' : '' ?>">
                   Approved
                </a>
                <a href="?status=rejected&search=<?= urlencode($search) ?>" 
                   class="filter-btn <?= $status_filter === 'rejected' ? 'active' : '' ?>">
                   Rejected
                </a>
                <a href="?status=all&search=<?= urlencode($search) ?>" 
                   class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">
                   All Requests
                </a>
            </div>
            
            <form method="GET" style="margin: 0;">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search by name, document type, or purpose..." 
                       class="search-input" onchange="this.form.submit()">
            </form>
        </div>

        <!-- Requests Table -->
        <div class="requests-table">
            <table>
                <thead>
                    <tr>
                        <th>REQUEST ID</th>
                        <th>STUDENT ID</th>
                        <th>DOCUMENT TYPE</th>
                        <th>PURPOSE</th>
                        <th>REQUEST DATE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                            $status = $row['status'] ?? 'pending';
                            $canTakeAction = ($status === 'pending' || $status === null);
                            ?>
                            <tr>
                                <td>#<?= htmlspecialchars($row['id']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['student_id']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($row['document_type_name']) ?></td>
                                <td><?= htmlspecialchars($row['purpose']) ?></td>
                                <td><?= date('M j, Y', strtotime($row['request_date'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $status ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($canTakeAction): ?>
                                            <button class="btn btn-approve" 
                                                    onclick="approveRequest(<?= $row['id'] ?>)">
                                                ✅ Approve
                                            </button>
                                            <button class="btn btn-reject" 
                                                    onclick="showRejectModal(<?= $row['id'] ?>)">
                                                ❌ Reject
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-view" 
                                                    onclick="viewDetails(<?= $row['id'] ?>)">
                                                👁️ View Details
                                            </button>
                                            <?php if ($status === 'rejected' && !empty($row['rejection_reason'])): ?>
                                                <button class="btn" style="background: #6c757d; color: white;" 
                                                        onclick="showRejectionReason('<?= htmlspecialchars($row['rejection_reason']) ?>')">
                                                    📝 Reason
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <ion-icon name="document-outline" style="font-size: 48px; color: #ccc;"></ion-icon>
                                <br><br>
                                <?php if (!empty($search)): ?>
                                    No requests found matching "<?= htmlspecialchars($search) ?>"
                                <?php else: ?>
                                    No <?= $status_filter !== 'all' ? $status_filter : '' ?> requests found.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Reject Modal -->
<div class="modal" id="rejectModal">
    <div class="modal-content">
        <h3>Reject Document Request</h3>
        <p>Please provide a reason for rejecting this request:</p>
        <textarea id="rejectionReason" placeholder="Enter rejection reason here..." required></textarea>
        <div class="modal-buttons">
            <button class="btn btn-reject" onclick="confirmReject()">Confirm Reject</button>
            <button class="btn" onclick="closeModal()" style="background: #6c757d; color: white;">Cancel</button>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div class="modal" id="reasonModal">
    <div class="modal-content">
        <h3>Rejection Reason</h3>
        <p id="reasonText"></p>
        <div class="modal-buttons">
            <button class="btn" onclick="closeModal()" style="background: #6c757d; color: white;">Close</button>
        </div>
    </div>
</div>

<!-- Notification -->
<div class="notification" id="notification"></div>

<script src="script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
let currentRequestId = null;

function approveRequest(requestId) {
    if (!confirm('Are you sure you want to approve this request?')) {
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=approve&request_id=${requestId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function showRejectModal(requestId) {
    currentRequestId = requestId;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectModal').style.display = 'flex';
}

function confirmReject() {
    const reason = document.getElementById('rejectionReason').value.trim();
    if (!reason) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reject&request_id=${currentRequestId}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function showRejectionReason(reason) {
    document.getElementById('reasonText').textContent = reason;
    document.getElementById('reasonModal').style.display = 'flex';
}

function viewDetails(requestId) {
    // You can implement this to show more detailed information
    showNotification('Opening detailed view...', 'success');
}

function closeModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('reasonModal').style.display = 'none';
}

function showNotification(message, type) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type} show`;
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// Close modals when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
});

// Header search functionality
document.getElementById('headerSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value;
    if (e.key === 'Enter' || searchTerm === '') {
        const currentStatus = new URLSearchParams(window.location.search).get('status') || 'pending';
        window.location.href = `?status=${currentStatus}&search=${encodeURIComponent(searchTerm)}`;
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>