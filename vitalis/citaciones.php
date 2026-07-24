<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'citaciones';

requiereRol('user');
$idUser = $_SESSION['idUser'];
$errores = [];
$hoy = date('Y-m-d');

// --- Crear nueva cita ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $fecha_cita = limpiar($_POST['fecha_cita'] ?? '');
    $motivo_cita = limpiar($_POST['motivo_cita'] ?? '');

    if ($fecha_cita === '') {
        $errores[] = 'La fecha de la cita es obligatoria.';
    } elseif ($fecha_cita < $hoy) {
        $errores[] = 'No puedes solicitar una cita en una fecha pasada.';
    }

    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iss', $idUser, $fecha_cita, $motivo_cita);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setMensaje('exito', 'Cita solicitada correctamente.');
        redirigir('citaciones.php');
    }
}

// --- Modificar cita existente (solo si no es pasada) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $idCita = (int) ($_POST['idCita'] ?? 0);
    $fecha_cita = limpiar($_POST['fecha_cita'] ?? '');
    $motivo_cita = limpiar($_POST['motivo_cita'] ?? '');

    if ($fecha_cita === '' || $fecha_cita < $hoy) {
        $errores[] = 'La nueva fecha debe ser hoy o posterior.';
    }

    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion,
            "UPDATE citas SET fecha_cita = ?, motivo_cita = ?
             WHERE idCita = ? AND idUser = ? AND fecha_cita >= ?");
        mysqli_stmt_bind_param($stmt, 'ssiis', $fecha_cita, $motivo_cita, $idCita, $idUser, $hoy);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setMensaje('exito', 'Cita actualizada correctamente.');
        redirigir('citaciones.php');
    }
}

// --- Borrar cita (solo si no es pasada) ---
if (isset($_GET['borrar'])) {
    $idCita = (int) $_GET['borrar'];
    $stmt = mysqli_prepare($conexion, "DELETE FROM citas WHERE idCita = ? AND idUser = ? AND fecha_cita >= ?");
    mysqli_stmt_bind_param($stmt, 'iis', $idCita, $idUser, $hoy);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    setMensaje('exito', 'Cita eliminada correctamente.');
    redirigir('citaciones.php');
}

// --- Listado de citas del usuario ---
$stmt = mysqli_prepare($conexion, "SELECT * FROM citas WHERE idUser = ? ORDER BY fecha_cita ASC");
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$citas = mysqli_stmt_get_result($stmt);

$meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="author" content="Nada Es Sabti">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis citas — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Mis citas</h1>
        <p>Solicita una nueva cita o gestiona las que ya tienes planificadas.</p>
    </div>
</div>

<section class="seccion">
    <div class="contenedor rejilla-2">
        <div>
            <h3>Tus citas</h3>
            <?php pintarMensaje(); ?>
            <?php if (!empty($errores)): ?>
                <div class="alerta alerta-error">
                    <ul style="margin:0; padding-left:18px;">
                        <?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (mysqli_num_rows($citas) === 0): ?>
                <p>Todavía no tienes citas solicitadas.</p>
            <?php else: ?>
                <?php while ($c = mysqli_fetch_assoc($citas)):
                    $esPasada = $c['fecha_cita'] < $hoy;
                    $ts = strtotime($c['fecha_cita']);
                ?>
                <div class="ticket <?= $esPasada ? 'ticket-vencida' : '' ?>">
                    <div class="ticket-fecha">
                        <span class="dia"><?= date('d', $ts) ?></span>
                        <span class="mes"><?= $meses[(int) date('n', $ts) - 1] ?> <?= date('Y', $ts) ?></span>
                    </div>
                    <div class="ticket-perforacion"></div>
                    <div class="ticket-cuerpo">
                        <div>
                            <div class="motivo"><?= htmlspecialchars($c['motivo_cita'] ?: 'Sin motivo especificado', ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if ($esPasada): ?><span class="etiqueta-vencida">Ya realizada</span><?php endif; ?>
                        </div>
                        <?php if (!$esPasada): ?>
                        <div class="ticket-acciones">
                            <button type="button" class="boton boton-secundario boton-pequeno" onclick="document.getElementById('editar-<?= $c['idCita'] ?>').showModal()">Modificar</button>
                            <a href="citaciones.php?borrar=<?= $c['idCita'] ?>" class="boton boton-peligro boton-pequeno" onclick="return confirm('¿Seguro que quieres borrar esta cita?')">Borrar</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$esPasada): ?>
                <dialog id="editar-<?= $c['idCita'] ?>" style="border:none;border-radius:8px;padding:0;max-width:480px;width:90%;">
                    <form method="POST" action="citaciones.php" class="tarjeta">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="idCita" value="<?= $c['idCita'] ?>">
                        <h3>Modificar cita</h3>
                        <div class="campo">
                            <label>Fecha</label>
                            <input type="date" name="fecha_cita" value="<?= $c['fecha_cita'] ?>" min="<?= $hoy ?>" required>
                        </div>
                        <div class="campo">
                            <label>Motivo</label>
                            <textarea name="motivo_cita" rows="3"><?= htmlspecialchars($c['motivo_cita'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <button type="submit" class="boton">Guardar cambios</button>
                        <button type="button" class="boton boton-secundario" onclick="document.getElementById('editar-<?= $c['idCita'] ?>').close()">Cancelar</button>
                    </form>
                </dialog>
                <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <div>
            <div class="tarjeta">
                <h3>Solicitar nueva cita</h3>
                <form method="POST" action="citaciones.php" class="formulario">
                    <input type="hidden" name="accion" value="crear">
                    <div class="campo">
                        <label>Fecha <span class="obligatorio">*</span></label>
                        <input type="date" name="fecha_cita" min="<?= $hoy ?>" required>
                    </div>
                    <div class="campo">
                        <label>Motivo de la cita</label>
                        <textarea name="motivo_cita" rows="4" placeholder="Cuéntanos brevemente el motivo de tu visita"></textarea>
                    </div>
                    <button type="submit" class="boton">Solicitar cita</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
