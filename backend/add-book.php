<?php
require __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["ok" => false, "error" => "Use POST"], 405);
}

$title     = trim($_POST["title"] ?? "");
$isbn      = trim($_POST["isbn"] ?? "");
$author    = trim($_POST["author"] ?? "");
$category  = trim($_POST["category"] ?? "");
$quantity  = intval($_POST["quantity"] ?? 0);
$publisher = trim($_POST["publisher"] ?? "");
$date_pub  = trim($_POST["date_published"] ?? "");

if ($title === "" || $isbn === "" || $author === "" || $category === "" || $publisher === "" || $quantity <= 0) {
    json_response(["ok" => false, "error" => "Please fill all required fields."], 400);
}

// handle cover image
$coverName = null;
if (!empty($_FILES["cover"]["name"])) {
    $dir = __DIR__ . "/../uploads/covers";
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    $ext = strtolower(pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg","jpeg","png","gif","webp"];
    if (!in_array($ext, $allowed)) {
        json_response(["ok" => false, "error" => "Invalid image type"], 400);
    }
    $coverName = uniqid("cover_", true) . "." . $ext;
    $dest = $dir . "/" . $coverName;
    if (!move_uploaded_file($_FILES["cover"]["tmp_name"], $dest)) {
        json_response(["ok" => false, "error" => "Failed to save image"], 500);
    }
}

// insert book
$stmt = $conn->prepare("
    INSERT INTO books (title, isbn, author, category, quantity, publisher, date_published, cover)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$dp = $date_pub !== "" ? $date_pub : null;
$stmt->bind_param("ssssisss", $title, $isbn, $author, $category, $quantity, $publisher, $dp, $coverName);

if (!$stmt->execute()) {
    $err = $conn->errno === 1062 ? "ISBN already exists." : "Insert failed.";
    json_response(["ok" => false, "error" => $err], 400);
}

json_response(["ok" => true, "id" => $stmt->insert_id]);
