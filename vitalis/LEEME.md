# Clínica Vitalis — Trabajo Final: PHP

**Autora:** Nada Es Sabti

Sitio web de una clínica de medicina integrativa ficticia, desarrollado con
HTML5, CSS3, JavaScript, PHP y MySQL como ejercicio final del módulo.

## 1. Estructura del proyecto

```
clinica-vitalis/
├── database.sql                     -> Script SQL (tablas + datos de ejemplo)
├── index.php                        -> Portada del sitio
├── noticias.php                     -> Listado público de noticias
├── registro.php                     -> Alta de nuevos usuarios (rol "user")
├── login.php                        -> Inicio de sesión
├── logout.php                       -> Cierre de sesión
├── perfil.php                       -> Datos personales + cambio de contraseña (user y admin)
├── citaciones.php                   -> Gestión de citas propias (solo rol "user")
├── usuarios-administracion.php      -> CRUD de usuarios (solo rol "admin")
├── citas-administracion.php         -> CRUD de citas de cualquier usuario (solo rol "admin")
├── noticias-administracion.php      -> CRUD de noticias con imagen (solo rol "admin")
├── includes/
│   ├── db.php                       -> Conexión mysqli
│   ├── functions.php                -> Sesión, validaciones y helpers de rol
│   ├── navbar.php                   -> Barra de navegación (cambia según visitante/user/admin)
│   └── footer.php                   -> Pie de página común
├── css/style.css                    -> Estilos de todo el sitio
└── uploads/noticias/                -> Imágenes subidas desde el panel de noticias
```

## 2. Instalación local (XAMPP / WAMP / MAMP)

1. Copia la carpeta `clinica-vitalis` dentro de `htdocs` (o `www`).
2. Crea la base de datos importando `database.sql` desde phpMyAdmin o por consola:
   ```
   mysql -u root -p < database.sql
   ```
3. Revisa `includes/db.php` y ajusta usuario/contraseña de MySQL si no usas `root` sin contraseña.
4. Asegúrate de que la carpeta `uploads/noticias/` tiene permisos de escritura (para poder subir imágenes de noticias).
5. Abre `http://localhost/clinica-vitalis/index.php`.

## 3. Instalación en un hosting gratuito compatible con PHP + MySQL

1. Sube todos los archivos por FTP (o el gestor de archivos del panel).
2. Crea una base de datos MySQL desde el panel del hosting e importa `database.sql`.
3. Edita `includes/db.php` con los datos de conexión que te facilite el hosting
   (host, usuario, contraseña y nombre de la base de datos).
4. Comprueba que la carpeta `uploads/noticias/` tenga permisos de escritura (normalmente 755).

## 4. Usuarios de prueba

| Rol   | Usuario     | Contraseña     |
|-------|-------------|----------------|
| admin | `admin`     | `admin1234`    |
| user  | `usuario1`  | `usuario1234`  |

Las contraseñas están almacenadas con `password_hash()` (bcrypt), nunca en texto plano.

## 5. Cómo se cubre cada apartado del enunciado

- **Base de datos (1 punto):** `database.sql` crea `users_data`, `users_login`,
  `citas` y `noticias` con las claves, tipos y restricciones pedidas (PK autoincremental,
  FKs, campos únicos y no nulos, ENUM para rol y sexo).
- **Sitio web (4 puntos):**
  - `index.php`, `noticias.php`, `registro.php` y `login.php` con navegación común (`includes/navbar.php`),
    que resalta la página activa y cambia de contenido según el rol.
  - Validación de formularios en PHP (campos obligatorios, formato de email, fechas, contraseñas coincidentes).
  - Contraseña cifrada con `password_hash()` / verificada con `password_verify()`.
  - Mensajes de error y de éxito, con redirección tras registro (a `login.php`) y tras login (a `index.php`).
- **Usuarios (2,5 puntos):** `perfil.php` (ver/editar datos, usuario no editable, cambio de
  contraseña) y `citaciones.php` (crear, modificar y borrar citas propias siempre que la
  fecha no sea anterior a hoy).
- **Administradores (2,5 puntos):** `usuarios-administracion.php` (crear/editar/borrar
  usuarios y asignar rol), `citas-administracion.php` (elige un paciente y gestiona sus citas)
  y `noticias-administracion.php` (crear/editar/borrar noticias con imagen).

## 6. Notas técnicas

- Todas las consultas usan sentencias preparadas (`mysqli_prepare` + `bind_param`) para
  evitar inyección SQL.
- El control de acceso se centraliza en `includes/functions.php`
  (`requiereLogin()`, `requiereRol()`), y cada página protegida lo llama al principio.
- El sitio es responsive (se adapta a móvil) y respeta `prefers-reduced-motion`.
