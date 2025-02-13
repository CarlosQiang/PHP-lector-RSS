<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PHP RSS Filter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 20px;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        fieldset {
            border: none;
            padding: 0;
        }

        legend {
            font-size: 1.2em;
            font-weight: bold;
            color: #444;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        select, input[type="date"], input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background: #007BFF;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 15px;
            width: 100%;
        }

        input[type="submit"]:hover {
            background: #0056b3;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #007BFF;
            color: white;
            text-transform: uppercase;
        }

        tr:hover {
            background: #f1f1f1;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            form {
                max-width: 100%;
                padding: 15px;
            }
            table {
                width: 100%;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<form action="index.php" method="GET">
    <fieldset>
        <legend>FILTRO</legend>
        <label>PERIÓDICO:</label>
        <select name="periodicos">
            <option value="elpais">El País</option>
            <option value="elmundo">El Mundo</option>
        </select>

        <label>CATEGORÍA:</label>
        <select name="categoria">
            <option value=""></option>
            <option value="Política">Política</option>
            <option value="Deportes">Deportes</option>
            <option value="Ciencia">Ciencia</option>
            <option value="España">España</option>
            <option value="Economía">Economía</option>
            <option value="Música">Música</option>
            <option value="Cine">Cine</option>
            <option value="Europa">Europa</option>
            <option value="Justicia">Justicia</option>
        </select>

        <label>FECHA:</label>
        <input type="date" name="fecha">

        <label>AMPLIAR FILTRO (descripción contenga la palabra):</label>
        <input type="text" name="buscar">

        <input type="submit" name="filtrar" value="Filtrar">
    </fieldset>
</form>

<table>
    <tr>
        <th>TÍTULO</th>
        <th>CONTENIDO</th>
        <th>DESCRIPCIÓN</th>
        <th>CATEGORÍA</th>
        <th>ENLACE</th>
        <th>FECHA DE PUBLICACIÓN</th>
    </tr>

<?php
require_once "conexionBBDD.php";

// Función para filtrar datos
function filtros($sql, $link) {
    $result = pg_query($link, $sql);

    if (!$result) {
        echo "<tr><td colspan='6'>Error en la consulta SQL: " . pg_last_error($link) . "</td></tr>";
        return;
    }

    while ($fila = pg_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fila['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['contenido']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['descripcion']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['categoria']) . "</td>";
        echo "<td><a href='" . htmlspecialchars($fila['link']) . "' target='_blank'>Ver noticia</a></td>";
        echo "<td>" . date('d-M-Y', strtotime($fila['fpubli'])) . "</td>";
        echo "</tr>";
    }
}

// Valores por defecto al cargar la página
$periodico = 'elpais'; // Valor inicial
$categoria = '';
$fecha = '';
$buscar = '';

// Si se han enviado filtros, se actualizan las variables
if (isset($_GET['filtrar'])) {
    $periodico = isset($_GET['periodicos']) ? pg_escape_string($link, $_GET['periodicos']) : 'elpais';
    if (!in_array($periodico, ['elpais', 'elmundo'])) {
        $periodico = 'elpais';
    }

    $categoria = isset($_GET["categoria"]) ? pg_escape_string($link, $_GET["categoria"]) : '';
    $fecha = isset($_GET["fecha"]) ? date("Y-m-d", strtotime($_GET["fecha"])) : '';
    $buscar = isset($_GET["buscar"]) ? pg_escape_string($link, $_GET["buscar"]) : '';
}

// Construcción de la consulta SQL
$sql = "SELECT * FROM $periodico";
$conditions = [];

if ($categoria != "") {
    $conditions[] = "categoria ILIKE '%$categoria%'";
}
if ($fecha != '' && $fecha != '1970-01-01') {
    $conditions[] = "fpubli = '$fecha'";
}
if (!empty($buscar)) {
    $conditions[] = "descripcion ILIKE '%$buscar%'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY fpubli DESC LIMIT 20";

// Ejecutar consulta y mostrar resultados
filtros($sql, $link);

pg_close($link);
?>

</table>

</body>
</html>
