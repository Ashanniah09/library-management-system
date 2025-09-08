<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'student') {
  header('Location: /libratrack/login.html'); exit;
}
?><!doctype html><html><body>
<h1>Student Home</h1>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['first_name']); ?>!</p>
</body></html>
