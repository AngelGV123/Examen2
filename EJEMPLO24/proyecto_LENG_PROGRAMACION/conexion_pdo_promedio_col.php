<?php

declare(strict_types=1);

// ------- Configuración de conexión (ajústala a tu entorno) -------
$server   = "127.0.0.1"; 
$port     = "3309";
$username = "root";
$password = "";
$database = "proyecto";
$charset  = "utf8mb4";

// ------- Conexión PDO -------

$dsn = "mysql:host=$server;port=$port;dbname=$database;charset=$charset";
try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
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
$buscar  = isset($_GET['buscar'])  ? (string)$_GET['buscar'] : '';
// Umbral de aprobación (porcentaje)
$pass = 7;
// Parcial activo (1,2,3)
$parcial = isset($_GET['parcial']) ? (int)$_GET['parcial'] : 1;
if ($parcial < 1 || $parcial > 3) {
    $parcial = 1;
}

// Helper: intenta ejecutar una consulta preparada; si falla y se suministra fallback, lo ejecuta
function tryPrepareExecute(PDO $pdo, string $sql, array $params = [], ?string $fallbackSql = null, array $fallbackParams = []): PDOStatement
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if ($fallbackSql !== null) {
            $stmt = $pdo->prepare($fallbackSql);
            $stmt->execute($fallbackParams);
            return $stmt;
        }
        throw $e;
    }
}

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumnos y promedios (PDO con columna Promedio)</title>
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

        .search-form {
            margin: 16px 0;
            display: flex;
            gap: 8px;
        }

        .search-form input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid var(--line);
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .search-form button {
            padding: 8px 16px;
            background: #111827;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .search-form button:hover {
            background: #1f2937;
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
        <a href="index2.php" class="back-button" title="Volver">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1>Alumnos y promedios <span class="chip">
            <?php $pLabels = [1 => 'Primer', 2 => 'Segundo', 3 => 'Tercer'];
                                                    echo h($pLabels[$parcial] . ' Parcial'); ?></span></h1>

<!--------------------- Subir archivo Excel/CSV ------------->
<h2>Subir archivo de alumnos</h2>

<form method="post" enctype="multipart/form-data" style="margin-bottom: 24px; display:flex; gap:8px; align-items:center;">
    <input type="file" name="archivo_excel" accept=".csv" required 
            style="flex:1; border:1px solid #ccc; padding:8px; border-radius:6px; font-family:inherit;">
    <button type="submit" name="importar_excel" 
            style="background:#111827; color:white; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; font-size:14px;">
        📤 Subir archivo
    </button>
</form>

<?php
// === Lógica de importación CSV ===
if (isset($_POST['importar_excel'])) {

    if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
        echo '<p style="color:red;">❌ Error al subir el archivo.</p>';
    } else {
        $archivoTmp = $_FILES['archivo_excel']['tmp_name'];
        $nombreArchivo = $_FILES['archivo_excel']['name'];
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        if ($extension !== 'csv') {
            echo '<p style="color:red;">⚠️ Solo se permiten archivos CSV.</p>';
        } else {
            try {
                $archivo = fopen($archivoTmp, 'r');
                $contador = 0;
                $stmt = $pdo->prepare("INSERT INTO alumnos (Numero_D_Cuenta, Nombre_D_Alumno) VALUES (?, ?)");

                // Leer cada línea (omitir encabezado)
                $primera = true;
                while (($datos = fgetcsv($archivo, 1000, ',')) !== FALSE) {
                    if ($primera) { $primera = false; continue; }
                    if (count($datos) < 2) continue;

                    $numCuenta = trim($datos[0]);
                    $nombre = trim($datos[1]);
                    if ($numCuenta && $nombre) {
                        $stmt->execute([$numCuenta, $nombre]);
                        $contador++;
                    }
                }
                fclose($archivo);
                echo "<p style='color:green;'>✅ Se importaron <strong>$contador</strong> alumnos correctamente.</p>";
                echo "<script>
                        // Espera 1.5 segundos y recarga la página automáticamente
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    </script>";
            } catch (Exception $e) {
                echo "<p style='color:red;'>❌ Error al importar: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}
?>

        <!-- Formulario de búsqueda -->
        <form method="GET" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar alumno por nombre..." value="<?php echo h($buscar); ?>">
            <button type="submit">🔍 Buscar</button>
            <?php if ($buscar): ?>
                <a href="?parcial=<?php echo $parcial; ?>" style="padding: 8px 16px; background: #ef4444; color: white; border-radius: 4px; text-decoration: none; display: flex; align-items: center;">✕ Limpiar</a>
            <?php endif; ?>
        </form>

        <!-- Pestañas de parciales -->
        <div style="margin-bottom:12px; display:flex; gap:8px;">
            <?php
            function url_with_parcial(int $n): string
            {
                $qs = $_GET;
                $qs['parcial'] = $n;
                return '?' . http_build_query($qs);
            }
            $labels = [1 => 'Primer', 2 => 'Segundo', 3 => 'Tercer'];
            foreach ($labels as $n => $label) {
                $active = ($parcial === $n) ? 'background:#111827;color:#fff;' : 'background:#f3f4f6;color:#111827;';
                echo '<a class="actions" href="' . url_with_parcial($n) . '" style="padding:8px 12px;border-radius:6px;text-decoration:none; ' . $active . '">' . h($label) . ' parcial</a>';
            }
            ?>
        </div>

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

                // Tabla de materias del alumno (usa la columna Promedio)
                $sqlDetailParcial = "
            SELECT materia,
                HeteroEvaluacion, CoEvaluacion, AutoEvaluacion,
                promedio AS promedio
            FROM vw_alumno_materia am
            WHERE Numero_D_Cuenta = ? AND am.parcial = ?
            ORDER BY materia
        ";
                $sqlDetailNoPar = "
            SELECT materia,
                HeteroEvaluacion, CoEvaluacion, AutoEvaluacion,
                promedio AS promedio
            FROM vw_alumno_materia
            WHERE Numero_D_Cuenta = ?
            ORDER BY materia
        ";
                $stmt = tryPrepareExecute($pdo, $sqlDetailParcial, [$cuenta, $parcial], $sqlDetailNoPar, [$cuenta]);

                echo '<table>';
                echo '<tr><th>Materia</th><th>Hetero</th><th>Co</th><th>Auto</th><th>promedio</th></tr>';
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

                // Promedio global del alumno (usa AVG(Promedio))
                $sqlAvgPar = "
            SELECT ROUND(AVG(Promedio), 2) AS promedio_global
            FROM vw_alumno_materia am
            WHERE Numero_D_Cuenta = ? AND am.parcial = ?
        ";
                $sqlAvgNoPar = "
            SELECT ROUND(AVG(Promedio), 2) AS promedio_global
            FROM vw_alumno_materia
            WHERE Numero_D_Cuenta = ?
        ";
                $stmtAvg = tryPrepareExecute($pdo, $sqlAvgPar, [$cuenta, $parcial], $sqlAvgNoPar, [$cuenta]);
                $promedioGlobal = $stmtAvg->fetchColumn();

                echo "<p><strong>Promedio global:</strong> " . h((string)$promedioGlobal) . "</p>";
                echo '<p class="actions"><a class="back" href="?">⟵ Volver</a></p>';
            }

            // 2) Resumen por materia (usa AVG(Promedio))
        } elseif ($resumen === 'materias') {
            // Resumen por materia con inscritos, % reprobadas, % aprobadas y promedio
            // Construir SQL (intentaremos ejecutar con filtro parcial y si falla haremos fallback sin filtro)
            $sqlBase = "
        SELECT materia,
            COUNT(*) AS inscritos,
            SUM(CASE WHEN promedio < ? THEN 1 ELSE 0 END) AS reprobadas,
            ROUND(100 * SUM(CASE WHEN promedio < ? THEN 1 ELSE 0 END) / COUNT(*), 2) AS pct_reprobadas,
            ROUND(100 * SUM(CASE WHEN promedio >= ? THEN 1 ELSE 0 END) / COUNT(*), 2) AS pct_aprobadas,
            ROUND(AVG(promedio), 2) AS promedio_materia
        FROM vw_alumno_materia
        ";
            $sqlWithPar = $sqlBase . " WHERE parcial = ? GROUP BY materia ORDER BY materia";
            $sqlNoPar = $sqlBase . " GROUP BY materia ORDER BY materia";
            $paramsWithPar = [$pass, $pass, $pass, $parcial];
            $paramsNoPar = [$pass, $pass, $pass];
            $stmt = tryPrepareExecute($pdo, $sqlWithPar, $paramsWithPar, $sqlNoPar, $paramsNoPar);

            echo '<h2>Promedios por materia</h2>';
            // Tabla principal (detalle por materia)
            echo '<table><tr><th>Materia</th><th>Promedio</th><th>Inscritos</th></tr>';
            foreach ($stmt as $row) {
                $materia = $row['materia'];
                echo '<tr>';
                echo '<td>' . h((string)$row['materia']) . '</td>';
                echo '<td>' . h((string)$row['promedio_materia']) . '</td>';
                echo '<td>' . h((string)$row['inscritos']) . '</td>';
                echo '</tr>';

                // Mostrar alumnos que reprobaron (promedio < $pass)
                // Mostrar alumnos que reprobaron (promedio < $pass)
                $sqlReprobPar = "
                            SELECT a.Numero_D_Cuenta,
                                a.Nombre_D_Alumno,
                                am.promedio
                            FROM vw_alumno_materia am
                            JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta
                            WHERE CAST(am.materia AS CHAR) = ? AND am.parcial = ? AND am.promedio < ?
                            ORDER BY am.promedio ASC
                        ";
                $sqlReprobNoPar = "
                            SELECT a.Numero_D_Cuenta,
                                a.Nombre_D_Alumno,
                                am.promedio
                            FROM vw_alumno_materia am
                            JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta
                            WHERE CAST(am.materia AS CHAR) = ? AND am.promedio < ?
                            ORDER BY am.promedio ASC
                        ";
                $stmtReprobados = tryPrepareExecute($pdo, $sqlReprobPar, [$materia, $parcial, $pass], $sqlReprobNoPar, [$materia, $pass]);
                $reprobados = $stmtReprobados->fetchAll();

                if (!empty($reprobados)) {
                    $cantidadReprobados = count($reprobados);
                    echo '<tr style="background-color: #fee2e2;">';
                    echo '<td colspan="3"><strong>❌ Alumnos que reprobaron: ' . $cantidadReprobados . '</strong></td>';
                    echo '</tr>';
                    foreach ($reprobados as $alumno) {
                        echo '<tr style="background-color: #fecaca;">';
                        echo '<td style="padding-left: 30px;">→ ' . h((string)$alumno['Nombre_D_Alumno']) . '</td>';
                        echo '<td>Calificación: ' . h((string)$alumno['promedio']) . '</td>';
                        echo '<td></td>';
                        echo '</tr>';
                    }
                }
            }
            echo '</table>';

            // Tabla resumen por materia (inscritos, reprobadas, aprobadas en % y promedio)
            $sqlSummaryBase = "
        SELECT materia,
            COUNT(*) AS inscritos,
            SUM(CASE WHEN promedio < ? THEN 1 ELSE 0 END) AS reprobadas,
            SUM(CASE WHEN promedio >= ? THEN 1 ELSE 0 END) AS aprobadas,
            ROUND(AVG(promedio),2) AS promedio_materia
        FROM vw_alumno_materia
        ";
            $sqlSummaryWithPar = $sqlSummaryBase . " WHERE parcial = ? GROUP BY materia ORDER BY materia";
            $sqlSummaryNoPar = $sqlSummaryBase . " GROUP BY materia ORDER BY materia";
            $stmtSummary = tryPrepareExecute($pdo, $sqlSummaryWithPar, [$pass, $pass, $parcial], $sqlSummaryNoPar, [$pass, $pass]);

            echo '<h3>Resumen por materia</h3>';
            echo '<table>';
            echo '<tr><th>Materia</th><th>Inscritos</th><th>Reprobadas</th><th>Aprobadas</th><th>Promedio</th></tr>';
            foreach ($stmtSummary as $s) {
                $inscritos = (int)$s['inscritos'];
                $reprobadas = (int)$s['reprobadas'];
                $aprobadas = (int)$s['aprobadas'];
                $porReprob = $inscritos > 0 ? round(($reprobadas * 100) / $inscritos, 2) : 0;
                $porApro = $inscritos > 0 ? round(($aprobadas * 100) / $inscritos, 2) : 0;
                echo '<tr>';
                echo '<td>' . h((string)$s['materia']) . '</td>';
                echo '<td>' . h((string)$inscritos) . '</td>';
                echo '<td>' . h((string)$reprobadas) . ' (' . h((string)$porReprob) . '%)</td>';
                echo '<td>' . h((string)$aprobadas) . ' (' . h((string)$porApro) . '%)</td>';
                echo '<td>' . h((string)$s['promedio_materia']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '<p class="actions"><a class="back" href="?">⟵ Volver</a></p>';

            // 3) Resumen por alumno (usa AVG(Promedio))
        } else {
    // Construir la consulta con o sin filtro de búsqueda, preferente filtro por parcial si existe
    if (!empty($buscar)) {
        $sqlBase = "
            SELECT a.Numero_D_Cuenta,
                a.Nombre_D_Alumno,
                ROUND(AVG(am.promedio), 2) AS promedio_global,
                COUNT(am.materia) AS materias_cursadas
            FROM Alumnos a
            LEFT JOIN vw_alumno_materia am
                ON a.Numero_D_Cuenta = am.Numero_D_Cuenta
            WHERE a.Nombre_D_Alumno LIKE ?
        ";
        $sqlWithPar = $sqlBase . " AND (am.parcial = ? OR am.parcial IS NULL)
                                GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno
                                ORDER BY a.Nombre_D_Alumno";
        $sqlNoPar = $sqlBase . " GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno
                                ORDER BY a.Nombre_D_Alumno";
        $stmt = tryPrepareExecute($pdo, $sqlWithPar, ['%' . $buscar . '%', $parcial], $sqlNoPar, ['%' . $buscar . '%']);
    } else {
        $sqlBase = "
            SELECT a.Numero_D_Cuenta,
                a.Nombre_D_Alumno,
                ROUND(AVG(am.promedio), 2) AS promedio_global,
                COUNT(am.materia) AS materias_cursadas
            FROM Alumnos a
            LEFT JOIN vw_alumno_materia am
                ON a.Numero_D_Cuenta = am.Numero_D_Cuenta
        ";
        $sqlWithPar = $sqlBase . " WHERE (am.parcial = ? OR am.parcial IS NULL)
                                GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno
                                ORDER BY a.Nombre_D_Alumno";
        $sqlNoPar = $sqlBase . " GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno
                                ORDER BY a.Nombre_D_Alumno";
        $stmt = tryPrepareExecute($pdo, $sqlWithPar, [$parcial], $sqlNoPar, []);
    }

    echo '<h2>Promedios por alumno</h2>';

            if (!empty($buscar)) {
                echo '<p class="muted">Resultados para: <strong>' . h($buscar) . '</strong></p>';
            }
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
            echo '  <div><a class="actions" href="?resumen=materias&parcial=' . $parcial . '"><span>🔎 Ver promedios por materia</span></a></div>';
            echo '</div>';
        }
        ?>

    </div>
</body>

</html>
