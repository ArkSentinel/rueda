<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');

if (empty($titulo)) {
    header('Location: admin.php?msg=error');
    exit;
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    header('Location: admin.php?msg=error');
    exit;
}

$file = $_FILES['archivo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $allowedTypes)) {
    header('Location: admin.php?msg=error');
    exit;
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_', true) . '.' . $extension;

$s3Client = getS3Client();
$bucket = getenv('AWS_BUCKET');

try {
    $result = $s3Client->putObject([
        'Bucket' => $bucket,
        'Key' => $filename,
        'SourceFile' => $file['tmp_name'],
        'ContentType' => $mimeType,
    ]);

    $url = getS3Url($filename);

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO imagenes (titulo, descripcion, categoria, url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $titulo, $descripcion, $categoria, $url);

    if ($stmt->execute()) {
        header('Location: admin.php?msg=success');
    } else {
        header('Location: admin.php?msg=error');
    }
    $stmt->close();
} catch (Exception $e) {
    header('Location: admin.php?msg=error');
}

$conn->close();
exit;