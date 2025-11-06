<?php

$server = "localhost";
$username = "root";
$password = "";
$database = "ejemplo2";

$conexion = new mysqli($server, $username, $password, $database);

$conexion = new mysqli($server, $username, $password, $database);
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$cuenta  = isset($_GET['cuenta']) ? (int)$_GET['cuenta'] : 0;
$resumen = isset($_GET['resumen']) ? $_GET['resumen'] : '';

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Alumnos y Promedios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, Arial, sans-serif;
            margin: 24px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 1000px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            border: 1px solid #ddd;
        }

        .muted {
            color: #666;
        }

        .actions a {
            margin-right: 8px;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <h2>Alumnos y promedios</h2>
    <p class="muted">Usa <code>?cuenta=NUMERO</code> para ver detalle por alumno. Usa <code>?resumen=materias</code> para ver promedios por materia.</p>

    <?php
    // 3) Resumen por materia
    if ($resumen === 'materias') {
        $sql = "
      SELECT materia,
             ROUND(AVG((HeteroEvaluacion+CoEvaluacion+AutoEvaluacion)/3),2) AS promedio_materia,
             COUNT(*) AS inscritos
      FROM vw_alumno_materia
      GROUP BY materia
      ORDER BY materia
    ";
        $rs = $conexion->query($sql);
        echo "<h3>Promedios por materia</h3>";
        echo "<table><tr><th>Materia</th><th>Promedio</th><th>Inscritos</th></tr>";
        while ($r = $rs->fetch_assoc()) {
            echo "<tr>
          <td>" . h($r['materia']) . "</td>
          <td>" . h($r['promedio_materia']) . "</td>
          <td>" . h($r['inscritos']) . "</td>
        </tr>";
        }
        echo "</table>";
        echo '<p class="actions"><a href="?">⟵ Volver</a></p>';
        exit;
    }

    // 2) Detalle de un alumno
    if ($cuenta > 0) {
        // Nombre
        $stmt = $conexion->prepare("SELECT Nombre_D_Alumno FROM Alumnos WHERE Numero_D_Cuenta = ?");
        $stmt->bind_param("i", $cuenta);
        $stmt->execute();
        $stmt->bind_result($nombre);
        $existe = $stmt->fetch();
        $stmt->close();

        if (!$existe) {
            echo "<p>No existe el alumno con cuenta <strong>" . h($cuenta) . "</strong>.</p>";
            echo '<p class="actions"><a href="?">⟵ Ver lista</a></p>';
            exit;
        }

        echo "<h3>" . h($nombre) . " <span class='badge'>" . h($cuenta) . "</span></h3>";

        // Materias y promedios
        $stmt = $conexion->prepare("
      SELECT materia, HeteroEvaluacion, CoEvaluacion, AutoEvaluacion,
             ROUND((HeteroEvaluacion+CoEvaluacion+AutoEvaluacion)/3,2) AS promedio
      FROM vw_alumno_materia
      WHERE Numero_D_Cuenta = ?
      ORDER BY materia
    ");
        $stmt->bind_param("i", $cuenta);
        $stmt->execute();
        $res = $stmt->get_result();

        echo "<table>
      <tr><th>Materia</th><th>Hetero</th><th>Co</th><th>Auto</th><th>Promedio</th></tr>";
        while ($r = $res->fetch_assoc()) {
            echo "<tr>
          <td>" . h($r['materia']) . "</td>
          <td>" . h($r['HeteroEvaluacion']) . "</td>
          <td>" . h($r['CoEvaluacion']) . "</td>
          <td>" . h($r['AutoEvaluacion']) . "</td>
          <td>" . h($r['promedio']) . "</td>
        </tr>";
        }
        echo "</table>";

        // Promedio global
        $stmt = $conexion->prepare("
      SELECT ROUND(AVG((HeteroEvaluacion+CoEvaluacion+AutoEvaluacion)/3),2) AS promedio_global
      FROM vw_alumno_materia
      WHERE Numero_D_Cuenta = ?
    ");
        $stmt->bind_param("i", $cuenta);
        $stmt->execute();
        $stmt->bind_result($promedio_global);
        $stmt->fetch();
        $stmt->close();

        echo "<p><strong>Promedio global:</strong> " . h($promedio_global) . "</p>";
        echo '<p class="actions"><a href="?">⟵ Ver lista</a></p>';
        exit;
    }

    // 1) Listado de todos con promedio global
    $sql = "
  SELECT a.Numero_D_Cuenta, a.Nombre_D_Alumno,
         ROUND(AVG((am.HeteroEvaluacion+am.CoEvaluacion+am.AutoEvaluacion)/3),2) AS promedio_global,
         COUNT(*) AS materias_cursadas
  FROM vw_alumno_materia am
  JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta
  GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno
  ORDER BY a.Nombre_D_Alumno
";
    $rs = $conexion->query($sql);

    echo "<table>
<tr><th>Alumno</th><th>Número de cuenta</th><th>Materias</th><th>Promedio global</th><th>Acciones</th></tr>";
    while ($r = $rs->fetch_assoc()) {
        echo "<tr>
      <td>" . h($r['Nombre_D_Alumno']) . "</td>
      <td>" . h($r['Numero_D_Cuenta']) . "</td>
      <td>" . h($r['materias_cursadas']) . "</td>
      <td>" . h($r['promedio_global']) . "</td>
      <td class='actions'><a href='?cuenta=" . urlencode($r['Numero_D_Cuenta']) . "'>Ver detalle</a></td>
    </tr>";
    }
    echo "</table>";

    echo '<p class="actions"><a href="?resumen=materias">Ver promedios por materia</a></p>';

    $conexion->close();
    ?>
</body>

</html>