<?php
header('Content-Type: application/json');
ini_set('display_errors','0'); ini_set('log_errors','1');

$host='localhost'; $user='root'; $pass=''; $db='libratrack';

function fail($msg,$field=null,$code=400){
  http_response_code($code);
  echo json_encode(['success'=>false,'message'=>$msg,'field'=>$field]); exit;
}

$req = json_decode(file_get_contents('php://input'), true) ?: [];
$email = trim($req['email'] ?? '');
$password = (string)($req['password'] ?? '');

if ($email==='') fail('Email address is required','email');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Invalid email address','email');
if ($password==='') fail('Password is required','password');

$mysqli = @new mysqli($host,$user,$pass,$db);
if ($mysqli->connect_errno) fail('Database connection failed', null, 500);

$sql = "SELECT id, first_name, last_name, email, password, role
        FROM users WHERE email=? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) fail('Account not found','email',401);
if (!password_verify($password, $user['password'])) fail('Incorrect password','password',401);

session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']);
session_start();
$_SESSION['user'] = [
  'id'=>(int)$user['id'],
  'first_name'=>$user['first_name'],
  'last_name'=>$user['last_name'],
  'email'=>$user['email'],
  'role'=>$user['role'],
];

$redirect = ($user['role'] === 'librarian')
  ? '/LibraTrack/librarian/librarian-dashboard.php'
  : '/LibraTrack/student/student-dashboard.php';

echo json_encode(['success' => true, 'redirect' => $redirect]);

