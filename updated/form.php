<?php
include 'conn.php'; // ensure this file contains your DB connection $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $contact = trim($_POST['contact']);
    $document_type_name = $_POST['document_type_name'];
    $purpose = trim($_POST['purpose']);
    $release_date = $_POST['release_date'];
    $request_date = date('Y-m-d H:i:s');



    $stmt = $conn->prepare("INSERT INTO document_requests (student_id, document_type_name, purpose, request_date, release_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $student_id, $document_type_name, $purpose, $request_date, $release_date);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Request submitted successfully!'); window.location.href='homepage.html';</script>";
    } else {
        echo "<script>alert('❌ Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

$documents = [];
$result = $conn->query("SELECT name FROM document_type");
if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="navbar.css" />
 
</head>
<body  onload="document.querySelector('.main-navbar').classList.add('show')">


    
  <!-- Top Navbar -->
  <div class="top-navbar">
  <div class="top-content text-center">
    <a href="homepage.html"><span class="top-text">Truth, Excellence, Service</span></a>
  </div>
</div>


 <div class="header2" id="banner2"></div>


<div class="form-wrapper">
  <h2>Fill in with your Information</h2>
  <form method="POST">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Student ID</label>
        <input type="text" class="form-control" placeholder="Enter your Student ID" name="student_id" required>
      </div>
      <div class="form-group">
        <label class="form-label">Contact number</label>
        <input type="text" class="form-control" placeholder="+63XXXXXXXXXX" name="contact" required>
      </div>
    </div>

    <!--
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Select document</label>
        <select class="form-select" required>
          <option disabled selected>Choose documents</option>
          <option>Transcript of Records (TOR)</option>
          <option>Certificate of Registration (COR)</option>
          <option>Certificate of Grades (COG)</option>
        </select>
      </div>
-->

      <div class="form-row">
        <div class="form-group">
        <label class="form-label">Select document</label>
        <select class="form-select" name="document_type_name" required>
          <option disabled selected>Choose document</option>
          <?php foreach ($documents as $doc): ?>
            <option value="<?= htmlspecialchars($doc) ?>"><?= htmlspecialchars($doc) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Community</label>
        <select class="form-select" required>
          <option disabled selected>Choose community</option>
          <option>Student</option>
          <option>Alumni</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Purpose of request</label>
      <input type="text" class="form-control" placeholder="Enter your purpose" name="purpose" required>
    </div>

    <div class="form-group">
      <label class="form-label">Release date</label>
      <input type="date" class="form-control" name="release_date" required>
    </div>

    <button type="submit" class="submit-btn">Submit Request</button>

    <div class="form-footer">
      We respect your data. By submitting this form, you agree that we may contact you for processing purposes in accordance with our <a href="#">Privacy Policy</a>.
    </div>
  </form>
</div>

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

   
</body>
</html>
