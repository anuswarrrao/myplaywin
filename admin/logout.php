<?php
session_start();
session_unset();  
session_destroy(); 

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

header('Location: login.php');
exit();
?>
