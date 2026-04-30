<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
requireAdmin();

$conn = getDBConnection();
$message = '';

if (isset($_GET['msg'])) {
    $message = $_GET['msg'] === 'success' ? 'Imagen subida correctamente' : 'Error al procesar la imagen';
}

$result = $conn->query("SELECT * FROM imagenes ORDER BY created_at DESC");
$imagenes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$categorias = getCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Galería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/styles.css" rel="stylesheet">
</head>
<body>
    <div class="animated-bg"></div>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin.php">Galería Admin</a>
            <div class="d-flex">
                <span class="navbar-text me-3"><?= htmlspecialchars($_SESSION['admin_usuario']) ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Subir Nueva Imagen</h2>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form action="subir_imagen.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="archivo" class="form-label">Imagen</label>
                            <input type="file" class="form-control" id="archivo" name="archivo" accept="image/*" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Subir imagen</button>
                </form>
            </div>
        </div>

        <h2 class="mb-4">Imágenes en la Base de Datos</h2>
        
        <?php if (empty($imagenes)): ?>
        <div class="alert alert-info">No hay imágenes cargadas.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Vista previa</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>URL</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imagenes as $img): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($img['url']) ?>" class="img-preview" alt="<?= htmlspecialchars($img['titulo']) ?>"></td>
                        <td><?= htmlspecialchars($img['titulo']) ?></td>
                        <td><?= htmlspecialchars($img['descripcion']) ?></td>
                        <td>
                            <?php if (!empty($img['categoria'])): ?>
                            <span class="badge badge-categoria"><?= htmlspecialchars($img['categoria']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= htmlspecialchars($img['url']) ?></small></td>
                        <td><?= date('d/m/Y H:i', strtotime($img['created_at'])) ?></td>
                        <td>
                            <a href="eliminar_imagen.php?id=<?= $img['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta imagen?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="../index.php" class="btn btn-secondary">Ver carrusel público</a>
        </div>
    </div>
</body>
</html>