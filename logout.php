<?php
require_once 'includes/auth.php';
logoutUser();
setFlash('success', 'You have been logged out.');
redirect('index.php');
?>
