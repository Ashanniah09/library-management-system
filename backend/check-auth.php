<?php
// backend/check-auth.php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../login.html');
  exit;
}
// Optionally expose the user info to the page:
$user = $_SESSION['user'];
