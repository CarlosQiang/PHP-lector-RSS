<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PHP RSS Filter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 20px;
            transition: background-color 0.3s ease;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-out;
        }

        fieldset {
            border: 2px solid #CB9CF2;
            border-radius: 4px;
            padding: 15px;
        }

        legend {
            color: #616283;
            font-weight: bold;
            padding: 0 10px;
        }

        label {
            display: block;
            margin-top: 10px;
            color: #374A67;
        }

        select, input[type="date"], input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #CB9CF2;
            background-color: #ffffff;
            color: #333;
            border-radius: 4px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        select:focus, input[type="date"]:focus, input[type="text"]:focus {
            border-color: #9E7B9B;
            box-shadow: 0 0 0 2px rgba(158, 123, 155, 0.2);
            outline: none;
        }

        input[type="submit"] {
            background-color: #CB9CF2;
            color: #ffffff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }

        input[type="submit"]:hover {
            background-color: #9E7B9B;
            transform: translateY(-2px);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.5s ease-out;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #CB9CF2;
            color: #ffffff;
        }

        tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        tr:hover {
            background-color: #e8e8e8;
            transition: background-color 0.3s ease;
        }

        a {
            color: #616283;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #CB9CF2;
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            table {
                font-size: 14px;
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

