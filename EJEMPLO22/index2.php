<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-white">  
    <div class="min-h-screen flex flex-col p-6 items-center">

        <a href="agregar_alumno.php">
            <button class="bg-gray-400 text-gray-800 font-bold p-3 mt-5 rounded-lg hover:bg-gray-500 items-center justify-between" style="width: 200px;">
                Registrar Alumno
            </button>
        </a>
        <a href="agregar_calificacion.php">
            <button class="bg-gray-400 text-gray-800 font-bold p-3 mt-5 rounded-lg hover:bg-gray-500 items-center justify-between" style="width: 200px;">
                Registrar Calificacion
            </button>
        </a>
        <a href="gestionar_alumnos.php">
            <button class="bg-blue-400 text-gray-800 font-bold p-3 mt-5 rounded-lg hover:bg-blue-500 items-center justify-between" style="width: 200px;">
                👥 Gestionar Alumnos
            </button>
        </a>
        <a href="gestionar_grupos.php">
            <button class="bg-purple-400 text-gray-800 font-bold p-3 mt-5 rounded-lg hover:bg-purple-500 items-center justify-between" style="width: 200px;">
                👫 Gestionar Grupos
            </button>
        </a>
        <a href="conexion_pdo_promedio_col.php">
            <button class="bg-green-400 text-gray-800 font-bold p-3 mt-5 rounded-lg hover:bg-green-500 items-center justify-between" style="width: 200px;">
                📊 Ver Promedios
            </button>
        </a>

    </div>

    <script>
        // Oculta las tablas al inicio (contrario a la implementación original, para que funcione el toggle)
        document.querySelectorAll('.dropdown-content').forEach(table => {
            table.classList.add('hidden');
        });

        // Lógica del Dropdown
        document.querySelectorAll('.dropdown-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const tableId = this.getAttribute('data-table');
                const table = document.getElementById(tableId);
                const icon = this.querySelector('.dropdown-icon');

                table.classList.toggle('hidden');

                icon.classList.toggle('rotate-180');
            });
        });
    </script>
</body>

</html>