<?php

declare(strict_types=1);

// ------- Configuración de conexión (ajústala a tu entorno) -------
$server   = "localhost";
$username = "root";
$password = "";
$database = "ejemplo2";
$charset  = "utf8mb4";

// ------- Conexión PDO -------
$dsn = "mysql:host={$server};dbname={$database};charset={$charset}";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // errores como excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // fetch por defecto como array asociativo
        PDO::ATTR_EMULATE_PREPARES   => false,                    // prepara en el servidor (más seguro)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit("❌ Error de conexión: " . $e->getMessage());
}

// ------- Utilidades -------
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$cuenta  = isset($_GET['cuenta'])  ? (int)$_GET['cuenta'] : 0;
$resumen = isset($_GET['resumen']) ? (string)$_GET['resumen'] : '';

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumnos y promedios (PDO)</title>
    <style>
        :root {
            --fg: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --bg: #ffffff;
            --chip: #f3f4f6;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--fg);
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif
        }

        .wrap {
            max-width: 980px;
            margin: 32px auto;
            padding: 0 16px
        }

        h1 {
            font-size: 22px;
            margin: 0 0 6px
        }

        h2 {
            font-size: 18px;
            margin: 24px 0 12px
        }

        .muted {
            color: var(--muted);
            font-size: 14px
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 12px 0 20px
        }

        th,
        td {
            border: 1px solid var(--line);
            padding: 8px;
            text-align: left
        }

        th {
            background: #f8fafc
        }

        .actions a {
            display: inline-block;
            margin-right: 8px;
            text-decoration: none;
            background: #111827;
            color: #fff;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 14px
        }

        .chip {
            display: inline-block;
            background: var(--chip);
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            margin-left: 6px
        }

        .back {
            font-size: 14px
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px
        }

        @media (max-width:720px) {
            .grid {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h1>Alumnos y promedios <span class="chip">PDO</span></h1>
        <p class="muted">
            Usa <code>?cuenta=NUMERO</code> para ver el detalle de un alumno. |
            Usa <code>?resumen=materias</code> para ver promedios por materia. |
            Usa <code>?resumen=alumnos</code> para ver promedios por alumno.
        </p>

        <?php
        // 1) Detalle de un alumno específico
        if ($cuenta > 0) {
            // Nombre del alumno
            $stmt = $pdo->prepare("SELECT Nombre_D_Alumno FROM Alumnos WHERE Numero_D_Cuenta = ?");
            $stmt->execute([$cuenta]);
            $nombre = $stmt->fetchColumn();

            if (!$nombre) {
                echo '<p>No se encontró el alumno con número de cuenta ' . h((string)$cuenta) . '.</p>';
                echo '<p class="actions"><a class="back" href="?">⟵ Volver</a></p>';
            } else {
                echo "<h2>Detalle del alumno: " . h($nombre) . " <span class='chip'>" . $cuenta . "</span></h2>";

                // Tabla de materias del alumno
                $stmt = $pdo->prepare("
            SELECT materia,
                   HeteroEvaluacion, CoEvaluacion, AutoEvaluacion,
                   ROUND((HeteroEvaluacion + CoEvaluacion + AutoEvaluacion)/3, 2) AS promedio
            FROM vw_alumno_materia
            WHERE Numero_D_Cuenta = ?
            ORDER BY materia
        ");
                $stmt->execute([$cuenta]);

                echo '<table>';
                echo '<tr><th>Materia</th><th>Hetero</th><th>Co</th><th>Auto</th><th>Promedio</th></tr>';
                foreach ($stmt as $row) {
                    echo '<tr>';
                    echo '<td>' . h((string)$row['materia']) . '</td>';
                    echo '<td>' . h((string)$row['HeteroEvaluacion']) . '</td>';
                    echo '<td>' . h((string)$row['CoEvaluacion']) . '</td>';
                    echo '<td>' . h((string)$row['AutoEvaluacion']) . '</td>';
                    echo '<td>' . h((string)$row['promedio']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';

                // Promedio global del alumno
                $stmt = $pdo->prepare("
            SELECT ROUND(AVG((HeteroEvaluacion + CoEvaluacion + AutoEvaluacion)/3), 2) AS promedio_global
            FROM vw_alumno_materia
            WHERE Numero_D_Cuenta = ?
        ");
                $stmt->execute([$cuenta]);
                $promedioGlobal = $stmt->fetchColumn();

                echo "<p><strong>Promedio global:</strong> " . h((string)$promedioGlobal) . "</p>";
                echo '<p class="actions"><a class="back" href="?">⟵ Volver</a></p>';
            }

            // 2) Resumen por materia
        } elseif ($resumen === 'materias') {
            $sql = "
        SELECT materia,
               ROUND(AVG((HeteroEvaluacion + CoEvaluacion + AutoEvaluacion)/3), 2) AS promedio_materia,
               COUNT(*) AS inscritos
        FROM vw_alumno_materia
        GROUP BY materia
        ORDER BY materia
    ";
            $stmt = $pdo->query($sql);

            echo '<h2>Promedios por materia</h2>';
            echo '<table><tr><th>Materia</th><th>Promedio</th><th>Inscritos</th></tr>';
            foreach ($stmt as $row) {
                echo '<tr>';
                echo '<td>' . h((string)$row['materia']) . '</td>';
                echo '<td>' . h((string)$row['promedio_materia']) . '</td>';
                echo '<td>' . h((string)$row['inscritos']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '<p class="actions"><a class="back" href="?">⟵ Volver</a></p>';

            // 3) Resumen por alumno (default)
        } else {
            $sql = "
        SELECT a.Numero_D_Cuenta,
               a.Nombre_D_Alumno,
               ROUND(AVG((am.HeteroEvaluacion + am.CoEvaluacion + am.AutoEvaluacion)/3), 2) AS promedio_global,
               COUNT(*) AS materias_cursadas
        FROM vw_alumno_materia am
        JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta
        GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno
        ORDER BY a.Nombre_D_Alumno
    ";
            $stmt = $pdo->query($sql);

            echo '<h2>Promedios por alumno</h2>';
            echo '<table>';
            echo '<tr><th>Alumno</th><th>Número de cuenta</th><th>Materias</th><th>Promedio global</th><th>Acciones</th></tr>';
            foreach ($stmt as $row) {
                $num = (int)$row['Numero_D_Cuenta'];
                echo '<tr>';
                echo '<td>' . h((string)$row['Nombre_D_Alumno']) . '</td>';
                echo '<td>' . h((string)$row['Numero_D_Cuenta']) . '</td>';
                echo '<td>' . h((string)$row['materias_cursadas']) . '</td>';
                echo '<td>' . h((string)$row['promedio_global']) . '</td>';
                echo '<td><a href="?cuenta=' . $num . '">Ver detalle</a></td>';
                echo '</tr>';
            }
            echo '</table>';

            echo '<div class="grid">';
            echo '  <div><a class="actions" href="?resumen=materias"><span>🔎 Ver promedios por materia</span></a></div>';
            echo '</div>';
        }
        ?>

    </div>
</body>

</html>