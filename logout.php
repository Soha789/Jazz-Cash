<?php
session_start();
session_unset();
session_destroy();

// Use JavaScript for redirection to prevent header errors
echo "<script>window.location.href = 'login.php';</script>";
exit();
?> 
