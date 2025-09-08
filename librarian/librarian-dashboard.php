<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'librarian') {
  header('Location: /libratrack/login.html'); exit;
}
?><!doctype html><html><body>
<h1>Librarian Dashboard</h1>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['first_name']); ?>!</p>
</body></html>
