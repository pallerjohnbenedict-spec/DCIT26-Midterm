<?php
session_start();
include 'conn.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = '';
$error = '';

// Check if professor is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'professor') {
    die("Access denied. Only professors can upload grades.");
}

$professor_id = $_SESSION['id'];

// Check if professor has profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'professor'");
$stmt->execute([$professor_id]);
$professor_profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor_profile) {
    die("Professor profile not found. Please contact admin.");
}

// Get professor's full name
$professor_name = $professor_profile['first_name'] . ' ' . $professor_profile['last_name'];

// Handle single grade submission
if (isset($_POST['single_grade'])) {
    $student_id = trim($_POST['student_id']);
    $subject = trim($_POST['subject']);
    $grade = trim($_POST['grade']);

    if (empty($student_id) || empty($subject) || $grade === '') {
        $error = "All fields are required.";
    } elseif (!is_numeric($grade) || $grade < 0 || $grade > 100) {
        $error = "Grade must be a number between 0 and 100.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO grades (student_id, subject, grade, professor_name, date_recorded) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$student_id, $subject, $grade, $professor_name]);

            $_SESSION['success_message'] = "Grade uploaded successfully!";
            header("Location: instructor.php");
            exit;
        } catch(PDOException $e) {
            $error = "Error uploading grade: " . $e->getMessage();
        }
    }
}

// Handle bulk CSV upload
elseif (isset($_POST['bulk_upload']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle);
        $success_count = 0;
        $error_count = 0;
        $errors = [];

        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 3) {
                $student_id = trim($data[0]);
                $subject = trim($data[1]);
                $grade = trim($data[2]);

                if (empty($student_id) || empty($subject) || $grade === '') {
                    $error_count++;
                    $errors[] = "Row with Student ID '$student_id': Missing fields";
                    continue;
                }

                if (!is_numeric($grade) || $grade < 0 || $grade > 100) {
                    $error_count++;
                    $errors[] = "Row with Student ID '$student_id': Invalid grade value";
                    continue;
                }

                try {
                    // Verify student exists
                    $check = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
                    $check->execute([$student_id]);
                    if ($check->rowCount() === 0) {
                        $error_count++;
                        $errors[] = "Student ID '$student_id' not found";
                        continue;
                    }

                    $stmt = $pdo->prepare("INSERT INTO grades (student_id, subject, grade, professor_name, date_recorded) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$student_id, $subject, $grade, $professor_name]);
                    $success_count++;

                } catch(PDOException $e) {
                    $error_count++;
                    $errors[] = "Error for Student ID '$student_id': " . $e->getMessage();
                }
            }
        }
        fclose($handle);

        $message = "Bulk upload complete! $success_count grades uploaded.";
        if ($error_count > 0) {
            $message .= " $error_count errors occurred.";
            $error = implode("<br>", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $error .= "<br>... and " . (count($errors) - 10) . " more.";
            }
        }
    } else {
        $error = "Error uploading file.";
    }
}

// Fetch recent uploads
$stmt = $pdo->prepare("
    SELECT g.*, s.student_id AS student_ref
    FROM grades g
    JOIN students s ON g.student_id = s.student_id
    WHERE g.professor_name = ?
    ORDER BY g.date_recorded DESC
    LIMIT 10
");
$stmt->execute([$professor_name]);
$recent_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Upload System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
 
   <style>
    
/* Top Navbar */
/* Top Navbar */
.top-navbar {
  display: flex;
  justify-content: center; /* Center content horizontally */
  align-items: center;     /* Center content vertically */
  height: 35px;
  background-color: #004225;
  color: white;
  font-size: 14px;
  padding: 5px 0;
  overflow: hidden;
  transition: height 0.4s ease, opacity 0.4s ease;
}

.top-navbar.hidden {
  height: 0;
  opacity: 0;
}

/* Span inside navbar */
.top-navbar span {
  color: #ffcc00;
  margin: 0 8px;
  margin-left: 480px;
}

/* Navbar links */
.top-navbar a {
  color: #ffcc00;
  text-decoration: none;
  margin: 0 8px;
}

.top-navbar a:hover {
  text-decoration: underline;
}

    * {
        font-family: 'Segoe UI', sans-serif;
        box-sizing: border-box;
    }

    body {
        background-color: #f4f6f9;
        color: #333;
    }

    .container {
        max-width: 1000px;
        padding: 20px;
    }

    .header-gradient {
        background: linear-gradient(135deg, #09571c 0%, #caed2c 100%);
        color: white;
        border-radius: 1rem;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        text-align: center;
        padding: 2.5rem 1rem;
        margin-bottom: 2rem;
    }

    .header-gradient h1 {
        font-size: 2rem;
        font-weight: 600;
    }

    .header-gradient p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .btn-gradient {
       background: linear-gradient(135deg, #09571c 0%, #caed2c 100%);
        color: white;
        border: none;
        border-radius: 0.375rem;
        padding: 0.6rem 1.2rem;
        transition: 0.3s ease;
    }

    .btn-gradient:hover {
      background: linear-gradient(135deg, #09571c 0%, #11500b 100%);
        color: #fff;
    }

    .btn-outline-secondary:hover {
        background-color: #145724;
        color: #fff;
        border-color: #104d1e;
    }

    .btn-close {
        background-color: transparent;
    }

    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0;
        background-color: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 1rem 1.5rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .nav-pills .nav-link {
        color: #09571c;
    }

    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #09571c 0%, #2ced76 100%);
        color: #fff;
        border-radius: 0.5rem;
    }

    .form-control {
        border-radius: 0.5rem;
    }

    .form-control:focus {
        border-color: #17502d;
        box-shadow: 0 0 0 0.2rem rgba(44, 237, 118, 0.25);
    }

    .csv-format {
        background-color: #fff;
        border-left: 5px solid #1e5e2d;
        border-radius: 0.5rem;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }

    .alert {
        border-radius: 0.5rem;
    }

    .alert i {
        margin-right: 8px;
    }

    table.table {
        font-size: 0.95rem;
    }

    .table th {
        color: #555;
        font-weight: 600;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.7em;
        border-radius: 0.375rem;
    }

    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background-color: #195f29;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background-color: #e0e0e0;
    }

    .text-muted small {
        font-size: 0.8rem;

    }


    .header2 {
      background-image: url('upload/ewan.svg'); /* Replace with your image path */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 350px;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      text-align: center;
      position: relative;
      transition: opacity 0.5s ease, transform 0.5s ease;
      flex-direction: column;
    }

    .header2::before {
      content: "";
      position: absolute;
      inset: 0;
       background-color: rgba(0, 0, 0, 0.4);
    
    }

    .header2 h1 {
      margin-top: 30px;
      font-size: 2rem;
      font-weight: bold;
      z-index: 1;
      transition: transform 0.5s ease, opacity 0.5s ease;
      color: white;
    }
    .header2 p {
    
    margin: 6px 0 0 0; /* Small space below h1 */
    z-index: 1;
    transition: transform 0.5s ease, opacity 0.5s ease;
    color: white;
    }
    .header2.shrink {
      opacity: 0;
      transform: translateY(-50px);
    }

    .header2.shrink h1 {
      transform: scale(0.9);
      opacity: 0;
    }
/* Section styling */
.grades-management-section {
    background-color: #f9fdfb;
}

/* Custom button for My Profile */
.btn-outline-profile {
    border: 2px solid #198754;
    color: #198754;
    background-color: transparent;
    transition: all 0.3s ease-in-out;
    border-radius: 50px;
    padding: 8px 20px;
    font-weight: 500;
}

.btn-outline-profile:hover {
    background: linear-gradient(135deg, #198754, #28df99);
    color: white;
    border-color: #28df99;
    box-shadow: 0 4px 10px rgba(40, 223, 153, 0.4);
    transform: translateY(-2px);
}

/* Optional icon hover animation */
.btn-outline-profile i {
    transition: transform 0.3s ease;
}
.btn-outline-profile:hover i {
    transform: scale(1.1) rotate(5deg);
}



</style>

</head>
<body>
    
  <!-- Top Navbar -->
  <div class="top-navbar">
  <div class="top-content text-center">
    <span class="top-text">Truth, Excellence, Service</span> 
  </div>
</div>


 <div class="header2" id="banner2"></div>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

        <?php echo "Welcome, " . htmlspecialchars($professor_name); ?>
        <section class="grades-management-section py-5">
    <div class="container my-4">
        <a href="professor_profile.php" class="btn btn-outline-profile mb-3">
    <i class="fas fa-user-circle me-2"></i>My Profile
    </a>

        <a href="logout.php" class="btn btn-danger" style="float: right; margin: 10px;">Logout</a>

        <!-- Header -->
        <div class="header-gradient p-4 rounded-3 text-center mb-4">
            <h1 class="mb-2"><i class="fas fa-graduation-cap me-2"></i><?php echo "Welcome, " . htmlspecialchars($professor_name); ?></h1>
            <p class="mb-0">Upload and manage student grades efficiently</p>
        </div>
        
        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Upload Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <!-- Navigation Pills -->
                <ul class="nav nav-pills mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="single-tab" data-bs-toggle="pill" data-bs-target="#single" type="button">
                            <i class="fas fa-user me-2"></i>Single Grade
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bulk-tab" data-bs-toggle="pill" data-bs-target="#bulk" type="button">
                            <i class="fas fa-upload me-2"></i>Bulk Upload
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Single Grade Upload -->
                    <div class="tab-pane fade show active" id="single" role="tabpanel">
                        <h4 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Upload Single Grade</h4>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="student_id" class="form-label">Student ID</label>
                                    <input type="text" class="form-control" id="student_id" name="student_id" placeholder="e.g., 2021100012" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           placeholder="e.g., Mathematics" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="grade" class="form-label">Grade (0-100)</label>
                                    <input type="number" class="form-control" id="grade" name="grade" 
                                           min="0" max="100" step="0.01" placeholder="e.g., 85.5" required>
                                </div>
                            </div>
                            <button type="submit" name="single_grade" class="btn btn-gradient text-white">
                                <i class="fas fa-save me-2"></i>Upload Grade
                            </button>
                        </form>
                    </div>
                    
                    <!-- Bulk Upload -->
                    <div class="tab-pane fade" id="bulk" role="tabpanel">
                        <h4 class="mb-3"><i class="fas fa-file-csv me-2"></i>Bulk Upload from CSV</h4>
                        <p class="text-muted">Upload multiple grades at once using a CSV file.</p>
                        
                        <div class="csv-format mb-3">
                            <strong>CSV Format:</strong><br>
                            student_ID,subject,grade<br>
                            "202311237",Mathematics,85.5<br>
                            "202311238",Mathematics,92.0<br>
                            "202311239",Mathematics,78.5<br>
                            "202311231",Physics,88.0
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Select CSV File</label>
                                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                <div class="form-text">Make sure your CSV file follows the format shown above.</div>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button type="submit" name="bulk_upload" class="btn btn-gradient text-white">
                                    <i class="fas fa-upload me-2"></i>Upload CSV
                                </button>
                                
                                <a href="data:text/csv;charset=utf-8,student_name%2Csubject%2Cgrade%0A%22John%20Doe%22%2CMathematics%2C85.5%0A%22Jane%20Smith%22%2CMathematics%2C92.0%0A%22Mike%20Johnson%22%2CPhysics%2C78.5" 
                                   download="grade_template.csv" class="btn btn-outline-secondary">
                                    <i class="fas fa-download me-2"></i>Download Template
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Grades -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Grades Uploaded</h5>
            </div>
            <div class="card-body">
                <?php if (count($recent_grades) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="fas fa-user me-1"></i>Student ID</th>
                                    <th><i class="fas fa-book me-1"></i>Subject</th>
                                    <th><i class="fas fa-chart-line me-1"></i>Grade</th>
                                    <th><i class="fas fa-calendar me-1"></i>Date Recorded</th>
                                    <th><i class="fas fa-check-circle me-1"></i>Verified</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_grades as $grade): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-user-graduate me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($grade['student_id']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                                        <td>
                                            <?php 
                                                $gradeValue = $grade['grade'];
                                                $badgeClass = 'bg-primary';
                                                if ($gradeValue >= 90) $badgeClass = 'bg-success';
                                                elseif ($gradeValue >= 80) $badgeClass = 'bg-info';
                                                elseif ($gradeValue >= 70) $badgeClass = 'bg-warning';
                                                elseif ($gradeValue < 60) $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($gradeValue); ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y g:i A', strtotime($grade['date_recorded'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($grade['is_verified']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Yes
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No grades uploaded yet</h5>
                        <p class="text-muted">Start by uploading your first student grade using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </section>
    <!-- Bootstrap JS -->
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    offset: 120,
    once: false,   
    mirror: true  
  });
</script>




   <script>
     const banner = document.getElementById('banner1');

    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        banner1.classList.add('shrink');
      } else {
        banner1.classList.remove('shrink');
      }
    });

 document.addEventListener("DOMContentLoaded", function () {
    const dropdowns = document.querySelectorAll(".mega-dropdown");

    dropdowns.forEach(dropdown => {
      const button = dropdown.querySelector(".dropbtn");

      button.addEventListener("click", function (e) {
        e.stopPropagation();
        
        // Close other open dropdowns
        dropdowns.forEach(d => {
          if (d !== dropdown) d.classList.remove("open");
        });

        // Toggle this dropdown
        dropdown.classList.toggle("open");
      });
    });

    // Close dropdowns if clicked outside
    document.addEventListener("click", function () {
      dropdowns.forEach(dropdown => {
        dropdown.classList.remove("open");
      });
    });
  });

   window.addEventListener('scroll', function () {
  const mainNavbar = document.querySelector('.main-navbar');
  const topNavbar = document.querySelector('.top-navbar');

  if (window.scrollY > 40) {
    topNavbar.classList.add('hidden');
    mainNavbar.classList.add('pinned');
  } else {
    topNavbar.classList.remove('hidden');
    mainNavbar.classList.remove('pinned');
  }
});
     const searchOverlay = document.querySelector('.search-overlay');
const dimOverlay = document.querySelector('.page-dim-overlay');
const openBtn = document.querySelector('.search-icon-btn');
const closeBtn = document.querySelector('.close-btn');

openBtn.addEventListener('click', () => {
  searchOverlay.classList.add('active');
  dimOverlay.classList.add('active');
});

closeBtn.addEventListener('click', () => {
  searchOverlay.classList.remove('active');
  dimOverlay.classList.remove('active');
});
  
function toggleSearch() {
  document.getElementById("searchOverlay").classList.add("active");
  document.getElementById("searchToggle").classList.add("hidden");
}

function closeSearch() {
  document.getElementById("searchOverlay").classList.remove("active");
  document.getElementById("searchToggle").classList.remove("hidden");
}

    
      document.addEventListener("DOMContentLoaded", function () {
        const slider = document.getElementById('newsSlider');
        const slideLeft = document.getElementById('slideLeft');
        const slideRight = document.getElementById('slideRight');
    
        const scrollAmount = 800;
    
        slideLeft.addEventListener('click', () => {
          slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
    
        slideRight.addEventListener('click', () => {
          slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>