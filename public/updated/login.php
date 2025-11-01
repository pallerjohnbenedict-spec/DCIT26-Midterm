<?php
session_start();
include 'conn.php';

if (isset($_POST['submit'])) {
    $email = $_POST['username'];
    $password_input = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password_input, $user['password'])) {
            $_SESSION['id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['student_id'] = $user['student_id'];


            // ✅ Get student_id if user is a student
            if ($user['role'] === 'student') {
                $user_id = $user['user_id'];
                $student_stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
                $student_stmt->bind_param("i", $user_id);
                $student_stmt->execute();
                $student_result = $student_stmt->get_result();

                if ($student_result->num_rows === 1) {
                    $student = $student_result->fetch_assoc();
                    $_SESSION['student_id'] = $student['student_id'];
                } else {
                    $_SESSION['student_id'] = null; // fallback in case student row not found
                }

                $student_stmt->close();
            }

            $stmt->close();
            $conn->close();

            // Redirect based on role
            switch ($user['role']) {
                case 'student':
                    header("Location: homepage.html"); // use PHP file if you want session access
                    break;
                case 'professor':
                    header("Location: instructor.php");
                    break;
                case 'admin':
                    header("Location: admin.php");
                    break;
                default:
                    header("Location: login.php?error=unknown_role");
            }
            exit;
        } else {
            $stmt->close();
            $conn->close();
            header("Location: login.php?error=invalid_password");
            exit;
        }
    } else {
        $stmt->close();
        $conn->close();
        header("Location: login.php?error=user_not_found");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <?php if (isset($_GET['error'])): ?>
        <div class="popup error">
            <?php
                switch ($_GET['error']) {
                    case 'invalid_password':
                        echo "❌ Invalid password.";
                        break;
                    case 'user_not_found':
                        echo "❌ User not found.";
                        break;
                    case 'unknown_role':
                        echo "⚠️ Unknown user role.";
                        break;
                    default:
                        echo "⚠️ Unknown error.";
                }
            ?>
        </div>
    <?php endif; ?>

    <section class="body">
        <div class="container">
            <div class="login-box">
                <h2>Login</h2>

                <form action="login.php" method="post">
                    <div class="input-field">
                        <input type="text" name="username" required>
                        <span class="logo"><ion-icon name="person-circle-outline"></ion-icon></span>
                        <label>Email</label>
                    </div>
                    <div class="input-field">
                        <input type="password" name="password" required>
                        <span class="logo"><ion-icon name="lock-closed"></ion-icon></span>
                        <label>Password</label>
                    </div>
                    <div class="remember">
                        <label><input type="checkbox">Remember Me</label>
                    </div>
                    <button type="submit" name="submit" class="button">Login</button>
                    <div class="sign_link">
                        <p>Don't have an account?
                            <a href="sign-up.php">Sign Up</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
