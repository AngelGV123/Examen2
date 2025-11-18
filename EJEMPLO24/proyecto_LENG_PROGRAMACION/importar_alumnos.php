<?php
declare(strict_types=1);

// CONFIG CONEXIÓN (ajusta si hace falta)
$server   = "127.0.0.1";
$port     = 3309;
$username = "root";
$password = "";
$database = "proyecto";
$charset  = "utf8mb4";

$dsn = "mysql:host={$server};port={$port};dbname={$database};charset={$charset}";
try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit("❌ Error de conexión: " . $e->getMessage());
}

// --- Asegurar existencia de tabla relacional grupo_alumno (sin modificar esquema existente) ---
$pdo->exec("
    CREATE TABLE IF NOT EXISTS grupo_alumno (
        id_relacion INT AUTO_INCREMENT PRIMARY KEY,
        id_grupo INT NOT NULL,
        id_alumno VARCHAR(64) NOT NULL,
        UNIQUE KEY ux_grupo_alumno (id_grupo, id_alumno),
        INDEX (id_grupo),
        INDEX (id_alumno),
        CONSTRAINT fk_ga_grupo FOREIGN KEY (id_grupo) REFERENCES grupos(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// --- Normalizador nombre materia -> tabla función ---
function materiaToTabla(string $nombre): ?string {
    $n = mb_strtolower(trim($nombre));
    $n = iconv('UTF-8', 'ASCII//TRANSLIT', $n); // normalizar acentos
    $n = preg_replace('/[^a-z0-9 ]+/', '', $n);
    $n = preg_replace('/\s+/', ' ', $n);

    if (strpos($n, 'analisis') !== false || strpos($n, 'historico') !== false) return 'analisishistorico';
    if (strpos($n, 'diversidad') !== false) return 'diversidadterrestre';
    if (strpos($n, 'idioma') !== false) return 'idioma3';
    if (strpos($n, 'modelo') !== false || strpos($n, 'matematic') !== false) return 'modelosmatematicos';
    if (strpos($n, 'produccion') !== false || strpos($n, 'texto') !== false) return 'producciontexto';
    if (strpos($n, 'solucion') !== false || strpos($n, 'tecnolog') !== false) return 'solucioneslogicas';
    if (strpos($n, 'transformacion') !== false) return 'transformaciondmateria';
    if (strpos($n, 'expresion') !== false || strpos($n, 'artist') !== false) return 'expresionartisticas';
    return null;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar'])) {
    $parcial = isset($_POST['parcial']) ? (int)$_POST['parcial'] : 1;
    if ($parcial < 1) $parcial = 1;
    if ($parcial > 3) $parcial = 1;

    // comprobar archivo
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $mensaje = "❌ Error al subir el archivo. Asegúrate de seleccionar un archivo y que su tamaño no exceda el límite del servidor.";
    } else {
        $tmp = $_FILES['archivo']['tmp_name'];
        $name = $_FILES['archivo']['name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        // Leer filas
        $rows = [];
        if ($ext === 'csv') {
            if (($fh = fopen($tmp, 'r')) === false) {
                $mensaje = "❌ No se pudo abrir CSV.";
            } else {
                while (($data = fgetcsv($fh, 0, ",")) !== false) {
                    if (count($rows) === 0 && isset($data[0])) {
                        $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]); // limpiar BOM
                    }
                    $rows[] = $data;
                }
                fclose($fh);
            }
        } elseif ($ext === 'xlsx' || $ext === 'xls') {
            if (!file_exists(__DIR__ . '/SimpleXLSX.php')) {
                $mensaje = "❌ Para leer .xlsx necesitas colocar SimpleXLSX.php en la misma carpeta.";
            } else {
                require_once __DIR__ . '/SimpleXLSX.php';
                $xlsx = new \SimpleXLSX($tmp);
                $rows = $xlsx->rows();
            }
        } else {
            $mensaje = "❌ Tipo de archivo no soportado. Usa CSV o XLSX.";
        }

        // Si hay filas procesar
        if (empty($mensaje) && count($rows) > 2) {
            // --- determinar grupo destino ---
            $grupoDestino = null;
            $modo = $_POST['modo_grupo'] ?? '';

            try {
                $pdo->beginTransaction();

                if ($modo === 'existente' && !empty($_POST['grupo_existente'])) {
                    $grupoDestino = (int)$_POST['grupo_existente'];
                    // validar que exista
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE id = ?");
                    $chk->execute([$grupoDestino]);
                    if ((int)$chk->fetchColumn() === 0) {
                        throw new Exception("El grupo seleccionado no existe.");
                    }
                } elseif ($modo === 'nuevo' && !empty($_POST['grupo_nuevo']) && !empty($_POST['programa_nuevo'])) {
                    $nombreNuevo = trim($_POST['grupo_nuevo']);
                    $progNuevo = trim($_POST['programa_nuevo']);
                    $insG = $pdo->prepare("INSERT INTO grupos (nombre, programa) VALUES (?, ?)");
                    $insG->execute([$nombreNuevo, $progNuevo]);
                    $grupoDestino = (int)$pdo->lastInsertId();
                } else {
                    throw new Exception("Debes seleccionar o crear un grupo (elige 'Grupo existente' o 'Crear grupo nuevo').");
                }

                // normalizar filas (rellenar columnas faltantes)
                $maxCols = 0;
                foreach ($rows as $r) if (count($r) > $maxCols) $maxCols = count($r);
                for ($i=0;$i<count($rows);$i++) {
                    for ($c = 0; $c < $maxCols; $c++) {
                        if (!isset($rows[$i][$c])) $rows[$i][$c] = '';
                    }
                }

                // Identificar materias como bloques H,C,A,Final a partir de fila1 y fila2
                $headers1 = $rows[0];
                $headers2 = $rows[1];
                $materias = [];
                $c = 2; // columnas empiezan en índice 2 (A=0,B=1)
                while ($c < $maxCols) {
                    $label = trim((string)($headers2[$c] ?? ''));
                    if (strcasecmp($label, 'H') === 0 || strcasecmp($label, 'Hetero') === 0 || strcasecmp($label, 'Heteroevaluacion') === 0) {
                        $hCol = $c; $cCol = $c+1; $aCol = $c+2; $fCol = $c+3;
                        $matName = '';
                        for ($x = $hCol; $x <= $fCol; $x++) {
                            if (!empty(trim((string)($headers1[$x] ?? '')))) { $matName = trim((string)$headers1[$x]); break; }
                        }
                        if ($matName === '') {
                            for ($x = $hCol; $x >= 0; $x--) {
                                if (!empty(trim((string)($headers1[$x] ?? '')))) { $matName = trim((string)$headers1[$x]); break; }
                            }
                        }
                        if ($matName === '') $matName = "materia_{$hCol}";
                        $materias[] = ['name' => $matName, 'cols' => ['h'=>$hCol,'c'=>$cCol,'a'=>$aCol,'f'=>$fCol]];
                        $c = $fCol + 1;
                    } else {
                        $c++;
                    }
                }
                // fallback si no se detectaron materias
                if (count($materias) === 0) {
                    $c = 2;
                    while ($c + 3 < $maxCols) {
                        $matName = trim((string)($headers1[$c] ?? ''));
                        if ($matName === '') {
                            for ($x = $c; $x >= 0; $x--) {
                                if (!empty(trim((string)($headers1[$x] ?? '')))) { $matName = trim((string)$headers1[$x]); break; }
                            }
                            if ($matName === '') $matName = "materia_{$c}";
                        }
                        $materias[] = ['name'=>$matName,'cols'=>['h'=>$c,'c'=>$c+1,'a'=>$c+2,'f'=>$c+3]];
                        $c += 5;
                    }
                }

                // preparar upsert alumno y statement para grupo_alumno
                $upsertAlumnoStmt = $pdo->prepare("
                    INSERT INTO alumnos (Numero_D_Cuenta, Nombre_D_Alumno)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE Nombre_D_Alumno = VALUES(Nombre_D_Alumno)
                ");

                $insertGrupoAlumno = $pdo->prepare("INSERT IGNORE INTO grupo_alumno (id_grupo, id_alumno) VALUES (?, ?)");

                $filasProcesadas = 0;
                $procesados = 0;

                // recorrer alumnos (fila 3 en adelante)
                for ($r = 2; $r < count($rows); $r++) {
                    $fila = $rows[$r];
                    $numCuenta = isset($fila[0]) ? trim((string)$fila[0]) : '';
                    $nombre = isset($fila[1]) ? trim((string)$fila[1]) : '';

                    if ($numCuenta === '' || $nombre === '') continue;

                    // upsert alumno
                    $upsertAlumnoStmt->execute([$numCuenta, mb_strtoupper($nombre)]);

                    // asignar a grupo
                    if ($grupoDestino) {
                        $insertGrupoAlumno->execute([$grupoDestino, $numCuenta]);
                    }

                    $filasProcesadas++;

                    // recorrer materias
                    foreach ($materias as $mat) {
                        $cols = $mat['cols'];
                        $hVal = isset($fila[$cols['h']]) ? trim((string)$fila[$cols['h']]) : '';
                        $cVal = isset($fila[$cols['c']]) ? trim((string)$fila[$cols['c']]) : '';
                        $aVal = isset($fila[$cols['a']]) ? trim((string)$fila[$cols['a']]) : '';
                        $fVal = isset($fila[$cols['f']]) ? trim((string)$fila[$cols['f']]) : '';

                        if ($hVal === '' && $cVal === '' && $aVal === '' && $fVal === '') continue;

                        $tabla = materiaToTabla($mat['name']);
                        if ($tabla === null) continue;

                        $hValN = ($hVal === '' ? null : floatval(str_replace(',', '.', $hVal)));
                        $cValN = ($cVal === '' ? null : floatval(str_replace(',', '.', $cVal)));
                        $aValN = ($aVal === '' ? null : floatval(str_replace(',', '.', $aVal)));
                        $fValN = ($fVal === '' ? null : floatval(str_replace(',', '.', $fVal)));

                        // si existe registro actualizar, sino insertar
                        $check = $pdo->prepare("SELECT COUNT(*) FROM {$tabla} WHERE Numero_D_Cuenta = ?");
                        $check->execute([$numCuenta]);
                        $exists = (int)$check->fetchColumn() > 0;

                        if ($exists) {
                            $sqlUpd = "UPDATE {$tabla} SET HeteroEvaluacion = ?, CoEvaluacion = ?, AutoEvaluacion = ?, promedio = ?, parcial = ? WHERE Numero_D_Cuenta = ?";
                            $stmtUpd = $pdo->prepare($sqlUpd);
                            $stmtUpd->execute([$hValN, $cValN, $aValN, $fValN, $parcial, $numCuenta]);
                        } else {
                            $sqlIns = "INSERT INTO {$tabla} (Numero_D_Cuenta, parcial, HeteroEvaluacion, CoEvaluacion, AutoEvaluacion, promedio) VALUES (?, ?, ?, ?, ?, ?)";
                            $stmtIns = $pdo->prepare($sqlIns);
                            $stmtIns->execute([$numCuenta, $parcial, $hValN, $cValN, $aValN, $fValN]);
                        }

                        $procesados++;
                    } // foreach materias
                } // foreach filas

                $pdo->commit();
                $mensaje = "✅ Import terminado. Filas procesadas: {$filasProcesadas}. Entradas materias insertadas/actualizadas: {$procesados}.";
                $mensaje .= "<script>setTimeout(()=>location.reload(),1200);</script>";

            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $mensaje = "❌ Error durante la importación: " . h($e->getMessage());
            }
        } // if rows > 2
    } // else archivo ok
}

// obtener lista de grupos para el select (usar columnas correctas: id, nombre, programa)
$grupos = [];
try {
    $grupos = $pdo->query("SELECT id, nombre, programa FROM grupos ORDER BY nombre")->fetchAll();
} catch (Exception $e) {
    // si falla, se sigue mostrando la página; simplemente la lista estará vacía
    $grupos = [];
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Importar alumnos/Calificaciones y Asignar a Grupo</title>
    <style>
        body{font-family:Inter,Arial;padding:24px;background:#f8fafc}
        .card{background:white;padding:24px;border-radius:12px;max-width:960px;margin:0 auto;box-shadow:0 6px 24px rgba(0,0,0,.06)}
        label{display:block;margin-top:12px}
        input[type=file]{padding:6px}
        .row{display:flex;gap:12px;align-items:center}
        .btn-modern { padding: 10px 18px; background:#2563eb; color:#fff; border:none; border-radius:8px; cursor:pointer; display:inline-flex; gap:8px; align-items:center; }
        .btn-modern:disabled { background:#9ca3af; cursor:not-allowed; }
        .back { display:inline-block; margin-top:14px; text-decoration:none; color:#111827; }
        .small{color:#666;font-size:13px}
        .notice{padding:10px;background:#eef2ff;border-radius:6px;margin-top:8px}
        select, input[type="text"]{width:100%; padding:8px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box}
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="card">
    <h1>Importar archivo (.csv/.xlsx) y asignar a grupo</h1>
    <p class="small">Formato: fila1 = nombres materias, fila2 = H C A Final parcial, fila3+ = alumnos (No. cuenta, Nombre, bloques de materias).</p>

    <?php if ($mensaje): ?>
        <div class="notice"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="formImport">
        <label>Archivo (.csv o .xlsx):</label>
        <input type="file" name="archivo" accept=".csv,.xlsx" required>

        <label>Parcial:
            <select name="parcial">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </label>

        <label style="margin-top:14px;">¿A qué grupo deseas asignar?</label>

        <label><input type="radio" name="modo_grupo" value="existente" required> Grupo existente</label>
        <select name="grupo_existente" id="grupo_existente">
            <option value="">-- Selecciona un grupo --</option>
            <?php foreach ($grupos as $g): ?>
                <option value="<?php echo h((string)$g['id']); ?>"><?php echo h($g['nombre']) . ' — ' . h($g['programa']); ?></option>
            <?php endforeach; ?>
        </select>

        <label style="margin-top:8px;"><input type="radio" name="modo_grupo" value="nuevo"> Crear grupo nuevo</label>
        <input type="text" name="grupo_nuevo" placeholder="Nombre del nuevo grupo">
        <select name="programa_nuevo">
            <option value="">-- Programa educativo --</option>
            <option value="Bachillerato">Bachillerato</option>
            <option value="Ingeniería de Software">Ingeniería de Software</option>
            <option value="Médico Cirujano">Médico Cirujano</option>
            <option value="Enfermería">Enfermería</option>
        </select>

        <div style="margin-top:16px;">
            <button type="submit" name="importar" id="btnImportar" class="btn-modern">
                <i class="fa-solid fa-file-arrow-up"></i> Importar y asignar
            </button>

            <a class="back" href="index2.php" style="margin-left:12px;">← Volver</a>
        </div>
    </form>
</div>

<script>
    // Mostrar/ocultar selects según radio
    const radios = document.querySelectorAll('input[name="modo_grupo"]');
    const selExistente = document.getElementById('grupo_existente');
    function updateMode() {
        const sel = document.querySelector('input[name="modo_grupo"]:checked');
        if (!sel) return;
        if (sel.value === 'existente') {
            selExistente.disabled = false;
        } else {
            selExistente.disabled = true;
        }
    }
    radios.forEach(r => r.addEventListener('change', updateMode));
    updateMode();

    // Botón import: deshabilitar al enviar para evitar doble envío
    document.getElementById('formImport').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnImportar');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';
        }
    });
</script>
</body>
</html>
