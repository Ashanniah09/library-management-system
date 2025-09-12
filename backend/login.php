<?php
// backend/login.php
header('Content-Type: application/json');
ini_set('display_errors','0'); ini_set('log_errors','1');

// ---- DB config (match with register.php) ----
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'libratrack';

// ---- Helpers ----
function bad($msg, $field = null, $code = 400) {
  http_response_code($code);
  echo json_encode(['success'=>false,'message'=>$msg,'field'=>$field]);
  exit;
}

// ---- Read JSON ----
$raw = file_get_contents('php://input');
$req = json_decode($raw, true) ?: [];
$email = trim($req['email'] ?? '');
$password = $req['password'] ?? '';

if ($email === '') bad('Email address is required', 'email');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) bad('Invalid email address', 'email');
if ($password === '') bad('Password is required', 'password');

// ---- DB connect ----
$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) bad('Database connection failed', null, 500);

// ---- Lookup user ----
$sql = "SELECT id, first_name, last_name, email, password FROM users WHERE email=? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$userRow = $res->fetch_assoc();
$stmt->close();

if (!$userRow) bad('Account not found', 'email', 401);

// ---- Verify password ----
if (!password_verify($password, $userRow['password'])) {
  bad('Incorrect password', 'password', 401);
}

// ---- Start secure session & store minimal identity ----
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'secure'   => isset($_SERVER['HTTPS']),
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();
$_SESSION['user'] = [
  'id'         => (int)$userRow['id'],
  'first_name' => $userRow['first_name'],
  'last_name'  => $userRow['last_name'],
  'email'      => $userRow['email'],
];

// ---- Respond OK ----
echo json_encode([
  'success' => true,
  'message' => 'Login successful',
  // optional: where you want to go after login
  'redirect' => '../librarian-dashboard.html'
]);
