<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$id = (int)$_GET['id'];

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT url FROM imagenes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: admin.php');
    exit;
}

$imagen = $result->fetch_assoc();

$key = basename($imagen['url']);

$s3Client = getS3Client();
$bucket = getenv('AWS_BUCKET');

try {
    $s3Client->deleteObject([
        'Bucket' => $bucket,
        'Key' => $key,
    ]);
} catch (Exception $e) {
}

$deleteStmt = $conn->prepare("DELETE FROM imagenes WHERE id = ?");
$deleteStmt->bind_param("i", $id);
$deleteStmt->execute();
$deleteStmt->close();

$conn->close();

header('Location: admin.php');
exit;