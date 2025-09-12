<?php
// backend/register.php
header('Content-Type: application/json');

// hide raw errors to browser, log instead
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ---- DB config (phpMyAdmin setup) ----
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'libratrack';

// connect
$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Database connection failed.']);
  exit;
}

// read JSON body
$raw = file_get_contents('php://input');
$req = json_decode($raw, true) ?: [];

// quick helper
function fail($msg, $field=null, $code=400) {
  http_response_code($code);
  echo json_encode(['success'=>false,'message'=>$msg,'field'=>$field]);
  exit;
}

// collect
$first  = trim($req['first_name'] ?? '');
$middle = trim($req['middle_name'] ?? '');
$last   = trim($req['last_name'] ?? '');
$suffix = trim($req['suffix'] ?? '');
$email  = trim($req['email'] ?? '');
$pass   = (string)($req['password'] ?? '');
$cpass  = (string)($req['confirm_password'] ?? '');

// validate
$letters = '/^[A-Za-z\s]+$/';
if ($first === '' || !preg_match($letters, $first)) fail('Invalid first name','firstName');
if ($last  === '' || !preg_match($letters, $last))  fail('Invalid last name','lastName');
if ($middle !== '' && !preg_match($letters,$middle)) fail('Invalid middle name','middleName');
if ($suffix === '') fail('Select suffix','suffix');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Invalid email','email');

if (strlen($pass) < 8 || !preg_match('/[A-Z]/',$pass) || !preg_match('/[^A-Za-z0-9]/',$pass)) {
  fail('Password must be 8+ chars with 1 uppercase & 1 special','password');
}
if ($pass !== $cpass) fail('Passwords do not match','confirmPassword');

// unique email
$chk = $mysqli->prepare("SELECT 1 FROM users WHERE email=? LIMIT 1");
$chk->bind_param("s", $email);
$chk->execute(); $chk->store_result();
if ($chk->num_rows > 0) fail('Email already registered','email',409);
$chk->close();

// insert
$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO users (first_name,middle_name,last_name,suffix,email,password) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssssss", $first,$middle,$last,$suffix,$email,$hash);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Insert failed.']);
  exit;
}

echo json_encode(['success'=>true,'message'=>'Registration successful!']);
