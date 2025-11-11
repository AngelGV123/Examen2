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
$grupo   = isset($_GET['grupo'])   ? (int)$_GET['grupo'] : 0;
// Umbral de aprobación (porcentaje)
$pass = 7;
// Parcial activo (1,2,3)
$parcial = isset($_GET['parcial']) ? (int)$_GET['parcial'] : 1;
if ($parcial < 1 || $parcial > 3) {
    $parcial = 1;
}
// Semestre activo (1..6)
$semestre = isset($_GET['semestre']) ? (int)$_GET['semestre'] : 1;
if ($semestre < 1 || $semestre > 6) {
    $semestre = 1;
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

// Helper: intenta varias opciones (sql+params) en orden y devuelve el primer statement que funcione
function tryPrepareOptions(PDO $pdo, array $options): PDOStatement
{
    // $options = [ ['sql' => ..., 'params' => [...]], ... ]
    foreach ($options as $opt) {
        try {
            $stmt = $pdo->prepare($opt['sql']);
            $stmt->execute($opt['params'] ?? []);
            return $stmt;
        } catch (PDOException $e) {
            // intentar siguiente opción
            continue;
        }
    }
    // si ninguna funcionó, lanzar el último error
    throw new PDOException('Ninguna opción de consulta tuvo éxito');
}

// Obtener lista de grupos
$grupos = [];
try {
    $stmtGrupos = $pdo->prepare("SELECT id_grupo, nombre_grupo FROM grupos ORDER BY nombre_grupo ASC");
    $stmtGrupos->execute();
    $grupos = $stmtGrupos->fetchAll();
} catch (PDOException $e) {
    // Si la tabla no existe, grupos vacía
    $grupos = [];
}

// Función para construir URL con grupo
function url_with_grupo(int $g): string
{
    $qs = $_GET;
    $qs['grupo'] = $g;
    return '?' . http_build_query($qs);
}

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumnos y promedios (PDO con columna Promedio)</title>
    <link rel="icon" href="iono.png" type="image/png">
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

        .grupos-bar {
            background: #f0f9ff;
            padding: 12px;
            margin: 12px 0;
            border-left: 4px solid #0284c7;
            border-radius: 4px;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .grupos-bar label {
            font-weight: bold;
            color: var(--fg);
            margin: 0;
        }

        .grupos-bar a {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .grupos-bar a.grupo-link {
            background: #e0f2fe;
            color: #0c4a6e;
            border: 1px solid #0284c7;
        }

        .grupos-bar a.grupo-link:hover {
            background: #0284c7;
            color: white;
        }

        .grupos-bar a.grupo-link.active {
            background: #0c4a6e;
            color: white;
            border-color: #0c4a6e;
        }

        .grupos-bar a.grupo-link-todos {
            background: #f3f4f6;
            color: #111827;
            border: 1px solid #d1d5db;
        }

        .grupos-bar a.grupo-link-todos:hover {
            background: #e5e7eb;
        }

        .grupos-bar a.grupo-link-todos.active {
            background: #111827;
            color: white;
        }

        @media (max-width:720px) {
            .grid {
                grid-template-columns: 1fr
            }

            .grupos-bar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h1>Alumnos y promedios <span class="chip"><?php $pLabels = [1 => 'Primer', 2 => 'Segundo', 3 => 'Tercer'];
                                                        $sLabel = 'Sem ' . $semestre;
                                                        echo h($sLabel . ' — ' . $pLabels[$parcial] . ' Parcial'); ?></span></h1>
            <a href="index2.php" style="padding: 8px 16px; background: #6b7280; color: white; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: bold;">⟵ Volver a Inicio</a>
        </div>

        <!-- Formulario de búsqueda -->
        <form method="GET" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar alumno por nombre..." value="<?php echo h($buscar); ?>">
            <button type="submit">🔍 Buscar</button>
            <?php if ($buscar): ?>
                <a href="?parcial=<?php echo $parcial; ?>&semestre=<?php echo $semestre; ?>" style="padding: 8px 16px; background: #ef4444; color: white; border-radius: 4px; text-decoration: none; display: flex; align-items: center;">✕ Limpiar</a>
            <?php endif; ?>
        </form>

        <!-- Pestañas de semestres -->
        <div style="margin-bottom:8px; display:flex; gap:8px;">
            <?php
            function url_with_semestre(int $s): string
            {
                $qs = $_GET;
                $qs['semestre'] = $s;
                if (!isset($qs['parcial'])) {
                    $qs['parcial'] = 1;
                }
                return '?' . http_build_query($qs);
            }
            $slabels = [1 => 'Sem 1', 2 => 'Sem 2', 3 => 'Sem 3', 4 => 'Sem 4', 5 => 'Sem 5', 6 => 'Sem 6'];
            foreach ($slabels as $s => $label) {
                $active = ($semestre === $s) ? 'background:#111827;color:#fff;' : 'background:#f3f4f6;color:#111827;';
                echo '<a class="actions" href="' . url_with_semestre($s) . '" style="padding:8px 12px;border-radius:6px;text-decoration:none; ' . $active . '">' . h($label) . '</a>';
            }
            ?>
        </div>

        <!-- Pestañas de parciales -->
        <div style="margin-bottom:12px; display:flex; gap:8px;">
            <?php
            function url_with_parcial(int $n): string
            {
                $qs = $_GET;
                $qs['parcial'] = $n;
                if (!isset($qs['semestre'])) {
                    $qs['semestre'] = $GLOBALS['semestre'] ?? 1;
                }
                return '?' . http_build_query($qs);
            }
            $labels = [1 => 'Primer', 2 => 'Segundo', 3 => 'Tercer'];
            foreach ($labels as $n => $label) {
                $active = ($parcial === $n) ? 'background:#111827;color:#fff;' : 'background:#f3f4f6;color:#111827;';
                echo '<a class="actions" href="' . url_with_parcial($n) . '" style="padding:8px 12px;border-radius:6px;text-decoration:none; ' . $active . '">' . h($label) . ' parcial</a>';
            }
            ?>
        </div>

        <!-- Barra de navegación de grupos -->
        <?php if (!empty($grupos)): ?>
            <div class="grupos-bar">
                <label>👥 Grupos:</label>
                <a href="?" class="grupo-link-todos <?php echo ($grupo === 0) ? 'active' : ''; ?>">Todos</a>
                <?php foreach ($grupos as $g): ?>
                    <a href="<?php echo url_with_grupo($g['id_grupo']); ?>" class="grupo-link <?php echo ($grupo === (int)$g['id_grupo']) ? 'active' : ''; ?>">
                        <?php echo h($g['nombre_grupo']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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
                $sqlDetailSemPar = "
            SELECT materia,
                   HeteroEvaluacion, CoEvaluacion, AutoEvaluacion,
                   promedio AS promedio
            FROM vw_alumno_materia am
            WHERE Numero_D_Cuenta = ? AND am.semestre = ? AND am.parcial = ?
            ORDER BY materia
        ";
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
                $stmt = tryPrepareOptions($pdo, [
                    ['sql' => $sqlDetailSemPar, 'params' => [$cuenta, $semestre, $parcial]],
                    ['sql' => $sqlDetailParcial, 'params' => [$cuenta, $parcial]],
                    ['sql' => $sqlDetailNoPar, 'params' => [$cuenta]],
                ]);

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
                $sqlAvgSemPar = "
            SELECT ROUND(AVG(Promedio), 2) AS promedio_global
            FROM vw_alumno_materia am
            WHERE Numero_D_Cuenta = ? AND am.semestre = ? AND am.parcial = ?
        ";
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
                $stmtAvg = tryPrepareOptions($pdo, [
                    ['sql' => $sqlAvgSemPar, 'params' => [$cuenta, $semestre, $parcial]],
                    ['sql' => $sqlAvgPar, 'params' => [$cuenta, $parcial]],
                    ['sql' => $sqlAvgNoPar, 'params' => [$cuenta]],
                ]);
                $promedioGlobal = $stmtAvg->fetchColumn();

                echo "<p><strong>Promedio global:</strong> " . h((string)$promedioGlobal) . "</p>";
                echo '<p class="actions"><a class="back" href="?">⟵ Volver</a></p>';
            }

            // 2) Resumen de promedios por materia
        } elseif ($resumen === 'materias') {
            // Determinar filtro de grupo
            $grupoWhere = '';
            $grupoParams = [];
            $grupoJoin = '';

            if ($grupo > 0) {
                $grupoJoin = " LEFT JOIN grupo_alumno ga ON a.Numero_D_Cuenta = ga.id_alumno";
                $grupoWhere = " AND (ga.id_grupo = ? OR ? = 0)";
                $grupoParams = [$grupo, 0];
            }

            // Obtener todas las materias únicas de la vista
            $sqlMateria = "
        SELECT DISTINCT am.materia
        FROM vw_alumno_materia am
        JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta" . $grupoJoin . "
        WHERE 1=1" . $grupoWhere . "
        ORDER BY am.materia
        ";
            try {
                $stmtMateria = $pdo->prepare($sqlMateria);
                $stmtMateria->execute($grupoParams);
                $materias = $stmtMateria->fetchAll();
            } catch (PDOException $e) {
                $materias = [];
            }

            echo '<h2>Promedios por materia</h2>';
            if ($grupo > 0) {
                foreach ($grupos as $g) {
                    if ($g['id_grupo'] === $grupo) {
                        echo '<p class="muted">Grupo: <strong>' . h($g['nombre_grupo']) . '</strong></p>';
                        break;
                    }
                }
            }
            echo '<table>';
            echo '<tr><th>Materia</th><th>Promedio</th><th>Inscritos</th><th>Acciones</th></tr>';

            $totalInscritos = 0;
            $totalReprobadas = 0;
            $totalAprobadas = 0;
            $totalPromedio = 0;
            $materiaCount = 0;

            foreach ($materias as $row) {
                $materia = $row['materia'];

                // Consulta para obtener inscritos y promedios
                $sqlBase = "
            SELECT DISTINCT a.Numero_D_Cuenta,
                   a.Nombre_D_Alumno,
                   am.promedio
            FROM vw_alumno_materia am
            JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta" . $grupoJoin . "
            WHERE am.materia = ?" . $grupoWhere . "
            ";
                $sqlWithSemPar = $sqlBase . " AND am.semestre = ? AND am.parcial = ?";
                $sqlWithPar = $sqlBase . " AND am.parcial = ?";

                $stmtMat = tryPrepareOptions($pdo, [
                    ['sql' => $sqlWithSemPar, 'params' => array_merge([$materia], $grupoParams, [$semestre, $parcial])],
                    ['sql' => $sqlWithPar,    'params' => array_merge([$materia], $grupoParams, [$parcial])],
                    ['sql' => $sqlBase,       'params' => array_merge([$materia], $grupoParams)],
                ]);

                $alumnos = $stmtMat->fetchAll();
                $inscritos = count($alumnos);
                $reprobados = 0;
                $aprobados = 0;
                $promedioMat = 0;

                foreach ($alumnos as $alumno) {
                    $promedio = floatval($alumno['promedio'] ?? 0);
                    if ($promedio < 6) {
                        $reprobados++;
                    } else {
                        $aprobados++;
                    }
                    $promedioMat += $promedio;
                }

                if ($inscritos > 0) {
                    $promedioMat = round($promedioMat / $inscritos, 2);
                }

                $totalInscritos += $inscritos;
                $totalReprobadas += $reprobados;
                $totalAprobadas += $aprobados;
                $totalPromedio += $promedioMat;
                $materiaCount++;

                echo '<tr>';
                echo '<td><strong>' . h($materia) . '</strong></td>';
                echo '<td>' . h((string)$promedioMat) . '</td>';
                echo '<td>' . $inscritos . '</td>';
                echo '<td><a href="#" onclick="toggleReprobados(\'' . h(preg_replace('/[^a-z0-9]/i', '_', $materia)) . '\'); return false;">👥 Ver reprobados</a></td>';
                echo '</tr>';

                // Fila oculta para reprobados
                echo '<tr id="reprobados_' . h(preg_replace('/[^a-z0-9]/i', '_', $materia)) . '" style="display:none;">';
                echo '<td colspan="4" style="padding-left:30px; background:#f5f5f5;">';
                echo '❌ <strong>Alumnos que reprobaron (' . $reprobados . '):</strong> ';
                $reprobadosList = [];
                foreach ($alumnos as $alumno) {
                    if (floatval($alumno['promedio'] ?? 0) < 6) {
                        $reprobadosList[] = '<a href="?cuenta=' . (int)$alumno['Numero_D_Cuenta'] . '&parcial=' . $parcial . '&semestre=' . $semestre . '&grupo=' . $grupo . '">' . h($alumno['Nombre_D_Alumno']) . '</a>';
                    }
                }
                echo implode(', ', $reprobadosList);
                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';

            // Tabla de resumen
            if ($materiaCount > 0) {
                $promMediaTotal = round($totalPromedio / $materiaCount, 2);
                $porcReprobadas = $totalInscritos > 0 ? round(($totalReprobadas / $totalInscritos) * 100, 2) : 0;
                $porcAprobadas = $totalInscritos > 0 ? round(($totalAprobadas / $totalInscritos) * 100, 2) : 0;

                echo '<h3 style="margin-top:2em;">Resumen</h3>';
                echo '<table style="max-width:600px;">';
                echo '<tr><th>Concepto</th><th>Valor</th></tr>';
                echo '<tr><td>Total de inscritos</td><td>' . $totalInscritos . '</td></tr>';
                echo '<tr><td>Reprobadas (Cantidad)</td><td>' . $totalReprobadas . '</td></tr>';
                echo '<tr><td>Reprobadas (%)</td><td>' . $porcReprobadas . '%</td></tr>';
                echo '<tr><td>Aprobadas (Cantidad)</td><td>' . $totalAprobadas . '</td></tr>';
                echo '<tr><td>Aprobadas (%)</td><td>' . $porcAprobadas . '%</td></tr>';
                echo '<tr><td>Promedio general</td><td>' . $promMediaTotal . '</td></tr>';
                echo '</table>';
            }

            echo '<div class="grid">';
            echo '  <div><a class="actions" href="?parcial=' . $parcial . '&semestre=' . $semestre . '&grupo=' . $grupo . '"><span>👥 Ver promedios por alumno</span></a></div>';
            echo '</div>';            // 3) Resumen por alumno (usa AVG(Promedio))
        } else {
            // Determinar filtro de grupo
            $grupoWhere = '';
            $grupoParams = [];
            $grupoJoin = '';

            if ($grupo > 0) {
                // Si se selecciona un grupo específico, filtramos usando la tabla grupo_alumno
                $grupoJoin = " LEFT JOIN grupo_alumno ga ON a.Numero_D_Cuenta = ga.id_alumno";
                $grupoWhere = " AND (ga.id_grupo = ? OR ? = 0)";
                $grupoParams = [$grupo, 0];
            }

            // Construir la consulta con o sin filtro de búsqueda, preferente filtro por parcial si existe
            if (!empty($buscar)) {
                $sqlBase = "
            SELECT a.Numero_D_Cuenta,
                   a.Nombre_D_Alumno,
                   ROUND(AVG(am.promedio), 2) AS promedio_global,
                   COUNT(*) AS materias_cursadas
            FROM vw_alumno_materia am
            JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta" . $grupoJoin . "
            WHERE a.Nombre_D_Alumno LIKE ?" . $grupoWhere . "
            ";
                $sqlWithSemPar = $sqlBase . " AND am.semestre = ? AND am.parcial = ? GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno ORDER BY a.Nombre_D_Alumno";
                $sqlWithPar = $sqlBase . " AND am.parcial = ? GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno ORDER BY a.Nombre_D_Alumno";
                $sqlNoPar = $sqlBase . " GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno ORDER BY a.Nombre_D_Alumno";
                $stmt = tryPrepareOptions($pdo, [
                    ['sql' => $sqlWithSemPar, 'params' => array_merge(['%' . $buscar . '%'], $grupoParams, [$semestre, $parcial])],
                    ['sql' => $sqlWithPar,    'params' => array_merge(['%' . $buscar . '%'], $grupoParams, [$parcial])],
                    ['sql' => $sqlNoPar,      'params' => array_merge(['%' . $buscar . '%'], $grupoParams)],
                ]);
            } else {
                $sqlBase = "
            SELECT a.Numero_D_Cuenta,
                   a.Nombre_D_Alumno,
                   ROUND(AVG(am.promedio), 2) AS promedio_global,
                   COUNT(*) AS materias_cursadas
            FROM vw_alumno_materia am
            JOIN Alumnos a ON a.Numero_D_Cuenta = am.Numero_D_Cuenta" . $grupoJoin . "
            WHERE 1=1" . $grupoWhere . "
            ";
                $sqlWithSemPar = $sqlBase . " AND am.semestre = ? AND am.parcial = ? GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno ORDER BY a.Nombre_D_Alumno";
                $sqlWithPar = $sqlBase . " AND am.parcial = ? GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno ORDER BY a.Nombre_D_Alumno";
                $sqlNoPar = $sqlBase . " GROUP BY a.Numero_D_Cuenta, a.Nombre_D_Alumno ORDER BY a.Nombre_D_Alumno";
                $stmt = tryPrepareOptions($pdo, [
                    ['sql' => $sqlWithSemPar, 'params' => array_merge($grupoParams, [$semestre, $parcial])],
                    ['sql' => $sqlWithPar,    'params' => array_merge($grupoParams, [$parcial])],
                    ['sql' => $sqlNoPar,      'params' => $grupoParams],
                ]);
            }

            echo '<h2>Promedios por alumno</h2>';
            if (!empty($buscar)) {
                echo '<p class="muted">Resultados para: <strong>' . h($buscar) . '</strong></p>';
            }
            if ($grupo > 0) {
                foreach ($grupos as $g) {
                    if ($g['id_grupo'] === $grupo) {
                        echo '<p class="muted">Grupo: <strong>' . h($g['nombre_grupo']) . '</strong></p>';
                        break;
                    }
                }
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
                echo '<td><a href="?cuenta=' . $num . '&parcial=' . $parcial . '&semestre=' . $semestre . '&grupo=' . $grupo . '">Ver detalle</a></td>';
                echo '</tr>';
            }
            echo '</table>';

            echo '<div class="grid">';
            echo '  <div><a class="actions" href="?resumen=materias&parcial=' . $parcial . '&semestre=' . $semestre . '&grupo=' . $grupo . '"><span>🔎 Ver promedios por materia</span></a></div>';
            echo '</div>';
        }
        ?>

    </div>
</body>

</html>