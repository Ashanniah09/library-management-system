<?php
$DB_HOST = "localhost";
$DB_USER = "root";       // default XAMPP user
$DB_PASS = "";           // default XAMPP password is blank
$DB_NAME = "libratrack"; // database name
$DB_PORT = 3307;         

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
