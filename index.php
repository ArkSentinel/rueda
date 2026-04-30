<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Imágenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
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
            <a class="navbar-brand" href="index.php">Galería</a>
            <div class="d-flex">
                <a href="admin/login.php" class="btn btn-outline-light">Admin</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterImages('all')">Todas</button>
            <button class="filter-btn" onclick="filterImages('Naturaleza')">Naturaleza</button>
            <button class="filter-btn" onclick="filterImages('Ciudad')">Ciudad</button>
            <button class="filter-btn" onclick="filterImages('Personas')">Personas</button>
            <button class="filter-btn" onclick="filterImages('Animales')">Animales</button>
            <button class="filter-btn" onclick="filterImages('Tecnología')">Tecnología</button>
            <button class="filter-btn" onclick="filterImages('Arte')">Arte</button>
            <button class="filter-btn" onclick="filterImages('Otros')">Otros</button>
        </div>

        <?php
        require_once __DIR__ . '/vendor/autoload.php';
        require_once __DIR__ . '/config.php';

        $conn = getDBConnection();
        $categoria = $_GET['categoria'] ?? 'all';
        
        if ($categoria !== 'all') {
            $stmt = $conn->prepare("SELECT * FROM imagenes WHERE categoria = ? ORDER BY created_at DESC");
            $stmt->bind_param("s", $categoria);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT * FROM imagenes ORDER BY created_at DESC");
        }

        if ($result && $result->num_rows > 0):
            $images = $result->fetch_all(MYSQLI_ASSOC);
        ?>
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($images as $index => $img): ?>
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="<?= $index ?>" <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?>></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($images as $index => $img): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-categoria="<?= htmlspecialchars($img['categoria'] ?? 'Otros') ?>" data-bs-interval="5000">
                    <img src="<?= htmlspecialchars($img['url']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($img['titulo']) ?>">
                    <div class="carousel-caption d-none d-md-block">
                        <h5><?= htmlspecialchars($img['titulo']) ?></h5>
                        <p><?= htmlspecialchars($img['descripcion']) ?></p>
                        <?php if (!empty($img['categoria'])): ?>
                        <span class="badge badge-categoria"><?= htmlspecialchars($img['categoria']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <?php else: ?>
        <div class="alert alert-info">No hay imágenes disponibles. <a href="admin/login.php">Ingresa como admin para subir imágenes</a>.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterImages(categoria) {
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            if (categoria === 'all') {
                window.location.href = 'index.php';
            } else {
                window.location.href = 'index.php?categoria=' + categoria;
            }
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        const currentCategoria = urlParams.get('categoria') || 'all';
        const buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(btn => {
            if (btn.textContent.toLowerCase() === currentCategoria.toLowerCase() || 
                (currentCategoria === 'all' && btn.textContent === 'Todas')) {
                btn.classList.add('active');
            }
        });
    </script>
</body>
</html>