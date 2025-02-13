<?php

// Verificar si la extensión de PostgreSQL está habilitada
if (!function_exists('pg_connect')) {
    die("La extensión pgsql NO está habilitada. Verifica tu configuración de PHP.");
}

// Cadena de conexión con credenciales directas
$conn_string = "host=ep-lively-tree-a26v53qd-pooler.eu-central-1.aws.neon.tech 
                dbname=neondb 
                user=neondb_owner 
                password=npg_fGcouIJO45VU 
                sslmode=require";

// Conectar a PostgreSQL
$link = pg_connect($conn_string);

if (!$link) {
    die("Error en la conexión: " . pg_last_error());
}

// Configurar codificación de caracteres a UTF8
pg_set_client_encoding($link, "UTF8");

echo "Conexión a PostgreSQL exitosa.";

?>
