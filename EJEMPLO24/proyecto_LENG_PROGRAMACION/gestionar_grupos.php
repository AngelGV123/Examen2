<?php

declare(strict_types=1);

$server   = "127.0.0.1";
$port     = "3309";
$username = "root";
$password = "";
$database = "proyecto";

/**
 * Conexión
 */
$conn = new mysqli($server, $username, $password, $database, (int)$port);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

/**
 * Helper seguro para imprimir (acepta null y otros tipos)
 */
function h($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Detectar columnas de la tabla `grupos`
 * - pkCol: columna PK (por defecto 'id_grupo')
 * - nameCol: columna nombre (por defecto 'nombre_grupo')
 * - progCol: columna programa educativo (opcional)
 */
$pkCol = 'id_grupo';
$nameCol = 'nombre_grupo';
$progCol = ''; // puede quedar vacío si no existe

$cols_ok = false;
$colsRes = $conn->query("SHOW COLUMNS FROM grupos");
if ($colsRes && $colsRes instanceof mysqli_result) {
    $cols = [];
    while ($c = $colsRes->fetch_assoc()) {
        $cols[] = $c;
    }
    if (!empty($cols)) {
        // Buscar pk
        foreach ($cols as $c) {
            if (($c['Key'] ?? '') === 'PRI') {
                $pkCol = $c['Field'];
                break;
            }
        }
        // Buscar nombre (intentar varios nombres comunes)
        $possibleName = ['nombre_grupo','nombre','name','grupo','group_name'];
        foreach ($cols as $c) {
            $f = $c['Field'];
            if (in_array($f, $possibleName, true)) {
                $nameCol = $f;
                break;
            }
        }
        // Buscar programa educativo (opcional)
        $possibleProg = ['programa','programa_educativo','programa_edu','programaEducativo','programa_educacion'];
        foreach ($cols as $c) {
            $f = $c['Field'];
            if (in_array($f, $possibleProg, true)) {
                $progCol = $f;
                break;
            }
        }
        $cols_ok = true;
    }
}

/**
 * Función para comprobar existencia de tabla
 */
function tableExists(mysqli $conn, string $table): bool
{
    $t = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$t}'");
    return ($res && $res->num_rows > 0);
}

/**
 * Procesar formularios (crear grupo, asignar, desasignar)
 *
 * NOTA: aquí hacemos adaptaciones dinámicas según las columnas detectadas.
 */
$mensaje = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Crear grupo
    if (isset($_POST['action']) && $_POST['action'] === 'crear_grupo') {
        $nombre_grupo = trim((string)($_POST['nombre_grupo'] ?? ''));
        $programa = trim((string)($_POST['programa'] ?? ''));

        if ($nombre_grupo === '') {
            $mensaje = ['tipo' => 'error', 'texto' => 'El nombre del grupo no puede estar vacío.'];
        } else {
            // Construir SQL dinámico según columnas detectadas
            $fields = [];
            $placeholders = [];
            $params = [];
            $types = '';

            // columna nombre
            $fields[] = $nameCol;
            $placeholders[] = '?';
            $params[] = $nombre_grupo;
            $types .= 's';

            // columna programa (opcional)
            if ($progCol !== '') {
                $fields[] = $progCol;
                $placeholders[] = '?';
                $params[] = $programa;
                $types .= 's';
            }

            $sql = "INSERT INTO grupos (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $conn->error . " — SQL: {$sql}"];
            } else {
                // bind dinámico
                $bind_names = [];
                $bind_names[] = $types;
                for ($i = 0; $i < count($params); $i++) {
                    $bind_names[] = &$params[$i];
                }
                call_user_func_array([$stmt, 'bind_param'], $bind_names);

                if ($stmt->execute()) {
                    $mensaje = ['tipo' => 'exito', 'texto' => 'Grupo creado exitosamente.'];
                } else {
                    if (stripos($stmt->error, 'Duplicate') !== false) {
                        $mensaje = ['tipo' => 'error', 'texto' => 'El grupo ya existe.'];
                    } else {
                        $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $stmt->error];
                    }
                }
                $stmt->close();
            }
        }

    // Asignar alumnos (ahora puede enviar varios alumnos por checkbox -> ver UI más abajo)
    } elseif (isset($_POST['action']) && $_POST['action'] === 'asignar') {
        $id_grupo = (int)($_POST['id_grupo'] ?? 0);
        $ids_alumnos = $_POST['id_alumno'] ?? []; // puede ser array de checkboxes
        if (!is_array($ids_alumnos)) {
            // si vienen como string de uno solo, convertir a array
            if ($ids_alumnos !== '') $ids_alumnos = [(int)$ids_alumnos]; else $ids_alumnos = [];
        }
        // limpiar array
        $ids_clean = [];
        foreach ($ids_alumnos as $a) {
            $aInt = (int)$a;
            if ($aInt > 0) $ids_clean[] = $aInt;
        }

        if ($id_grupo <= 0 || empty($ids_clean)) {
            $mensaje = ['tipo' => 'error', 'texto' => 'Debes seleccionar grupo y al menos un alumno.'];
        } else {
            if (!tableExists($conn, 'grupo_alumno')) {
                $mensaje = ['tipo' => 'error', 'texto' => 'La tabla <strong>grupo_alumno</strong> no existe en la base de datos. Crea la tabla con campos (id_relacion PK AUTO_INCREMENT, id_grupo, id_alumno) para poder asignar alumnos.'];
            } else {
                $stmt = $conn->prepare("INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES (?, ?)");
                if (!$stmt) {
                    $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $conn->error];
                } else {
                    $added = 0;
                    foreach ($ids_clean as $id_alumno) {
                        // intentar ejecutar, ignorar duplicados
                        $stmt->bind_param("ii", $id_grupo, $id_alumno);
                        try {
                            $ok = $stmt->execute();
                            if ($ok) $added++;
                        } catch (mysqli_sql_exception $e) {
                            // si es duplicate, ignorar
                            if (stripos($e->getMessage(), 'Duplicate') !== false) {
                                // ignore
                            } else {
                                // guardar último error
                                $mensaje = ['tipo' => 'error', 'texto' => 'Error al asignar: ' . $e->getMessage()];
                            }
                        }
                    }
                    $stmt->close();
                    if (!isset($mensaje)) $mensaje = ['tipo' => 'exito', 'texto' => "Se asignaron {$added} alumno(s) al grupo."];
                }
            }
        }

    // Desasignar (eliminar relación)
    } elseif (isset($_POST['action']) && $_POST['action'] === 'desasignar') {
        $id_relacion = (int)($_POST['id_relacion'] ?? 0);
        if ($id_relacion <= 0) {
            $mensaje = ['tipo' => 'error', 'texto' => 'ID de relación inválida.'];
        } else {
            if (!tableExists($conn, 'grupo_alumno')) {
                $mensaje = ['tipo' => 'error', 'texto' => 'La tabla <strong>grupo_alumno</strong> no existe.'];
            } else {
                $stmt = $conn->prepare("DELETE FROM grupo_alumno WHERE id_relacion = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $id_relacion);
                    if ($stmt->execute()) {
                        $mensaje = ['tipo' => 'exito', 'texto' => 'Asignación eliminada.'];
                    } else {
                        $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $stmt->error];
                    }
                    $stmt->close();
                } else {
                    $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $conn->error];
                }
            }
        }
    }
}

/**
 * Obtener datos
 * - grupos: se normaliza a $grupos_list (array) para poder iterar sin errores
 * - alumnos: lista de alumnos disponibles (los que no están en el grupo al que se está asignando)
 * - asignaciones: si existe tabla grupo_alumno, sacamos asignaciones; si no, dejamos vacío
 */
$grupos_list = [];
$grupos_result = $conn->query("SELECT * FROM grupos ORDER BY {$nameCol}");
if ($grupos_result && $grupos_result instanceof mysqli_result) {
    $grupos_list = $grupos_result->fetch_all(MYSQLI_ASSOC);
} else {
    $grupos_list = [];
}

// Alumnos: siempre sacamos lista completa (para el selector). Más abajo al asignar se quitarán los ya asignados si corresponde en la UI.
$alumnos_result = $conn->query("SELECT Numero_D_Cuenta, Nombre_D_Alumno FROM Alumnos ORDER BY Nombre_D_Alumno");

// Asignaciones (si existe tabla grupo_alumno)
$asignaciones_result = null;
if (tableExists($conn, 'grupo_alumno')) {
    // Si la tabla existe, intentamos obtener las asignaciones
    $sqlAsig = "
        SELECT ga.id_relacion, g.{$nameCol} AS nombre_grupo, a.Numero_D_Cuenta, a.Nombre_D_Alumno, ga.id_grupo
        FROM grupo_alumno ga
        LEFT JOIN grupos g ON ga.id_grupo = g.{$pkCol}
        LEFT JOIN Alumnos a ON ga.id_alumno = a.Numero_D_Cuenta
        ORDER BY g.{$nameCol}, a.Nombre_D_Alumno
    ";
    try {
        $asignaciones_result = $conn->query($sqlAsig);
    } catch (mysqli_sql_exception $e) {
        $asignaciones_result = null;
    }
} else {
    $asignaciones_result = null;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; 
            background: #f0f2f5; 
            min-height:100vh; padding:20px; }
        .container { max-width:1200px; margin:0 auto; background:white; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.3); overflow:hidden; }
        .header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:30px; display:flex; justify-content:space-between; align-items:center; }
        .header h1 { font-size:28px; }
        .back-button { display:inline-block; padding:8px 16px; background:rgba(255,255,255,0.2); color:white; text-decoration:none; border-radius:6px; transition:background 0.3s; }
        .content { padding:30px; }
        .mensaje { padding:15px; margin-bottom:20px; border-radius:6px; animation: slideIn .3s ease-out; }
        .mensaje.exito { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .mensaje.error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        @keyframes slideIn { from { opacity:0; transform:translateY(-10px);} to { opacity:1; transform:translateY(0);} }
        .secciones { display:grid; grid-template-columns:repeat(auto-fit,minmax(350px,1fr)); gap:30px; margin-bottom:30px; }
        .seccion { border:2px solid #e0e0e0; border-radius:8px; padding:20px; }
        .seccion h2 { color:#667eea; margin-bottom:20px; font-size:20px; border-bottom:2px solid #667eea; padding-bottom:10px; }
        .form-group { margin-bottom:15px; }
        label { display:block; margin-bottom:8px; color:#333; font-weight:500; }
        input, select { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px; font-family:inherit; }
        button { width:100%; padding:10px; background:#667eea; color:white; border:none; border-radius:6px; font-size:14px; font-weight:600; cursor:pointer; transition:background .3s; }
        button:hover { background:#5568d3; }
        .tabla-container { border:2px solid #e0e0e0; border-radius:8px; padding:20px; overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        table th { background:#f5f5f5; color:#333; padding:12px; text-align:left; font-weight:600; border-bottom:2px solid #e0e0e0; }
        table td { padding:12px; border-bottom:1px solid #e0e0e0; }
        table tr:hover { background:#f9f9f9; }
        .btn-eliminar { background:#dc3545; padding:6px 12px; font-size:12px; width:auto; border-radius:4px; color:white; border:none; cursor:pointer; }
        .btn-eliminar:hover { background:#c82333; }
        .sin-datos { text-align:center; padding:20px; color:#999; }
        .groups-buttons { display:flex; flex-wrap:wrap; gap:10px; margin-top:15px; }
        .group-btn { padding:10px 20px; background:#667eea; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; min-width:220px; text-align:left; }
        .small { color:#666; margin-top:8px; }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>👥 Gestión de Grupos</h1>
            <a href="index2.php" class="back-button">← Volver</a>
        </div>

        <div class="content">

            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo h($mensaje['tipo']); ?>">
                    <?php echo $mensaje['texto']; /* ya escapado internamente */ ?>
                </div>
            <?php endif; ?>

            <div class="secciones">
                <!-- Crear Grupo -->
                <div class="seccion">
                    <h2>➕ Crear Grupo</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="crear_grupo">
                        <div class="form-group">
                            <label for="nombre_grupo">Nombre del grupo:</label>
                            <input type="text" id="nombre_grupo" name="nombre_grupo" placeholder="Ej: Grupo A" required>
                        </div>

                        <?php if ($progCol !== ''): ?>
                        <div class="form-group">
                            <label for="programa">Programa educativo:</label>
                            <select id="programa" name="programa" required>
                                <option value="">-- Selecciona --</option>
                                <option value="Bachillerato">Bachillerato</option>
                                <option value="Ingenieria software">Ingenieria software</option>
                                <option value="Medico cirujano">Medico cirujano</option>
                                <option value="Enfermeria">Enfermeria</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <button type="submit">Crear grupo</button>
                    </form>
                </div>

                <!-- Asignar Alumno (multi-checkbox) -->
                <div class="seccion">
                    <h2>➕ Asignar Alumno(s)</h2>
                    <?php if (!tableExists(new mysqli($server,$username,$password,$database,(int)$port), 'grupo_alumno')): ?>
                        <p class="small">Nota: la tabla <strong>grupo_alumno</strong> no existe. No se pueden guardar asignaciones hasta crearla.</p>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="asignar">
                        <div class="form-group">
                            <label for="id_grupo">Grupo:</label>
                            <select id="id_grupo" name="id_grupo" required>
                                <option value="">-- Selecciona un grupo --</option>
                                <?php foreach ($grupos_list as $grupo): ?>
                                    <option value="<?php echo h((string)($grupo[$pkCol] ?? '')); ?>">
                                        <?php echo h($grupo[$nameCol] ?? ''); ?>
                                        <?php if ($progCol !== '' && isset($grupo[$progCol]) && $grupo[$progCol] !== ''): ?>
                                            (<?php echo h($grupo[$progCol]); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alumnos (marca varios):</label>
                            <div style="max-height:220px; overflow:auto; border:1px solid #eee; padding:8px; border-radius:6px;">
                                <?php if ($alumnos_result && $alumnos_result instanceof mysqli_result): ?>
                                    <?php while ($al = $alumnos_result->fetch_assoc()): ?>
                                        <div style="margin-bottom:6px;">
                                            <label style="display:flex; gap:8px; align-items:center;">
                                                <input type="checkbox" name="id_alumno[]" value="<?php echo h((string)$al['Numero_D_Cuenta']); ?>">
                                                <span><?php echo h($al['Nombre_D_Alumno']); ?> (<?php echo h((string)$al['Numero_D_Cuenta']); ?>)</span>
                                            </label>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="small">No hay alumnos en la base de datos.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit">Asignar alumno(s)</button>
                    </form>
                </div>
            </div>

            <!-- Lista de grupos como botones (al presionar muestra los alumnos del grupo) -->
            <div class="seccion">
                <h2>🔎 Grupos registrados</h2>
                <?php if (!empty($grupos_list)): ?>
                    <div class="groups-buttons">
                        <?php foreach ($grupos_list as $g): 
                            $idVal = $g[$pkCol] ?? '';
                            $nombreVal = $g[$nameCol] ?? ($g[$pkCol] ?? 'Grupo');
                            $progVal = $progCol ? ($g[$progCol] ?? '') : '';
                        ?>
                            <form method="get" style="margin:0;">
                                <input type="hidden" name="ver_grupo" value="<?php echo h($idVal); ?>">
                                <button type="submit" class="group-btn">
                                    <?php echo h($nombreVal); ?>
                                    <?php if ($progVal !== ''): ?> — <?php echo h($progVal); ?><?php endif; ?>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="small">No hay grupos creados aún.</p>
                <?php endif; ?>
            </div>

            <!-- Si el usuario ha solicitado ver un grupo en particular -->
            <?php
            if (isset($_GET['ver_grupo']) && (int)$_GET['ver_grupo'] > 0) {
                $id_grupo_sel = (int)$_GET['ver_grupo'];
                // Abrir conexión temporal para obtener alumnos del grupo
                $c2 = new mysqli($server, $username, $password, $database, (int)$port);
                if (!$c2->connect_error && tableExists($c2, 'grupo_alumno')) {
                    $sql = "SELECT ga.id_relacion, a.Numero_D_Cuenta, a.Nombre_D_Alumno
                            FROM grupo_alumno ga
                            JOIN Alumnos a ON ga.id_alumno = a.Numero_D_Cuenta
                            WHERE ga.id_grupo = ?
                            ORDER BY a.Nombre_D_Alumno";
                    $stmt = $c2->prepare($sql);
                    $rows = null;
                    if ($stmt) {
                        $stmt->bind_param('i', $id_grupo_sel);
                        $stmt->execute();
                        $rows = $stmt->get_result();
                        $stmt->close();
                    } else {
                        $rows = $c2->query("SELECT a.Numero_D_Cuenta, a.Nombre_D_Alumno FROM grupo_alumno ga JOIN Alumnos a ON ga.id_alumno = a.Numero_D_Cuenta WHERE ga.id_grupo = {$id_grupo_sel} ORDER BY a.Nombre_D_Alumno");
                    }
                } else {
                    $rows = null;
                }
            ?>
                <div class="seccion" style="margin-top:18px;">
                    <h2>👥 Alumnos del grupo seleccionado</h2>
                    <?php if ($rows && $rows instanceof mysqli_result && $rows->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr><th>Nombre</th><th>Núm. de cuenta</th></tr>
                            </thead>
                            <tbody>
                                <?php while ($al = $rows->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo h($al['Nombre_D_Alumno'] ?? ''); ?></td>
                                        <td><?php echo h((string)($al['Numero_D_Cuenta'] ?? '')); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="small">No hay alumnos asignados a este grupo o la tabla <code>grupo_alumno</code> no existe.</p>
                    <?php endif; ?>
                </div>
            <?php } ?>

            
            <!-- Asignaciones actuales -->
            <div class="tabla-container" style="margin-top:18px;">
                <h2>📋 Asignaciones Actuales</h2>
                <?php if ($asignaciones_result && $asignaciones_result instanceof mysqli_result && $asignaciones_result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Alumno</th>
                                <th>Número de cuenta</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($asig = $asignaciones_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo h($asig['nombre_grupo'] ?? ''); ?></td>
                                    <td><?php echo h($asig['Nombre_D_Alumno'] ?? ''); ?></td>
                                    <td><?php echo h((string)($asig['Numero_D_Cuenta'] ?? '')); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="desasignar">
                                            <input type="hidden" name="id_relacion" value="<?php echo h((string)($asig['id_relacion'] ?? '')); ?>">
                                            <button type="submit" class="btn-eliminar" onclick="return confirm('¿Desasignar este alumno?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="sin-datos">
                        <p>No hay asignaciones registradas.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>

</html>
