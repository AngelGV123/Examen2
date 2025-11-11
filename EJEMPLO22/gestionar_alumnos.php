<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "ejemplo2";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$accion = $_GET['accion'] ?? '';
$cuenta = $_GET['cuenta'] ?? 0;
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion_post = $_POST['accion'] ?? '';

    if ($accion_post === 'editar') {
        $numero_cuenta = (int)$_POST['numero_cuenta'];
        $nombre_alumno = strtoupper(trim($_POST['nombre_alumno']));
        $numero_cuenta_original = (int)$_POST['numero_cuenta_original'];

        if (empty($nombre_alumno)) {
            $mensaje = "El nombre del alumno no puede estar vacío.";
            $tipo_mensaje = "error";
        } else {
            // Verificar si el nuevo número de cuenta ya existe (y no es el mismo alumno)
            if ($numero_cuenta != $numero_cuenta_original) {
                $stmtCheck = $conn->prepare("SELECT Numero_D_Cuenta FROM alumnos WHERE Numero_D_Cuenta = ?");
                $stmtCheck->bind_param("i", $numero_cuenta);
                $stmtCheck->execute();
                $resultCheck = $stmtCheck->get_result();

                if ($resultCheck->num_rows > 0) {
                    $mensaje = "El número de cuenta ya existe.";
                    $tipo_mensaje = "error";
                } else {
                    // Actualizar número de cuenta en tabla Alumnos
                    $stmtUpdate = $conn->prepare("UPDATE alumnos SET Numero_D_Cuenta = ?, Nombre_D_Alumno = ? WHERE Numero_D_Cuenta = ?");
                    $stmtUpdate->bind_param("isi", $numero_cuenta, $nombre_alumno, $numero_cuenta_original);
                    $stmtUpdate->execute();

                    // Actualizar en todas las tablas de materias
                    $tablas = [
                        'analisishistorico',
                        'diversidadterrestre',
                        'expresionartisticas',
                        'idioma3',
                        'modelosmatematicos',
                        'producciontexto',
                        'solucioneslogicas',
                        'transformaciondmateria'
                    ];

                    foreach ($tablas as $tabla) {
                        $stmtMat = $conn->prepare("UPDATE $tabla SET Numero_D_Cuenta = ? WHERE Numero_D_Cuenta = ?");
                        $stmtMat->bind_param("ii", $numero_cuenta, $numero_cuenta_original);
                        $stmtMat->execute();
                    }

                    $mensaje = "✅ Alumno actualizado correctamente.";
                    $tipo_mensaje = "success";
                    $accion = '';
                }
            } else {
                // Solo actualizar nombre
                $stmtUpdate = $conn->prepare("UPDATE alumnos SET Nombre_D_Alumno = ? WHERE Numero_D_Cuenta = ?");
                $stmtUpdate->bind_param("si", $nombre_alumno, $numero_cuenta);
                $stmtUpdate->execute();

                $mensaje = "✅ Alumno actualizado correctamente.";
                $tipo_mensaje = "success";
                $accion = '';
            }
        }
    } elseif ($accion_post === 'eliminar') {
        $numero_cuenta = (int)$_POST['numero_cuenta'];

        // Primero eliminar de todas las tablas de materias (hijas) para no violar la FK
        $tablas = [
            'analisishistorico',
            'diversidadterrestre',
            'expresionartisticas',
            'idioma3',
            'modelosmatematicos',
            'producciontexto',
            'solucioneslogicas',
            'transformaciondmateria'
        ];

        foreach ($tablas as $tabla) {
            $stmtMat = $conn->prepare("DELETE FROM $tabla WHERE Numero_D_Cuenta = ?");
            $stmtMat->bind_param("i", $numero_cuenta);
            $stmtMat->execute();
        }

        // Luego eliminar de tabla alumnos (padre)
        $stmtDel = $conn->prepare("DELETE FROM alumnos WHERE Numero_D_Cuenta = ?");
        $stmtDel->bind_param("i", $numero_cuenta);
        $stmtDel->execute();

        $mensaje = "✅ Alumno eliminado correctamente.";
        $tipo_mensaje = "success";
        $accion = '';
    } elseif ($accion_post === 'editar_calificacion') {
        $numero_cuenta = (int)$_POST['numero_cuenta'];
        $tabla_materia = $_POST['tabla_materia'];
        $parcial = (int)$_POST['parcial'];
        $hetero = (float)$_POST['hetero'];
        $coe = (float)$_POST['coe'];
        $auto = (float)$_POST['auto'];
        $porc_hetero = (float)$_POST['porc_hetero'] ?? 90;
        $porc_coe = (float)$_POST['porc_coe'] ?? 5;
        $porc_auto = (float)$_POST['porc_auto'] ?? 5;

        // Validar tabla_materia contra lista blanca
        $tablas_validas = [
            'analisishistorico',
            'diversidadterrestre',
            'expresionartisticas',
            'idioma3',
            'modelosmatematicos',
            'producciontexto',
            'solucioneslogicas',
            'transformaciondmateria'
        ];

        if (!in_array($tabla_materia, $tablas_validas)) {
            $mensaje = "❌ Materia inválida.";
            $tipo_mensaje = "error";
        } else {
            // Validar que los porcentajes sumen 100
            $suma_porcentajes = $porc_hetero + $porc_coe + $porc_auto;
            if ($suma_porcentajes != 100) {
                $mensaje = "❌ La suma de los porcentajes debe ser exactamente 100% (Actual: $suma_porcentajes%).";
                $tipo_mensaje = "error";
            } else {
                // Calcular promedio con los nuevos porcentajes
                $promedio = round(($hetero * $porc_hetero / 100) + ($coe * $porc_coe / 100) + ($auto * $porc_auto / 100), 2);

                $stmtCalif = $conn->prepare("UPDATE $tabla_materia SET HeteroEvaluacion = ?, CoEvaluacion = ?, AutoEvaluacion = ?, promedio = ? WHERE Numero_D_Cuenta = ? AND parcial = ?");
                $stmtCalif->bind_param("ddddii", $hetero, $coe, $auto, $promedio, $numero_cuenta, $parcial);

                if ($stmtCalif->execute()) {
                    $mensaje = "✅ Calificación actualizada correctamente. Promedio: $promedio";
                    $tipo_mensaje = "success";
                    $accion = '';
                } else {
                    $mensaje = "❌ Error al actualizar calificación.";
                    $tipo_mensaje = "error";
                }
            }
        }
    }
}

// Obtener datos del alumno si se está editando
$alumno_editar = null;
if ($accion === 'editar' && $cuenta > 0) {
    $stmtAlumno = $conn->prepare("SELECT Numero_D_Cuenta, Nombre_D_Alumno FROM alumnos WHERE Numero_D_Cuenta = ?");
    $stmtAlumno->bind_param("i", $cuenta);
    $stmtAlumno->execute();
    $resultAlumno = $stmtAlumno->get_result();
    if ($resultAlumno->num_rows > 0) {
        $alumno_editar = $resultAlumno->fetch_assoc();
    }
}

// Obtener calificaciones del alumno
$calificaciones = [];
if ($accion === 'editar' && $cuenta > 0) {
    $tablas_materias = [
        'analisishistorico' => 'Análisis Histórico',
        'diversidadterrestre' => 'Diversidad Terrestre',
        'expresionartisticas' => 'Expresión Artística',
        'idioma3' => 'Idioma 3',
        'modelosmatematicos' => 'Modelos Matemáticos',
        'producciontexto' => 'Producción de Texto',
        'solucioneslogicas' => 'Soluciones Lógicas',
        'transformaciondmateria' => 'Transformación de la Materia'
    ];

    foreach ($tablas_materias as $tabla => $nombre) {
        $stmt = $conn->prepare("SELECT parcial, HeteroEvaluacion, CoEvaluacion, AutoEvaluacion, promedio FROM $tabla WHERE Numero_D_Cuenta = ? ORDER BY parcial");
        $stmt->bind_param("i", $cuenta);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $calificaciones[] = [
                'tabla' => $tabla,
                'materia' => $nombre,
                'parcial' => $row['parcial'],
                'hetero' => $row['HeteroEvaluacion'],
                'coe' => $row['CoEvaluacion'],
                'auto' => $row['AutoEvaluacion'],
                'promedio' => $row['promedio']
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestionar Alumnos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            gap: 15px;
        }

        .back-button {
            text-decoration: none;
            color: #333;
            font-size: 24px;
            padding: 5px;
            border: 1px solid transparent;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            color: #007bff;
            background-color: #eee;
        }

        h2 {
            font-size: 24px;
            color: #333;
        }

        .mensaje {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            display: none;
        }

        .mensaje.show {
            display: block;
        }

        .mensaje.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Estilos para formulario de edición */
        .form-container {
            background: #dcdcdc;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 1px 4px 12px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #000;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        button {
            background-color: #99a1af;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #6a7282;
        }

        .btn-cancelar {
            background-color: #dc3545;
            color: white;
        }

        .btn-cancelar:hover {
            background-color: #c82333;
        }

        /* Estilos para tabla de alumnos */
        .tabla-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #f8fafc;
            border-bottom: 2px solid #ddd;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-weight: bold;
            color: #333;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        .acciones {
            display: flex;
            gap: 8px;
        }

        .btn-editar,
        .btn-eliminar {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-editar {
            background-color: #17a2b8;
            color: white;
        }

        .btn-editar:hover {
            background-color: #138496;
        }

        .btn-eliminar {
            background-color: #dc3545;
            color: white;
        }

        .btn-eliminar:hover {
            background-color: #c82333;
        }

        .sin-alumnos {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .sin-alumnos i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ccc;
        }

        /* Estilos para edición de calificaciones */
        .calificaciones-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
        }

        .calificaciones-container h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
        }

        .calificacion-item {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
        }

        .calificacion-item h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .calificacion-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }

        .calificacion-row input {
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }

        .calificacion-item .promedio-display {
            background: #e7f3ff;
            padding: 8px;
            border-radius: 4px;
            font-weight: bold;
            color: #0066cc;
            text-align: center;
            margin-top: 8px;
        }

        .btn-guardar-calif {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
            margin-top: 10px;
        }

        .btn-guardar-calif:hover {
            background-color: #218838;
        }

        @media (max-width: 600px) {
            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px 10px;
            }

            .acciones {
                flex-direction: column;
                gap: 5px;
            }

            .calificacion-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-container">
            <a href="index2.php" class="back-button" title="Volver">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2><?php echo ($accion === 'editar' && $alumno_editar) ? 'Editar Alumno' : 'Gestionar Alumnos'; ?></h2>
        </div>

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?> show">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if ($accion === 'editar' && $alumno_editar): ?>
            <!-- Formulario de edición -->
            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="numero_cuenta_original" value="<?php echo $alumno_editar['Numero_D_Cuenta']; ?>">

                    <div class="form-group">
                        <label for="numero_cuenta">Número de cuenta:</label>
                        <input type="number" id="numero_cuenta" name="numero_cuenta" value="<?php echo $alumno_editar['Numero_D_Cuenta']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre_alumno">Nombre del alumno:</label>
                        <input type="text" id="nombre_alumno" name="nombre_alumno" value="<?php echo htmlspecialchars($alumno_editar['Nombre_D_Alumno']); ?>" required>
                    </div>

                    <div>
                        <button type="submit">💾 Guardar cambios</button>
                        <a href="?"><button type="button" class="btn-cancelar">✕ Cancelar</button></a>
                    </div>
                </form>
            </div>

            <!-- Tabla de calificaciones para editar -->
            <?php if (!empty($calificaciones)): ?>
                <div class="calificaciones-container">
                    <h3>📊 Editar calificaciones</h3>
                    <?php
                    // Agrupar por materia
                    $califs_por_materia = [];
                    foreach ($calificaciones as $calif) {
                        $key = $calif['tabla'];
                        if (!isset($califs_por_materia[$key])) {
                            $califs_por_materia[$key] = [
                                'materia' => $calif['materia'],
                                'registros' => []
                            ];
                        }
                        $califs_por_materia[$key]['registros'][] = $calif;
                    }

                    foreach ($califs_por_materia as $tabla => $data) {
                    ?>
                        <div class="calificacion-item">
                            <h4><?php echo htmlspecialchars($data['materia']); ?></h4>
                            <?php foreach ($data['registros'] as $calif): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="accion" value="editar_calificacion">
                                    <input type="hidden" name="numero_cuenta" value="<?php echo $cuenta; ?>">
                                    <input type="hidden" name="tabla_materia" value="<?php echo $tabla; ?>">
                                    <input type="hidden" name="parcial" value="<?php echo $calif['parcial']; ?>">

                                    <strong>Parcial <?php echo $calif['parcial']; ?>:</strong>

                                    <div class="calificacion-row">
                                        <div>
                                            <label>Hetero:</label>
                                            <input type="number" name="hetero" step="0.01" min="0" max="10" value="<?php echo $calif['hetero']; ?>" required>
                                        </div>
                                        <div>
                                            <label>% Hetero:</label>
                                            <input type="number" name="porc_hetero" step="0.01" min="0" max="100" value="90" required>
                                        </div>
                                    </div>

                                    <div class="calificacion-row">
                                        <div>
                                            <label>Co:</label>
                                            <input type="number" name="coe" step="0.01" min="0" max="10" value="<?php echo $calif['coe']; ?>" required>
                                        </div>
                                        <div>
                                            <label>% Co:</label>
                                            <input type="number" name="porc_coe" step="0.01" min="0" max="100" value="5" required>
                                        </div>
                                    </div>

                                    <div class="calificacion-row">
                                        <div>
                                            <label>Auto:</label>
                                            <input type="number" name="auto" step="0.01" min="0" max="10" value="<?php echo $calif['auto']; ?>" required>
                                        </div>
                                        <div>
                                            <label>% Auto:</label>
                                            <input type="number" name="porc_auto" step="0.01" min="0" max="100" value="5" required>
                                        </div>
                                    </div>

                                    <div class="promedio-display">
                                        Promedio actual: <?php echo number_format($calif['promedio'], 2); ?>
                                    </div>

                                    <button type="submit" class="btn-guardar-calif">💾 Guardar</button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            <?php endif; ?>


        <?php elseif ($accion === 'confirmar_eliminar' && $cuenta > 0): ?>
            <!-- Confirmación de eliminación -->
            <?php
            $stmtEliminar = $conn->prepare("SELECT Numero_D_Cuenta, Nombre_D_Alumno FROM alumnos WHERE Numero_D_Cuenta = ?");
            $stmtEliminar->bind_param("i", $cuenta);
            $stmtEliminar->execute();
            $resultEliminar = $stmtEliminar->get_result();
            if ($resultEliminar->num_rows > 0) {
                $alumno = $resultEliminar->fetch_assoc();
            ?>
                <div style="background: #fff3cd; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                    <h3 style="color: #856404; margin-bottom: 10px;">⚠️ Confirmación de eliminación</h3>
                    <p style="color: #856404; margin-bottom: 15px;">¿Estás seguro de que deseas eliminar al alumno <strong><?php echo htmlspecialchars($alumno['Nombre_D_Alumno']); ?></strong> (Cuenta: <?php echo $alumno['Numero_D_Cuenta']; ?>)?</p>
                    <p style="color: #856404; font-size: 12px;"><strong>Nota:</strong> Se eliminarán todas sus calificaciones y registros en todas las materias.</p>

                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="numero_cuenta" value="<?php echo $alumno['Numero_D_Cuenta']; ?>">
                        <button type="submit" style="background-color: #dc3545; color: white; margin-top: 10px;">🗑️ Eliminar definitivamente</button>
                        <a href="?"><button type="button" class="btn-cancelar" style="background-color: #6c757d;">Cancelar</button></a>
                    </form>
                </div>
            <?php
            }
            ?>

        <?php else: ?>
            <!-- Lista de alumnos -->
            <div class="tabla-container">
                <?php
                $stmtLista = $conn->prepare("SELECT Numero_D_Cuenta, Nombre_D_Alumno FROM alumnos ORDER BY Nombre_D_Alumno ASC");
                $stmtLista->execute();
                $resultLista = $stmtLista->get_result();

                if ($resultLista->num_rows > 0) {
                ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Número de cuenta</th>
                                <th>Nombre del alumno</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $resultLista->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Numero_D_Cuenta']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Nombre_D_Alumno']); ?></td>
                                    <td>
                                        <div class="acciones">
                                            <a href="?accion=editar&cuenta=<?php echo $row['Numero_D_Cuenta']; ?>" class="btn-editar">✏️ Editar</a>
                                            <a href="?accion=confirmar_eliminar&cuenta=<?php echo $row['Numero_D_Cuenta']; ?>" class="btn-eliminar">🗑️ Eliminar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php
                } else {
                ?>
                    <div class="sin-alumnos">
                        <i class="fa-solid fa-users-slash"></i>
                        <p>No hay alumnos registrados.</p>
                    </div>
                <?php
                }
                ?>
            </div>

        <?php endif; ?>

    </div>

    <?php
    // Auto-cerrar el mensaje después de 5 segundos
    if ($mensaje) {
        echo '<script>
            setTimeout(function() {
                var msg = document.querySelector(".mensaje");
                if (msg) {
                    msg.style.display = "none";
                }
            }, 5000);
        </script>';
    }
    ?>

    <script>
        // Validar y calcular promedio en tiempo real
        document.querySelectorAll('form[action=""]').forEach(form => {
            const heteroInput = form.querySelector('input[name="hetero"]');
            const coeInput = form.querySelector('input[name="coe"]');
            const autoInput = form.querySelector('input[name="auto"]');
            const porcHeteroInput = form.querySelector('input[name="porc_hetero"]');
            const porcCoeInput = form.querySelector('input[name="porc_coe"]');
            const porcAutoInput = form.querySelector('input[name="porc_auto"]');
            const promedioDisplay = form.querySelector('.promedio-display');

            function actualizarPromedio() {
                const hetero = parseFloat(heteroInput.value) || 0;
                const coe = parseFloat(coeInput.value) || 0;
                const auto = parseFloat(autoInput.value) || 0;
                const porcHetero = parseFloat(porcHeteroInput.value) || 0;
                const porcCoe = parseFloat(porcCoeInput.value) || 0;
                const porcAuto = parseFloat(porcAutoInput.value) || 0;
                const suma = porcHetero + porcCoe + porcAuto;

                const promedio = ((hetero * porcHetero / 100) + (coe * porcCoe / 100) + (auto * porcAuto / 100)).toFixed(2);

                if (promedioDisplay) {
                    if (suma !== 100) {
                        promedioDisplay.textContent = '⚠️ La suma de porcentajes debe ser 100% (Actual: ' + suma.toFixed(2) + '%)';
                        promedioDisplay.style.background = '#ffe0b2';
                        promedioDisplay.style.color = '#e65100';
                    } else {
                        promedioDisplay.textContent = 'Promedio calculado: ' + promedio;
                        promedioDisplay.style.background = '#e7f3ff';
                        promedioDisplay.style.color = '#0066cc';
                    }
                }
            }

            if (heteroInput) heteroInput.addEventListener('input', actualizarPromedio);
            if (coeInput) coeInput.addEventListener('input', actualizarPromedio);
            if (autoInput) autoInput.addEventListener('input', actualizarPromedio);
            if (porcHeteroInput) porcHeteroInput.addEventListener('input', actualizarPromedio);
            if (porcCoeInput) porcCoeInput.addEventListener('input', actualizarPromedio);
            if (porcAutoInput) porcAutoInput.addEventListener('input', actualizarPromedio);

            // Validar antes de enviar
            form.addEventListener('submit', function(e) {
                const porcHetero = parseFloat(porcHeteroInput.value) || 0;
                const porcCoe = parseFloat(porcCoeInput.value) || 0;
                const porcAuto = parseFloat(porcAutoInput.value) || 0;
                const suma = porcHetero + porcCoe + porcAuto;

                if (suma !== 100) {
                    e.preventDefault();
                    alert('❌ Error: La suma de los porcentajes debe ser exactamente 100%.\nActual: ' + suma.toFixed(2) + '%');
                }
            });
        });
    </script>

</body>

</html>