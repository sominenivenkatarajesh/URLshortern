<?php
$conn = new mysqli("localhost", "root", "", "urlshortener");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['c'])) {
    http_response_code(400);
    echo "❌ Missing code.";
    exit;
}

$code = $_GET['c'];

if (!preg_match('/^[A-Za-z0-9\-_]{1,100}$/', $code)) {
    http_response_code(400);
    echo "❌ Invalid code.";
    exit;
}

$stmt = $conn->prepare("SELECT id, long_url FROM urls WHERE short_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $id = $row['id'];
    $long = $row['long_url'];

    $inc = $conn->prepare("UPDATE urls SET clicks = clicks + 1 WHERE id = ?");
    $inc->bind_param("i", $id);
    $inc->execute();

    header("Location: ".$long, true, 302);
    exit;
}

http_response_code(404);
echo "❌ Invalid short URL.";
exit;
