<?php

declare(strict_types=1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "ejemplo2";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'crear_grupo') {
        $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
        if (empty($nombre_grupo)) {
            $mensaje = ['tipo' => 'error', 'texto' => 'El nombre del grupo no puede estar vacío.'];
        } else {
            $stmt = $conn->prepare("INSERT INTO grupos (nombre_grupo) VALUES (?)");
            if (!$stmt) {
                $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $conn->error];
            } else {
                $stmt->bind_param("s", $nombre_grupo);
                if ($stmt->execute()) {
                    $mensaje = ['tipo' => 'exito', 'texto' => 'Grupo creado exitosamente.'];
                } else {
                    if (strpos($stmt->error, 'Duplicate') !== false) {
                        $mensaje = ['tipo' => 'error', 'texto' => 'El grupo ya existe.'];
                    } else {
                        $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $stmt->error];
                    }
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'asignar') {
        $id_grupo = (int)($_POST['id_grupo'] ?? 0);
        $id_alumno = (int)($_POST['id_alumno'] ?? 0);

        if ($id_grupo <= 0 || $id_alumno <= 0) {
            $mensaje = ['tipo' => 'error', 'texto' => 'Debes seleccionar grupo y alumno.'];
        } else {
            $stmt = $conn->prepare("INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES (?, ?)");
            if (!$stmt) {
                $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $conn->error];
            } else {
                $stmt->bind_param("ii", $id_grupo, $id_alumno);
                if ($stmt->execute()) {
                    $mensaje = ['tipo' => 'exito', 'texto' => 'Alumno asignado al grupo.'];
                } else {
                    if (strpos($stmt->error, 'Duplicate') !== false) {
                        $mensaje = ['tipo' => 'error', 'texto' => 'El alumno ya está en este grupo.'];
                    } else {
                        $mensaje = ['tipo' => 'error', 'texto' => 'Error: ' . $stmt->error];
                    }
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'desasignar') {
        $id_relacion = (int)($_POST['id_relacion'] ?? 0);
        if ($id_relacion <= 0) {
            $mensaje = ['tipo' => 'error', 'texto' => 'ID de relación inválida.'];
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
            }
        }
    }
}

// Obtener datos
$grupos_result = $conn->query("SELECT * FROM grupos ORDER BY nombre_grupo");
$alumnos_result = $conn->query("SELECT Numero_D_Cuenta, Nombre_D_Alumno FROM Alumnos ORDER BY Nombre_D_Alumno");
$asignaciones_result = $conn->query("
    SELECT ga.id_relacion, g.nombre_grupo, a.Numero_D_Cuenta, a.Nombre_D_Alumno
    FROM grupo_alumno ga
    JOIN grupos g ON ga.id_grupo = g.id_grupo
    JOIN Alumnos a ON ga.id_alumno = a.Numero_D_Cuenta
    ORDER BY g.nombre_grupo, a.Nombre_D_Alumno
");

$conn->close();

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 28px;
        }

        .back-button {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .content {
            padding: 30px;
        }

        .mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            animation: slideIn 0.3s ease-out;
        }

        .mensaje.exito {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .secciones {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .seccion {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
        }

        .seccion h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            width: 100%;
            padding: 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #5568d3;
        }

        button:active {
            transform: scale(0.98);
        }

        .tabla-container {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            overflow-x: auto;
        }

        .tabla-container h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            color: #333;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .btn-eliminar {
            background: #dc3545;
            padding: 6px 12px;
            font-size: 12px;
            width: auto;
            border-radius: 4px;
        }

        .btn-eliminar:hover {
            background: #c82333;
        }

        .sin-datos {
            text-align: center;
            padding: 20px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>👥 Gestión de Grupos</h1>
            <a href="index2.php" class="back-button">← Volver</a>
        </div>

        <div class="content">
            <?php if (isset($mensaje)): ?>
                <div class="mensaje <?php echo h($mensaje['tipo']); ?>">
                    <?php echo h($mensaje['texto']); ?>
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
                        <button type="submit">Crear grupo</button>
                    </form>
                </div>

                <!-- Asignar Alumno -->
                <div class="seccion">
                    <h2>➕ Asignar Alumno</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="asignar">
                        <div class="form-group">
                            <label for="id_grupo">Grupo:</label>
                            <select id="id_grupo" name="id_grupo" required>
                                <option value="">-- Selecciona un grupo --</option>
                                <?php while ($grupo = $grupos_result->fetch_assoc()): ?>
                                    <option value="<?php echo h((string)$grupo['id_grupo']); ?>">
                                        <?php echo h($grupo['nombre_grupo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_alumno">Alumno:</label>
                            <select id="id_alumno" name="id_alumno" required>
                                <option value="">-- Selecciona un alumno --</option>
                                <?php while ($alumno = $alumnos_result->fetch_assoc()): ?>
                                    <option value="<?php echo h((string)$alumno['Numero_D_Cuenta']); ?>">
                                        <?php echo h($alumno['Nombre_D_Alumno']); ?> (<?php echo h((string)$alumno['Numero_D_Cuenta']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit">Asignar alumno</button>
                    </form>
                </div>
            </div>

            <!-- Tabla de Asignaciones -->
            <div class="tabla-container">
                <h2>📋 Asignaciones Actuales</h2>
                <?php if ($asignaciones_result && $asignaciones_result->num_rows > 0): ?>
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
                            <?php while ($asignacion = $asignaciones_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo h($asignacion['nombre_grupo']); ?></td>
                                    <td><?php echo h($asignacion['Nombre_D_Alumno']); ?></td>
                                    <td><?php echo h((string)$asignacion['Numero_D_Cuenta']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="desasignar">
                                            <input type="hidden" name="id_relacion" value="<?php echo h((string)$asignacion['id_relacion']); ?>">
                                            <button type="submit" class="btn-eliminar" onclick="return confirm('¿Desasignar este alumno?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="sin-datos">
                        <p>No hay asignaciones aún.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>