<?php
// $paginaActual debe definirse antes de incluir este archivo, ej: $paginaActual = 'index';
$paginaActual = $paginaActual ?? '';
$rol = rolActual();

function esActiva($nombre, $actual) {
    return $nombre === $actual ? ' class="activa"' : '';
}
?>
<header class="cabecera">
    <div class="cabecera-interior">
        <a href="index.php" class="logo">
            <span class="logo-icono" aria-hidden="true">
                <svg viewBox="0 0 40 40" width="34" height="34">
                    <path d="M20 4 C 20 4, 8 12, 8 22 C 8 30, 13.5 36, 20 36 C 26.5 36, 32 30, 32 22 C 32 12, 20 4, 20 4 Z" fill="none" stroke="currentColor" stroke-width="2"/>
                    <path d="M20 12 C 20 12, 20 24, 20 32" fill="none" stroke="currentColor" stroke-width="2"/>
                    <path d="M20 18 C 17 16, 13 17, 12 20" fill="none" stroke="currentColor" stroke-width="2"/>
                    <path d="M20 24 C 23 22, 27 23, 28 26" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
            </span>
            <span class="logo-texto">Clínica Vitalis</span>
        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="navegacion" id="navegacion">
            <a href="index.php"<?= esActiva('index', $paginaActual) ?>>Inicio</a>
            <a href="noticias.php"<?= esActiva('noticias', $paginaActual) ?>>Noticias</a>

            <?php if ($rol === 'user'): ?>
                <a href="citaciones.php"<?= esActiva('citaciones', $paginaActual) ?>>Mis citas</a>
                <a href="perfil.php"<?= esActiva('perfil', $paginaActual) ?>>Mi perfil</a>
            <?php elseif ($rol === 'admin'): ?>
                <a href="usuarios-administracion.php"<?= esActiva('usuarios-administracion', $paginaActual) ?>>Usuarios</a>
                <a href="citas-administracion.php"<?= esActiva('citas-administracion', $paginaActual) ?>>Citas</a>
                <a href="noticias-administracion.php"<?= esActiva('noticias-administracion', $paginaActual) ?>>Gestionar noticias</a>
                <a href="perfil.php"<?= esActiva('perfil', $paginaActual) ?>>Mi perfil</a>
            <?php endif; ?>

            <?php if ($rol === null): ?>
                <a href="login.php" class="nav-cta"<?= esActiva('login', $paginaActual) ?>>Iniciar sesión</a>
            <?php else: ?>
                <a href="logout.php" class="nav-cta nav-salir">Cerrar sesión</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<script>
document.getElementById('menuToggle').addEventListener('click', function () {
    const nav = document.getElementById('navegacion');
    const abierto = nav.classList.toggle('abierta');
    this.setAttribute('aria-expanded', abierto);
});
</script>
