<!DOCTYPE html>
<html>
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

        /* Estilos para la tabla */
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

        /* Responsividad */
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

</body>
</html>


<?php
    require_once "RSSElPais.php";
    require_once "RSSElMundo.php";
    require_once "conexionBBDD.php";

    function filtros($sql, $link)
    {
        $result = pg_query($link, $sql);

        if (! $result) {
            echo "Error en la consulta SQL: " . pg_last_error($link);
            return;
        }

        while ($arrayFiltro = pg_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 20%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['titulo'] . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 40%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['contenido'] . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 20%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['descripcion'] . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 10%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['categoria'] . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 10%; overflow: hidden; text-overflow: ellipsis;'><a href='" . $arrayFiltro['link'] . "' target='_blank'>" . $arrayFiltro['link'] . "</a></td>";
            $fecha           = date_create($arrayFiltro['fpubli']);
            $fechaConversion = date_format($fecha, 'd-M-Y');
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'>" . $fechaConversion . "</td>";
            echo "</tr>";
        }

        // Cerrar la tabla HTML
        echo "</table>";
    }

    if (! $link) {
        die("Conexión fallida: " . pg_last_error());
    } else {
        echo "<table style='border: 5px #E4CCE8 solid;'>";
        echo "<tr><th><p style='color: #66E9D9;'>TITULO</p></th><th><p style='color: #66E9D9;'>CONTENIDO</p></th><th><p style='color: #66E9D9;'>DESCRIPCIÓN</p></th><th><p style='color: #66E9D9;'>CATEGORÍA</p></th><th><p style='color: #66E9D9;'>ENLACE</p></th><th><p style='color: #66E9D9;'>FECHA DE PUBLICACIÓN</p></th></tr><br>";

        $periodico = 'elpais'; // Valor por defecto
        if (isset($_GET['filtrar'])) {
            $periodico = isset($_GET['periodicos']) ? $_GET['periodicos'] : 'elpais';
            // Validar entrada para prevenir SQL injection
            if (! in_array($periodico, ['elpais', 'elmundo'])) {
                $periodico = 'elpais';
            }

            $cat     = isset($_GET["categoria"]) ? $_GET["categoria"] : '';
            $fech    = isset($_GET["fecha"]) ? date("Y-m-d", strtotime($_GET["fecha"])) : '';
            $palabra = isset($_GET["buscar"]) ? $_GET["buscar"] : '';

            $sql        = "SELECT * FROM $periodico";
            $conditions = [];

            if ($cat != "") {
                $conditions[] = "categoria ILIKE '%$cat%'";
            }
            if ($fech != '' && $fech != '1970-01-01') {
                $conditions[] = "fpubli = '$fech'";
            }
            if (! empty($palabra)) {
                $conditions[] = "descripcion ILIKE '%$palabra%'";
            }

            if (! empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $sql .= " ORDER BY fpubli DESC";

            filtros($sql, $link);
        } else {
            // Consulta por defecto para ambos periódicos
            $sql = "(SELECT * FROM elpais UNION ALL SELECT * FROM elmundo) AS noticias
                    ORDER BY fpubli DESC LIMIT 20";
            filtros($sql, $link);
        }

        echo "</table>";
    }

    pg_close($link);
?>

