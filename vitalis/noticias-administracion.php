<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'noticias-administracion';

requiereRol('admin');
$errores = [];
$idUser = $_SESSION['idUser'];
$carpetaSubidas = 'uploads/noticias/';

function gestionarImagen($archivo, &$errores) {
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no se subió ninguna imagen nueva
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'Hubo un problema al subir la imagen.';
        return null;
    }
    $permitidas = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $tipo = mime_content_type($archivo['tmp_name']);
    if (!in_array($tipo, $permitidas, true)) {
        $errores[] = 'La imagen debe ser JPG, PNG, WEBP o GIF.';
        return null;
    }
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreArchivo = 'noticia_' . uniqid() . '.' . strtolower($extension);
    $destino = 'uploads/noticias/' . $nombreArchivo;
    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        $errores[] = 'No se pudo guardar la imagen en el servidor.';
        return null;
    }
    return $destino;
}

// --- Crear noticia ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $titulo = limpiar($_POST['titulo'] ?? '');
    $texto = limpiar($_POST['texto'] ?? '');
    $fecha = limpiar($_POST['fecha'] ?? '');

    if ($titulo === '') $errores[] = 'El título es obligatorio.';
    if ($texto === '') $errores[] = 'El texto de la noticia es obligatorio.';
    if ($fecha === '') $errores[] = 'La fecha es obligatoria.';

    $rutaImagen = null;
    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "SELECT idNoticia FROM noticias WHERE titulo=?");
        mysqli_stmt_bind_param($stmt, 's', $titulo);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) $errores[] = 'Ya existe una noticia con ese título.';
        mysqli_stmt_close($stmt);

        $rutaImagen = gestionarImagen($_FILES['imagen'] ?? null, $errores);
        if (!$rutaImagen && empty($errores)) $errores[] = 'Debes subir una imagen para la noticia.';
    }

    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "INSERT INTO noticias (titulo, imagen, texto, fecha, idUser) VALUES (?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssssi', $titulo, $rutaImagen, $texto, $fecha, $idUser);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setMensaje('exito', 'Noticia publicada correctamente.');
        redirigir('noticias-administracion.php');
    }
}

// --- Editar noticia ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $idNoticia = (int) ($_POST['idNoticia'] ?? 0);
    $titulo = limpiar($_POST['titulo'] ?? '');
    $texto = limpiar($_POST['texto'] ?? '');
    $fecha = limpiar($_POST['fecha'] ?? '');

    if ($titulo === '' || $texto === '' || $fecha === '') {
        $errores[] = 'Revisa los campos obligatorios de la noticia.';
    }

    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "SELECT idNoticia FROM noticias WHERE titulo=? AND idNoticia<>?");
        mysqli_stmt_bind_param($stmt, 'si', $titulo, $idNoticia);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) $errores[] = 'Ya existe otra noticia con ese título.';
        mysqli_stmt_close($stmt);
    }

    $rutaImagen = gestionarImagen($_FILES['imagen'] ?? null, $errores);

    if (empty($errores)) {
        if ($rutaImagen) {
            $stmt = mysqli_prepare($conexion, "UPDATE noticias SET titulo=?, imagen=?, texto=?, fecha=? WHERE idNoticia=?");
            mysqli_stmt_bind_param($stmt, 'ssssi', $titulo, $rutaImagen, $texto, $fecha, $idNoticia);
        } else {
            $stmt = mysqli_prepare($conexion, "UPDATE noticias SET titulo=?, texto=?, fecha=? WHERE idNoticia=?");
            mysqli_stmt_bind_param($stmt, 'sssi', $titulo, $texto, $fecha, $idNoticia);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setMensaje('exito', 'Noticia actualizada correctamente.');
        redirigir('noticias-administracion.php');
    }
}

// --- Borrar noticia ---
if (isset($_GET['borrar'])) {
    $idNoticia = (int) $_GET['borrar'];
    $stmt = mysqli_prepare($conexion, "DELETE FROM noticias WHERE idNoticia=?");
    mysqli_stmt_bind_param($stmt, 'i', $idNoticia);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    setMensaje('exito', 'Noticia eliminada correctamente.');
    redirigir('noticias-administracion.php');
}

$noticias = mysqli_query($conexion,
    "SELECT n.*, u.nombre, u.apellidos
     FROM noticias n INNER JOIN users_data u ON u.idUser = n.idUser
     ORDER BY n.fecha DESC, n.idNoticia DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="author" content="Nada Es Sabti">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de noticias — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Gestión de noticias</h1>
        <p>Publica novedades y gestiona el contenido visible en la página de noticias.</p>
    </div>
</div>

<section class="seccion">
    <div class="contenedor">
        <?php pintarMensaje(); ?>
        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="panel-cabecera">
            <h3 style="margin:0;">Noticias publicadas</h3>
            <button class="boton" onclick="document.getElementById('crearNoticia').showModal()">+ Nueva noticia</button>
        </div>

        <div style="overflow-x:auto;">
        <table class="tabla-admin">
            <thead><tr><th>Imagen</th><th>Título</th><th>Fecha</th><th>Autor</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php while ($n = mysqli_fetch_assoc($noticias)): ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($n['imagen'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:60px;height:44px;object-fit:cover;border-radius:4px;" onerror="this.src='https://placehold.co/60x44/3C5847/F1EEE2?text=V'"></td>
                    <td><?= htmlspecialchars($n['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= date('d/m/Y', strtotime($n['fecha'])) ?></td>
                    <td><?= htmlspecialchars($n['nombre'] . ' ' . $n['apellidos'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="acciones-tabla">
                        <button class="boton boton-secundario boton-pequeno" onclick="document.getElementById('editarNoticia-<?= $n['idNoticia'] ?>').showModal()">Editar</button>
                        <a class="boton boton-peligro boton-pequeno" href="noticias-administracion.php?borrar=<?= $n['idNoticia'] ?>" onclick="return confirm('¿Borrar esta noticia?')">Borrar</a>
                    </td>
                </tr>

                <dialog id="editarNoticia-<?= $n['idNoticia'] ?>" style="border:none;border-radius:8px;padding:0;max-width:600px;width:92%;">
                    <form method="POST" action="noticias-administracion.php" enctype="multipart/form-data" class="tarjeta">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="idNoticia" value="<?= $n['idNoticia'] ?>">
                        <h3>Editar noticia</h3>
                        <div class="campo"><label>Título</label><input type="text" name="titulo" value="<?= htmlspecialchars($n['titulo'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="campo"><label>Fecha</label><input type="date" name="fecha" value="<?= $n['fecha'] ?>" required></div>
                        <div class="campo"><label>Texto</label><textarea name="texto" rows="5" required><?= htmlspecialchars($n['texto'], ENT_QUOTES, 'UTF-8') ?></textarea></div>
                        <div class="campo">
                            <label>Nueva imagen (opcional)</label>
                            <input type="file" name="imagen" accept="image/*">
                            <small>Déjalo vacío para mantener la imagen actual.</small>
                        </div>
                        <button type="submit" class="boton">Guardar cambios</button>
                        <button type="button" class="boton boton-secundario" onclick="document.getElementById('editarNoticia-<?= $n['idNoticia'] ?>').close()">Cancelar</button>
                    </form>
                </dialog>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>

<dialog id="crearNoticia" style="border:none;border-radius:8px;padding:0;max-width:600px;width:92%;">
    <form method="POST" action="noticias-administracion.php" enctype="multipart/form-data" class="tarjeta">
        <input type="hidden" name="accion" value="crear">
        <h3>Nueva noticia</h3>
        <div class="campo"><label>Título <span class="obligatorio">*</span></label><input type="text" name="titulo" required></div>
        <div class="campo"><label>Fecha <span class="obligatorio">*</span></label><input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required></div>
        <div class="campo"><label>Texto <span class="obligatorio">*</span></label><textarea name="texto" rows="5" required></textarea></div>
        <div class="campo"><label>Imagen <span class="obligatorio">*</span></label><input type="file" name="imagen" accept="image/*" required></div>
        <button type="submit" class="boton">Publicar noticia</button>
        <button type="button" class="boton boton-secundario" onclick="document.getElementById('crearNoticia').close()">Cancelar</button>
    </form>
</dialog>

<?php include 'includes/footer.php'; ?>
</body>
</html>
