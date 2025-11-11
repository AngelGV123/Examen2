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

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numeroCuenta = $_POST['numero_cuenta'];
    $nombreAlumno = strtoupper(trim($_POST['nombre_alumno']));
    if (strlen((string)$numeroCuenta) != 6) {
        echo "<p style='color:red;'>El número de cuenta debe tener exactamente 6 dígitos.</p>";
    }
    // Validar campos vacíos
    elseif (empty($numeroCuenta) || empty($nombreAlumno)) {
        echo "<p style='color:red;'>Por favor completa todos los campos.</p>";
    } else {
        // Evitar duplicados
        $verificar = $conn->query("SELECT * FROM alumnos WHERE Numero_D_Cuenta = $numeroCuenta");
        if ($verificar->num_rows > 0) {
            echo "<p style='color:red;'>El número de cuenta ya existe.</p>";
        } else {
            // Insertar en tabla alumnos
            $sql = "INSERT INTO alumnos (Numero_D_Cuenta, Nombre_D_Alumno) VALUES ($numeroCuenta, '$nombreAlumno')";

            if ($conn->query($sql) === TRUE) {
                echo "<p style='color:green;'>Alumno agregado correctamente.</p>";

                // Insertar registros vacíos en las demás tablas
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
                    $conn->query("INSERT INTO $tabla (Numero_D_Cuenta, HeteroEvaluacion, CoEvaluacion, AutoEvaluacion, promedio) 
                                  VALUES ($numeroCuenta, 0, 0, 0, 0)");
                }

                echo "<p style='color:blue;'>🧾 Registros creados en todas las materias.</p>";
            } else {
                echo "<p style='color:red;'>Error al agregar alumno: " . $conn->error . "</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Alumno</title>
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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <div class="header-container">
        <a href="index2.php" class="back-button" title="Volver">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <h2> Agregar nuevo alumno</h2>
    </div>

    <form action="" method="POST">
        <label>Número de cuenta:</label>
        <input type="number" maxlength="6" name="numero_cuenta" required>

        <label id="textoForm">Nombre del alumno:</label>
        <input type="text" name="nombre_alumno" required>

        <button type="submit">Guardar alumno</button>
    </form>
</body>

</html>