<?php
include 'conn.php';

if (isset($_POST['submit'])) {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password_raw = $_POST['password'];
    $contact_number = $_POST['contact_number'];
    $role = $_POST['role'];
    $student_id = $_POST['student_id'] ?? null; // Will be null for non-students

    // Hash the password
    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, contact_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password, $role, $contact_number);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // If student, insert into students table
        if ($role === 'student' && !empty($student_id)) {
            $student_stmt = $conn->prepare("INSERT INTO students (student_id, user_id) VALUES (?, ?)");
            $student_stmt->bind_param("si", $student_id, $user_id);
            $student_stmt->execute();
            $student_stmt->close();
        }

        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Registration Page</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <section class="body">
        <div class="sign-up">
            <div class="sign-box">
                <h2>Registration</h2>
                <form action="sign-up.php" method="POST" class="reg-form">
                    <div class="sign-field">
                        <input type="text" name="first_name" required>
                        <span class="sign-logo"><ion-icon name="person-circle-outline"></ion-icon></span>
                        <label>First Name</label>
                    </div>
                    <div class="sign-field">
                        <input type="text" name="last_name" required>
                        <span class="sign-logo"><ion-icon name="person-circle-outline"></ion-icon></span>
                        <label>Last Name</label>
                    </div>
                    <div class="sign-field">
                        <input type="email" name="email" required>
                        <span class="sign-logo"><ion-icon name="mail-outline"></ion-icon></span>
                        <label>Email</label>
                    </div>
                    <div class="sign-field">
                        <input type="password" name="password" required>
                        <span class="sign-logo"><ion-icon name="lock-closed-outline"></ion-icon></span>
                        <label>Password</label>
                    </div>
                    <div class="sign-field">
                        <input type="tel" maxlength="11" name="contact_number" required>
                        <span class="sign-logo"><ion-icon name="call-outline"></ion-icon></span>
                        <label>Contact Number</label>
                    </div>

                    <!-- Student ID Field - Hidden by default -->
                    <div class="sign-field" id="student_id_field" style="display: none;">
                        <input type="text" name="student_id">
                        <span class="sign-logo"><ion-icon name="card-outline"></ion-icon></span>
                        <label>Student ID</label>
                    </div>

                    <!-- Role Dropdown -->
                    <div class="sign-choice">
                        <select class="choice" name="role" id="role" required onchange="toggleStudentIdField()">
                            <option value="">Select a Status</option>
                            <option value="student">Student</option>
                            <option value="professor">Instructor</option>
                            <option value="admin">Registrar</option>
                        </select>
                        <div class="icon">
                            <ion-icon name="caret-down-outline"></ion-icon>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="btn">Register</button>
                    <div class="sign_link">
                        <p>Have an account? <a href="login.php">Login</a></p>
                    </div>
                </form>

                <!-- JavaScript for toggling student_id field -->
                <script>
                    function toggleStudentIdField() {
                        const role = document.getElementById('role').value;
                        const studentField = document.getElementById('student_id_field');
                        studentField.style.display = (role === 'student') ? 'block' : 'none';
                    }

                    // On page load (if browser remembers role selection)
                    window.onload = toggleStudentIdField;
                </script>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
