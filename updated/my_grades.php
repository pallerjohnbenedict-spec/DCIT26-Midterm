<?php
include 'conn.php';
session_start();

// Ensure student is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];


// Get corresponding student_id
$stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();

$grades = [];

if ($student_id) {
    $stmt = $conn->prepare("SELECT subject, grade, date_recorded, is_verified FROM grades WHERE student_id = ? ORDER BY date_recorded DESC");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    $stmt->close();
}


// Fetch user details (email, name)
$stmt = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $email = $row['email'];
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $first_letter = strtoupper(substr($first_name, 0, 1));
} else {
    $email = '';
    $first_name = 'Student';
    $last_name = '';
    $first_letter = '?';
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="profile.css" />
  <link rel="stylesheet" href="style.css">
</head>
<body>
    <style>
    .requests-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-top: 20px;
        width: 100%;
    }
    .requests-table th {
        background: linear-gradient(135deg,rgb(9, 87, 28) 0%,rgb(44, 237, 118) 100%);
        color: white;
        padding: 15px;
        text-align: left;
    }
    .requests-table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .rejection-note {
        font-size: 14px;
        color: #721c24;
        font-style: italic;
    }
  </style>
</head>
<body>

<!-- Sidebar Profile Layout -->
<div class="profile-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="profile-picture">
      <div class="circle"><?= $first_letter ?></div>
      <p class="name"><?= $first_name ?></p>
      <p class="email"><?= $email ?></p>
    </div>
    <nav class="menu">
      <ul class="sidebar-menu">
        <li><a href="profile.php">Profile</a></li>
        <li><a href="my_requests.php" >My Requests</a></li>
        <li><a href="my_grades.php" >My Grades</a></li>
        <a href="logout.php" class="btn btn-danger" style="float: right; margin: 10px;">Logout</a>
      </ul>
    </nav>
  </aside>

    <!-- Main Navbar -->
<nav class="main-navbar">
  <div class="navbar-container">
    <!-- Logo & Name -->
    <a href="homepage.html" class="navbar-left-link">
      <div class="navbar-left">
        <img src="upload/Cavite_State_University__CvSU_-removebg-preview.png" alt="CvSU Logo" />
        <div class="campus-text">
          <span class="school-name">CvSU</span><br />
          <span class="campus-name">BACOOR CAMPUS</span>
        </div>
      </div>
    </a>

    <!-- Navigation Links -->
    <div class="navbar-links">
      <!-- About Dropdown -->
      <div class="dropdown mega-dropdown">
        <button class="dropbtn">About <i class="fas fa-angle-down"></i></button>
        <div class="mega-menu">
          <div class="mega-column">
            <h4>Story of CvSU</h4>
            <a href="about.html">The History of CvSU</a>
            <a href="about.html">Vision and Mission</a>
            <a href="about.html">Core Values</a>
          </div>
        </div>
      </div>

      <!-- Documents Dropdown -->
      <div class="dropdown mega-dropdown">
        <button class="dropbtn">Documents <i class="fas fa-angle-down"></i></button>
        <div class="mega-menu">
          <div class="mega-column">
            <h4>Documents</h4>
            <a href="ad.html">Request Document</a>
          </div>
        </div>
      </div>

      <!-- Academics Dropdown -->
      <div class="dropdown mega-dropdown">
        <button class="dropbtn">Academics <i class="fas fa-angle-down"></i></button>
        <div class="mega-menu">
          <div class="mega-column">
            <h4>Schedules</h4>
            <a href="#">Class Schedule</a>
            <a href="#">Academic Calendar</a>
          </div>
          <div class="mega-column">
            <h4>Track Progress</h4>
            <a href="#">Exam Results</a>
            <a href="#">Quizzes & Activities</a>
          </div>
        </div>
      </div>

      <!-- Profile Dropdown -->
      <div class="dropdown mega-dropdown profile-dropdown">
        <button class="dropbtn">
          <i class="fas fa-user-circle"></i> Profile <i class="fas fa-angle-down"></i>
        </button>
        <div class="mega-menu profile-menu">
          <div class="mega-column">
            <a href="profile.php"><i class="fas fa-user"></i> View Profile</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>

  <!-- Main Content -->
  <main class="main-content">
    <header>
        <h2>My Grades</h2>
        <p>Here are your Grades</p>
    </header>
    <div class="user-info">
    <section class="grades-section">
        <div class="grades-card">
            <h3>View Your Grades</h3>
            <?php if (empty($grades)): ?>
                <p class="text-muted">No grades available yet.</p>
            <?php else: ?>
                <table class="grades-table" width="100%">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Date Recorded</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $g): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($g['subject']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($g['grade']); ?></strong>
                                </td>
                                <td><?php echo date("M d, Y", strtotime($g['date_recorded'])); ?></td>
                                <td>
                                    <?php echo $g['is_verified'] ? '<span style="color: green;">Verified</span>' : '<span style="color: orange;">Pending</span>'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
                        </div>
  </main>

<footer class="site-footer" id="contact" data-aos="fade-up" data-aos-duration="1000">
  <div class="container">
    <div class="footer-content">
      <div class="footer-left">
        <h3>Contact Us</h3>
        <p>
          <i class="fab fa-facebook fa-lg me-2"></i>
          <a href="https://facebook.com/CvSU" target="_blank">facebook.com/CvSU</a>
        </p>
        <p>
          <i class="fa fa-envelope"></i>
        cvsubacoor@cvsu.edu.ph
        </p>
        <p>
          <i class="fas fa-map-marker-alt me-2"></i>
          CvSU Bacoor Campus, Molino Boulevard, Bacoor, Cavite
        </p>
        <p>
          <i class="fas fa-phone-alt me-2"></i>
               Call: (046) 476-5029
        </p>
      </div>


      <div class="footer-right map-container">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3876.7165479642957!2d120.97426267506408!3d14.450531182378013!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33962c18115c2cc1%3A0x4a79cbb62b5a16c1!2sCvSU%20Bacoor%20Campus!5e0!3m2!1sen!2sph!4v1625657413524!5m2!1sen!2sph"
          width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"
        ></iframe>
      </div>
    </div>

    <div class="footer-bottom text-center">
      <p>&copy; 2025 Cavite State University - Bacoor Campus. All Rights Reserved.</p>
      <p>
        <a href="privacy.html">Privacy Policy</a> |
        <a href="terms.html">Terms of Use</a>
      </p>
    </div>
  </div>
</footer>



<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init();
</script>

  
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    offset: 120,
    once: false,   // Allows animation to trigger every time you scroll up/down into view
    mirror: true   // Animates elements out when scrolling past them
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


</body>
</html>