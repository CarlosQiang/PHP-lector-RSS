<?php

require_once "conexionRSS.php";

// Descargar el XML del feed
$sXML = download("https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/portada");
$oXML = new SimpleXMLElement($sXML);

// Conectar a PostgreSQL
require_once "conexionBBDD.php";

// Preparar la consulta de inserción con parámetros
$sqlInsert = "INSERT INTO elpais (titulo, link, descripcion, categoria, fpubli, contenido) VALUES ($1, $2, $3, $4, $5, $6) ON CONFLICT (link) DO NOTHING";

// Iniciar transacción
pg_query($link, "BEGIN");

$insertCount = 0; // Contador de inserciones
$valuesArray = []; // Array para almacenar los parámetros de las consultas

foreach ($oXML->channel->item as $item) {
    if ($insertCount >= 5) {
        break; // Detener el procesamiento después de 40 inserciones
    }

    // Filtrar categorías
    $categoriaFiltro = '';
    foreach ($item->category as $category) {
        if (in_array($category, ["Política", "Deportes", "Ciencia", "España", "Economía", "Música", "Cine", "Europa", "Justicia"])) {
            $categoriaFiltro = "[" . $category . "]" . $categoriaFiltro;
        }
    }

    // Formatear la fecha
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);

    $content = $item->children("content", true);
    $encoded = (string)$content->encoded;

    // Preparar los valores para la inserción
    $valuesArray[] = [
        (string)$item->title,
        (string)$item->link,
        (string)$item->description,
        $categoriaFiltro,
        $new_fPubli,
        $encoded
    ];

    $insertCount++; // Incrementar el contador de inserciones
}

// Insertar en bloque si hay valores
if (count($valuesArray) > 0) {
    foreach ($valuesArray as $values) {
        pg_query_params($link, $sqlInsert, $values);
    }
}

// Confirmar la transacción
pg_query($link, "COMMIT");

// Cerrar la conexión a la base de datos
#pg_close($link);

?>




