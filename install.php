<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$conn = getDBConnection();

$sqlImagenes = "CREATE TABLE IF NOT EXISTS imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    url VARCHAR(500) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlUsuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sqlImagenes) === TRUE) {
    echo "Tabla 'imagenes' creada o ya existe.<br>";
} else {
    echo "Error al crear tabla 'imagenes': " . $conn->error . "<br>";
}

if ($conn->query($sqlUsuarios) === TRUE) {
    echo "Tabla 'usuarios' creada o ya existe.<br>";
} else {
    echo "Error al crear tabla 'usuarios': " . $conn->error . "<br>";
}

$checkUser = $conn->query("SELECT id FROM usuarios WHERE usuario = '" . $conn->real_escape_string(getenv('ADMIN_USER')) . "'");
if ($checkUser->num_rows === 0) {
    $hashedPassword = password_hash(getenv('ADMIN_PASSWORD'), PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)");
    $stmt->bind_param("ss", getenv('ADMIN_USER'), $hashedPassword);
    if ($stmt->execute()) {
        echo "Usuario admin creado correctamente.<br>";
    }
    $stmt->close();
} else {
    echo "Usuario admin ya existe.<br>";
}

echo "<br>Instalación completada. <a href='index.php'>Ir a la página principal</a>";

$conn->close();
?>