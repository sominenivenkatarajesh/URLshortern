<?php
$conn = new mysqli("localhost", "root", "", "urlshortener");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM urls WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: history.php");
exit;
?>
