<?php
session_start();
session_destroy(); // Destroy all login data
header("Location: index.php"); // Send them back to the login screen
exit();
?>