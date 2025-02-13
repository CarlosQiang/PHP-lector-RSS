<?php

$Repit = false;

if (function_exists('pg_connect')) {
    echo "La extensión pgsql está habilitada.";
} else {
    echo "La extensión pgsql NO está habilitada.";
}
// Cadena de conexión
$conn_string = "postgres://neondb_owner:npg_i1FdrVEhtW6R@ep-restless-haze-a2wi8ueb-pooler.eu-central-1.aws.neon.tech/neondb?sslmode=require";

// Conectar a PostgreSQL
$link = pg_connect($conn_string);

if (! $link) {
    die("Error en la conexión: " . pg_last_error());
}

// Configurar codificación de caracteres a UTF8
pg_set_client_encoding($link, "UTF8");

echo "Conexión a PostgreSQL exitosa.";
