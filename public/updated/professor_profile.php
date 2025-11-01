<?php
session_start();

include 'conn.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'professor') {
    die("Access denied.");
}

$user_id = $_SESSION['id'];

// Fetch existing profile (if any)
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'professor'");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    if (!$first_name || !$last_name || !$email) {
        $error = "All fields are required.";
    } else {
        try {
            if ($profile) {
                // Update
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
                $stmt->execute([$first_name, $last_name, $email, $user_id]);
                $message = "Profile updated successfully.";
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO users (user_id, first_name, last_name, email, role) VALUES (?, ?, ?, ?, 'professor')");
                $stmt->execute([$user_id, $first_name, $last_name, $email]);
                $message = "Profile created successfully.";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Professor Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>.top-navbar {
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
.header2 {
      background-image: url('upload/profile.png'); /* Replace with your image path */
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
    } body.bg-light {
  font-family: 'Segoe UI', sans-serif;
}

.card {
  background: #fff;
  border-radius: 1rem;
  border: none;
}

.card h2 {
  font-weight: 600;
  color: #198754;
}

label.form-label {
  font-size: 0.95rem;
  color: #333;
}

input.form-control {
  border-radius: 0.5rem;
  border: 1px solid #ced4da;
  padding: 0.65rem;
}

input.form-control:focus {
  border-color: #198754;
  box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.btn-success {
  border-radius: 50px;
  padding: 0.5rem 1.5rem;
}

.btn-outline-secondary {
  border-radius: 50px;
  padding: 0.5rem 1.5rem;
}

/* Optional animation with Animate.css (include via CDN) */
@import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');
</style>
<!-- Top Navbar -->
  <div class="top-navbar">
  <div class="top-content text-center">
    <span class="top-text">Truth, Excellence, Service</span> 
  </div>
</div>


 <div class="header2" id="banner2"></div>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-lg border-0 rounded-4 p-4 animate__animated animate__fadeIn">
          <div class="card-body">
            <h2 class="text-success mb-4">
              <i class="fas fa-user-circle me-2"></i>Professor Profile
            </h2>

            <?php if ($message): ?>
              <div class="alert alert-success">
                <i class="fas fa-check-circle me-1"></i><?= $message; ?>
              </div>
            <?php elseif ($error): ?>
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i><?= $error; ?>
              </div>
            <?php endif; ?>

            <form method="POST">
              <div class="mb-3">
                <label for="first_name" class="form-label fw-semibold">First Name</label>
                <input type="text" class="form-control" name="first_name" id="first_name"
                  value="<?= $profile['first_name'] ?? '' ?>" required>
              </div>
              <div class="mb-3">
                <label for="last_name" class="form-label fw-semibold">Last Name</label>
                <input type="text" class="form-control" name="last_name" id="last_name"
                  value="<?= $profile['last_name'] ?? '' ?>" required>
              </div>
              <div class="mb-4">
                <label for="email" class="form-label fw-semibold">Contact Email</label>
                <input type="email" class="form-control" name="email" id="email"
                  value="<?= $profile['email'] ?? '' ?>" required>
              </div>

              <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-save me-2"></i>Save Profile
                </button>
                <a href="instructor.php" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-2"></i>Back to Grade Upload
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

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
