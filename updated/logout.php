<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // Change this to your actual login page
exit;
?>
