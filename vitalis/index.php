<?php
require_once 'includes/functions.php';
$paginaActual = 'index';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="author" content="Nada Es Sabti">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clínica Vitalis — Medicina integrativa</title>
<meta name="description" content="Clínica Vitalis, medicina integrativa y bienestar. Solicita tu cita online.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<section class="hero">
    <div class="hero-interior">
        <div>
            <span class="eyebrow">Medicina integrativa · Valencia</span>
            <h1>Cuidar de ti, con tiempo y con criterio</h1>
            <p>En Clínica Vitalis combinamos seguimiento médico personalizado, naturopatía y bienestar emocional en un mismo espacio. Un equipo, un historial, una sola cita.</p>
            <div class="hero-acciones">
                <a href="registro.php" class="boton">Regístrate</a>
                <a href="noticias.php" class="boton boton-secundario" style="color:#fff;border-color:rgba(255,255,255,0.6);">Ver noticias</a>
            </div>
        </div>
        <div class="hero-figura">
            <svg viewBox="0 0 320 260" xmlns="http://www.w3.org/2000/svg">
                <circle cx="160" cy="130" r="92" fill="none" stroke="#D9B27C" stroke-width="1.5" opacity="0.6"/>
                <path d="M160 60 C 160 60, 96 96, 96 150 C 96 190, 124 218, 160 218 C 196 218, 224 190, 224 150 C 224 96, 160 60, 160 60 Z" fill="none" stroke="#F1EEE2" stroke-width="2"/>
                <path d="M160 88 L160 200" stroke="#F1EEE2" stroke-width="2"/>
                <path d="M160 110 C 148 100, 128 104, 122 118" stroke="#F1EEE2" stroke-width="2" fill="none"/>
                <path d="M160 140 C 172 130, 192 134, 198 148" stroke="#F1EEE2" stroke-width="2" fill="none"/>
                <path d="M160 168 C 148 160, 130 163, 124 176" stroke="#F1EEE2" stroke-width="2" fill="none"/>
            </svg>
        </div>
    </div>
</section>

<section class="franja">
    <div class="contenedor">
        <div class="franja-cabecera">
            <span class="eyebrow">Qué ofrecemos</span>
            <h2>Tres formas de acompañarte</h2>
        </div>
        <div class="rejilla-3">
            <div class="servicio">
                <span class="num">01</span>
                <h3>Consulta general</h3>
                <p>Seguimiento médico continuo, revisiones y diagnóstico con un enfoque cercano y sin prisas.</p>
            </div>
            <div class="servicio">
                <span class="num">02</span>
                <h3>Naturopatía</h3>
                <p>Planes de fitoterapia y nutrición adaptados a cada paciente, complementarios al tratamiento médico.</p>
            </div>
            <div class="servicio">
                <span class="num">03</span>
                <h3>Bienestar emocional</h3>
                <p>Espacio de escucha y acompañamiento psicológico integrado dentro de tu proceso de salud.</p>
            </div>
        </div>
    </div>
</section>

<section class="franja franja-alt">
    <div class="contenedor">
        <div class="cita-frase">
            <p>&ldquo;La salud no es solo la ausencia de enfermedad, es el equilibrio entre cuerpo, mente y tiempo bien invertido en cuidarse.&rdquo;</p>
        </div>
    </div>
</section>

<section class="franja">
    <div class="contenedor rejilla-2">
        <div>
            <span class="eyebrow">Cómo funciona</span>
            <h2>Regístrate y pide cita en dos pasos</h2>
            <p>Crea tu cuenta de paciente, accede a tu perfil y solicita cita cuando te venga bien. Podrás modificarla o cancelarla mientras no haya pasado la fecha.</p>
            <div class="hero-acciones">
                <a href="registro.php" class="boton">Crear mi cuenta</a>
                <a href="login.php" class="boton boton-secundario">Ya tengo cuenta</a>
            </div>
        </div>
        <div class="tarjeta">
            <h3>Horario de atención</h3>
            <p style="margin:0 0 6px;">Lunes a viernes — 9:00 a 14:00</p>
            <p style="margin:0 0 6px;">Lunes a viernes (tarde) — 16:00 a 20:00</p>
            <p style="margin:0;">Sábados — 9:00 a 13:00 (urgencias)</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
