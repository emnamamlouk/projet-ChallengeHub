<?php
session_start();
$_SESSION['is_admin'] = 1;
header('Location: index.php?action=admin');
exit();
?>