# 📋 Configuración de Grupos - Instrucciones

## Estructura de Base de Datos Requerida

El sistema de filtrado por grupos requiere una tabla de relación entre Alumnos y Grupos. A continuación encontrarás los comandos SQL necesarios.

### 1. Verificar existencia de tabla `grupos`

```sql
DESCRIBE grupos;
```

Si no existe, crear:

```sql
CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Crear tabla de relación `grupo_alumno`

```sql
CREATE TABLE grupo_alumno (
    id_relacion INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_alumno INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_alumno) REFERENCES Alumnos(Numero_D_Cuenta) ON DELETE CASCADE,
    UNIQUE KEY unique_grupo_alumno (id_grupo, id_alumno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Insertar datos de ejemplo

#### Crear grupos:

```sql
INSERT INTO grupos (nombre_grupo) VALUES
('Grupo A'),
('Grupo B'),
('Grupo C');
```

#### Asignar alumnos a grupos (ejemplo):

```sql
-- Cambiar los números de cuenta según tus datos reales
INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES
(1, 123456),  -- Alumno 123456 al Grupo A
(1, 123457),  -- Alumno 123457 al Grupo A
(2, 123458),  -- Alumno 123458 al Grupo B
(2, 123459);  -- Alumno 123459 al Grupo B
```

## 📌 Notas Importantes

- **Foreign Keys**: Las claves foráneas están configuradas con `ON DELETE CASCADE`, lo que significa que si eliminas un grupo, se eliminarán automáticamente todas las asignaciones.

- **Unicidad**: La combinación `(id_grupo, id_alumno)` es única, evitando duplicados (un alumno no puede asignarse dos veces al mismo grupo).

- **Collation**: Usa `utf8mb4_unicode_ci` para consistencia con el resto de la base de datos.

## 🔧 Operaciones Comunes

### Asignar un alumno a un grupo:

```sql
INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES (1, 123456);
```

### Cambiar un alumno de grupo:

```sql
DELETE FROM grupo_alumno WHERE id_alumno = 123456;
INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES (2, 123456);
```

### Ver todos los alumnos de un grupo:

```sql
SELECT a.Numero_D_Cuenta, a.Nombre_D_Alumno
FROM grupo_alumno ga
JOIN Alumnos a ON ga.id_alumno = a.Numero_D_Cuenta
WHERE ga.id_grupo = 1;
```

### Ver todos los grupos de un alumno:

```sql
SELECT g.nombre_grupo
FROM grupo_alumno ga
JOIN grupos g ON ga.id_grupo = g.id_grupo
WHERE ga.id_alumno = 123456;
```

## ✅ Verificación

Una vez creadas las tablas, puedes verificar que funciona correctamente visitando `conexion_pdo_promedio_col.php`:

1. Deberías ver la barra de navegación "👥 Grupos:" en la parte superior
2. Verás botones para "Todos" y cada grupo disponible
3. Al hacer clic, se filtrarán los alumnos por grupo
4. Los datos se preservarán al cambiar entre semestres, parciales y búsqueda
