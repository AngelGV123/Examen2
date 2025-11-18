<?php
// Conexión a la base de datos
$server   = "127.0.0.1"; 
$port     = "3309";
$username = "root";
$password = "";
$database = "proyecto";

$conn = new mysqli($server, $username, $password, $database, (int)$port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Lista de materias (tablas)
$materias = [
    'analisishistorico' => 'Análisis Histórico',
    'diversidadterrestre' => 'Diversidad Terrestre',
    'expresionartisticas' => 'Expresión Artística',
    'idioma3' => 'Idioma 3',
    'modelosmatematicos' => 'Modelos Matemáticos',
    'producciontexto' => 'Producción de Texto',
    'solucioneslogicas' => 'Soluciones Lógicas',
    'transformaciondmateria' => 'Transformación de la Materia'
];

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $materia = $_POST['materia'];
    $cuenta = (int)$_POST['numero_cuenta'];
    $parcial = (int)$_POST['parcial'];

    $hetero = (float)$_POST['hetero'];
    $coe = (float)$_POST['coe'];
    $auto = (float)$_POST['auto'];

    $porc_hetero = (float)$_POST['porc_hetero'];
    $porc_coe = (float)$_POST['porc_coe'];
    $porc_auto = (float)$_POST['porc_auto'];

    if (empty($materia) || $cuenta <= 0 || $parcial <= 0) {
        echo "<p style='color:red;'>Debes seleccionar materia, alumno y parcial.</p>";
    } else {
        // Validar que la materia existe en la lista
        if (!isset($materias[$materia])) {
            echo "<p style='color:red;'>Materia inválida.</p>";
        } else {
            // Verificar que el alumno existe (usando prepared statement)
            $stmtCheck = $conn->prepare("SELECT Numero_D_Cuenta FROM alumnos WHERE Numero_D_Cuenta = ?");
            $stmtCheck->bind_param("i", $cuenta);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();

            if ($resultCheck->num_rows == 0) {
                echo "<p style='color:red;'>No existe un alumno con el número de cuenta $cuenta.</p>";
            } else {
                $suma = $porc_hetero + $porc_coe + $porc_auto;
                if ($suma != 100) {
                    echo "<p style='color:red;'>La suma de los porcentajes debe ser exactamente 100% (Actual: $suma%)</p>";
                } else {
                    $promedio = round(($hetero * $porc_hetero / 100) + ($coe * $porc_coe / 100) + ($auto * $porc_auto / 100), 2);

                    // Verificar si ya hay registro del alumno para ese parcial en esa materia
                    $stmtVerif = $conn->prepare("SELECT HeteroEvaluacion, CoEvaluacion, AutoEvaluacion FROM $materia WHERE Numero_D_Cuenta = ? AND parcial = ?");
                    $stmtVerif->bind_param("ii", $cuenta, $parcial);
                    $stmtVerif->execute();
                    $resultVerif = $stmtVerif->get_result();

                    if ($resultVerif->num_rows > 0) {
                        // Obtener valores actuales
                        $rowVerif = $resultVerif->fetch_assoc();
                        $heteroActual = (float)$rowVerif['HeteroEvaluacion'];
                        $coeActual = (float)$rowVerif['CoEvaluacion'];
                        $autoActual = (float)$rowVerif['AutoEvaluacion'];

                        // Si todos los valores son 0, se trata de un registro vacío (creado al inscribir)
                        // Lo actualizamos sin duplicar
                        if ($heteroActual == 0 && $coeActual == 0 && $autoActual == 0) {
                            $stmtUpdate = $conn->prepare("UPDATE $materia 
                                    SET HeteroEvaluacion = ?, CoEvaluacion = ?, AutoEvaluacion = ?, promedio = ? 
                                    WHERE Numero_D_Cuenta = ? AND parcial = ?");
                            $stmtUpdate->bind_param("ddddii", $hetero, $coe, $auto, $promedio, $cuenta, $parcial);

                            if ($stmtUpdate->execute()) {
                                echo "<p style='color:green;'>Calificaciones actualizadas correctamente para el <strong>Parcial $parcial</strong> en <strong>{$materias[$materia]}</strong>.</p>";
                                echo "<p><strong>Promedio final:</strong> $promedio</p>";
                            } else {
                                echo "<p style='color:red;'>Error al guardar: " . $stmtUpdate->error . "</p>";
                            }
                        } else {
                            // Ya tiene calificaciones, realizar UPDATE normal
                            $stmtUpdate = $conn->prepare("UPDATE $materia 
                                    SET HeteroEvaluacion = ?, CoEvaluacion = ?, AutoEvaluacion = ?, promedio = ? 
                                    WHERE Numero_D_Cuenta = ? AND parcial = ?");
                            $stmtUpdate->bind_param("ddddii", $hetero, $coe, $auto, $promedio, $cuenta, $parcial);

                            if ($stmtUpdate->execute()) {
                                echo "<p style='color:green;'>Calificaciones actualizadas correctamente para el <strong>Parcial $parcial</strong> en <strong>{$materias[$materia]}</strong>.</p>";
                                echo "<p><strong>Promedio final:</strong> $promedio</p>";
                            } else {
                                echo "<p style='color:red;'>Error al guardar: " . $stmtUpdate->error . "</p>";
                            }
                        }
                    } else {
                        // No existe registro, hacer INSERT
                        $stmtInsert = $conn->prepare("INSERT INTO $materia (Numero_D_Cuenta, parcial, HeteroEvaluacion, CoEvaluacion, AutoEvaluacion, promedio) 
                                VALUES (?, ?, ?, ?, ?, ?)");
                        $stmtInsert->bind_param("iidddd", $cuenta, $parcial, $hetero, $coe, $auto, $promedio);

                        if ($stmtInsert->execute()) {
                            echo "<p style='color:green;'>Calificaciones registradas correctamente para el <strong>Parcial $parcial</strong> en <strong>{$materias[$materia]}</strong>.</p>";
                            echo "<p><strong>Promedio final:</strong> $promedio</p>";
                        } else {
                            echo "<p style='color:red;'>Error al guardar: " . $stmtInsert->error . "</p>";
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Calificación</title>
    <style>
        * {
            text-align: center;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        form {
            background: #dcdcdc;
            padding: 30px;
            width: 500px;
            border-radius: 10px;
            box-shadow: 1px 4px 12px rgba(0, 0, 0, 0.2);
            margin: 0 auto;
            border-radius: 20px;
        }

        label {
            display: block;
            padding-top: 10px;
            font-weight: bold;
        }

        input {
            background: #f5f5f5;
            width: 90%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #000;
            border-radius: 10px;
        }

        button {
            background-color: #99a1af;
            color: white;
            padding: 10px;
            border: none;
            width: 80%;
            cursor: pointer;
            margin-top: 20px;
            color: black;
            font-weight: bold;
        }

        button:hover {
            background-color: #6a7282;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;

        }

        .back-button {
            text-decoration: none;
            color: #333;
            font-size: 24px;
            margin-right: 15px;
            padding: 5px;
            border: 1px solid transparent;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            color: #007bff;
            background-color: #eee;
        }

        select {
            background: #f5f5f5;
            width: 90%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #000;
            border-radius: 10px;
        }

        .divider {
            height: 1px;
            /* grosor 2px */
            width: 90%;
            /* ancho 90% */
            background-color: #000;
            /* color (ajusta a tu preferencia) */
            border: none;
            /* eliminar borde predeterminado del <hr> */
            margin: 1rem auto;
            /* espacio arriba/abajo y centrado horizontal */
            display: block;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="header-container">
        <a href="index2.php" class="back-button" title="Volver">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <h2> Agregar calificaciones</h2>
    </div>
    <form action="" method="POST">
        <label>Materia:</label>
        <select name="materia" required>
            <option value="">-- Selecciona una materia --</option>
            <?php
            foreach ($materias as $tabla => $nombre) {
                echo "<option value='$tabla'>$nombre</option>";
            }
            ?>
        </select>

        <label>Número de cuenta:</label>
        <input type="number" name="numero_cuenta" required>

        <label>Parcial:</label>
        <select name="parcial" required>
            <option value="">-- Selecciona parcial --</option>
            <option value="1">Parcial 1</option>
            <option value="2">Parcial 2</option>
            <option value="3">Parcial 3</option>
        </select>

        <hr class="divider" />

        <h4>Calificaciones</h4>
        <div class="grupo">
            <div>
                <label>Heteroevaluación:</label>
                <input type="number" step="0.01" name="hetero" min="0" max="10" required>
                <label>% Hetero:</label>
                <input type="number" step="0.01" name="porc_hetero" value="90" required>
            </div>
            <div>
                <label>Coevaluación:</label>
                <input type="number" step="0.01" name="coe" min="0" max="10" required>
                <label>% Coe:</label>
                <input type="number" step="0.01" name="porc_coe" value="5" required>
            </div>
            <div>
                <label>Autoevaluación:</label>
                <input type="number" step="0.01" name="auto" min="0" max="10" required>
                <label>% Auto:</label>
                <input type="number" step="0.01" name="porc_auto" value="5" required>
            </div>
        </div>

        <button type="submit">Guardar Calificación</button>
    </form>
</body>

</html>