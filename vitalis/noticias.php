<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'noticias';

$sql = "SELECT n.idNoticia, n.titulo, n.imagen, n.texto, n.fecha,
               u.nombre, u.apellidos
        FROM noticias n
        INNER JOIN users_data u ON u.idUser = n.idUser
        ORDER BY n.fecha DESC, n.idNoticia DESC";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="author" content="Nada Es Sabti">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Noticias — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Noticias</h1>
        <p>Novedades, campañas y avisos de la clínica.</p>
    </div>
</div>

<section class="seccion">
    <div class="contenedor">
        <?php if (mysqli_num_rows($resultado) === 0): ?>
            <p>Todavía no hay noticias publicadas. Vuelve pronto.</p>
        <?php else: ?>
            <?php while ($noticia = mysqli_fetch_assoc($resultado)): ?>
                <article class="noticia-tarjeta">
                    <div class="noticia-img">
                        <img src="<?= htmlspecialchars($noticia['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                             alt="Imagen de la noticia: <?= htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                             onerror="this.src='https://placehold.co/400x300/3C5847/F1EEE2?text=Vitalis'">
                    </div>
                    <div class="noticia-cuerpo">
                        <div class="noticia-meta">
                            <?= date('d/m/Y', strtotime($noticia['fecha'])) ?> · Por <?= htmlspecialchars($noticia['nombre'] . ' ' . $noticia['apellidos'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <h2><?= htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= nl2br(htmlspecialchars($noticia['texto'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
