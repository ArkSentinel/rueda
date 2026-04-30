<?php

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        die("Error: Archivo .env no encontrado. Copia el archivo .env.example a .env y configura tus credenciales.\n");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

function getDBConnection() {
    $conn = new mysqli(
        getenv('DB_HOST'),
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        getenv('DB_NAME'),
        getenv('DB_PORT') ?: 3306
    );

    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

function getS3Client() {
    $credentials = new Aws\Credentials\Credentials(
        getenv('AWS_ACCESS_KEY_ID'),
        getenv('AWS_SECRET_ACCESS_KEY')
    );

    return new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => getenv('AWS_REGION'),
        'credentials' => $credentials
    ]);
}

function getS3Url($key) {
    return "https://" . getenv('AWS_BUCKET') . ".s3." . getenv('AWS_REGION') . ".amazonaws.com/" . $key;
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin/login.php');
        exit;
    }
}

function getCategorias() {
    return ['Naturaleza', 'Ciudad', 'Personas', 'Animales', 'Tecnología', 'Arte', 'Otros'];
}