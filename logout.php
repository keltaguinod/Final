<?php
//destroy session and variables
session_start();
session_unset();     
session_destroy();

//back to dashboard
header("Location: dashboard.php");
exit();
?>