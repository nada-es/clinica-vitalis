-- ============================================================
--  Clinica Vitalis - Base de datos del sitio web
--  Trabajo Final: PHP
-- ============================================================

CREATE DATABASE IF NOT EXISTS clinica_vitalis
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE clinica_vitalis;

-- ------------------------------------------------------------
-- Tabla: users_data
-- Información personal de los usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users_data (
    idUser          INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100)    NOT NULL,
    apellidos       VARCHAR(150)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    telefono        VARCHAR(20)     NOT NULL,
    fecha_nacimiento DATE           NOT NULL,
    direccion       VARCHAR(255)    DEFAULT NULL,
    sexo            ENUM('Mujer','Hombre','Otro') DEFAULT 'Otro'
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: users_login
-- Datos de acceso / rol de cada usuario registrado
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users_login (
    idLogin     INT AUTO_INCREMENT PRIMARY KEY,
    idUser      INT NOT NULL UNIQUE,
    usuario     VARCHAR(60)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('admin','user') NOT NULL DEFAULT 'user',
    CONSTRAINT fk_login_user FOREIGN KEY (idUser)
        REFERENCES users_data(idUser)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: citas
-- Citas solicitadas por los usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS citas (
    idCita      INT AUTO_INCREMENT PRIMARY KEY,
    idUser      INT NOT NULL,
    fecha_cita  DATE NOT NULL,
    motivo_cita TEXT,
    CONSTRAINT fk_citas_user FOREIGN KEY (idUser)
        REFERENCES users_data(idUser)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: noticias
-- Noticias publicadas por los administradores
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS noticias (
    idNoticia   INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(200) NOT NULL UNIQUE,
    imagen      VARCHAR(255) NOT NULL,
    texto       LONGTEXT NOT NULL,
    fecha       DATE NOT NULL,
    idUser      INT NOT NULL,
    CONSTRAINT fk_noticias_user FOREIGN KEY (idUser)
        REFERENCES users_data(idUser)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Datos de ejemplo
-- Usuario administrador -> usuario: admin  / contraseña: admin1234
-- Usuario normal        -> usuario: usuario1 / contraseña: usuario1234
-- Las contraseñas están hasheadas con password_hash() (bcrypt)
-- ============================================================

INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo) VALUES
('Elena', 'Roig Ferrer', 'admin@clinicavitalis.test', '600111222', '1985-04-12', 'Calle Mayor 1, Valencia', 'Mujer'),
('Marcos', 'Domenech Sanz', 'marcos.domenech@correo.test', '600333444', '1992-09-23', 'Avenida del Puerto 45, Valencia', 'Hombre');

-- password: admin1234
INSERT INTO users_login (idUser, usuario, password, rol) VALUES
(1, 'admin', '$2y$10$llnYQObi7t.dT8MD7H4.feeZolKvXlqhQ1QFsDgsATwgzyUNO1W.i', 'admin');

-- password: usuario1234
INSERT INTO users_login (idUser, usuario, password, rol) VALUES
(2, 'usuario1', '$2y$10$R1ClqDi1stLCX3rF1gebhepX2iTOArNBVZny8.d2S1urkFJMoQBA.', 'user');

INSERT INTO noticias (titulo, imagen, texto, fecha, idUser) VALUES
('Abrimos nuestro nuevo horario de tardes',
 'uploads/noticias/noticia1.jpg',
 'A partir de este mes ampliamos nuestro horario de atencion al publico tambien por las tardes, de lunes a viernes de 16:00 a 20:00. Podras solicitar tu cita desde el apartado de citaciones de tu perfil de usuario.',
 '2026-06-01', 1),
('Campaña gratuita de revision general',
 'uploads/noticias/noticia2.jpg',
 'Durante todo el mes realizaremos revisiones generales gratuitas para nuevos pacientes registrados en la plataforma. Aprovecha para reservar tu cita cuanto antes, las plazas son limitadas.',
 '2026-06-15', 1);
