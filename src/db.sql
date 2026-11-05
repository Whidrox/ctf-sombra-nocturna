-- src/db.sql
CREATE DATABASE IF NOT EXISTS sombra_db;
USE sombra_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    rol VARCHAR(20) NOT NULL
);

INSERT INTO usuarios (usuario, password, rol) VALUES
('admin_operaciones', 'S3cr3t_C4rt3l_P4ss_2026!', 'admin'),
('operador_logistica', 'user123', 'operator');

-- Tabla adicional: habilita la bandera F05 vía UNION-based SQL Injection.
-- Mismo número de columnas que "usuarios" (4) para que el UNION calce.
CREATE TABLE IF NOT EXISTS operaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag VARCHAR(100) NOT NULL,
    detalle VARCHAR(100) NOT NULL,
    estado VARCHAR(20) NOT NULL
);

INSERT INTO operaciones (flag, detalle, estado) VALUES
('CTF{f05_union_dump_operaciones_2026}', 'Envio_Lote_0472_Cali-Chia', 'activo');
