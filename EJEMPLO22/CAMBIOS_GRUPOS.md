# 🎯 Filtrado por Grupos - Cambios Realizados

## Resumen General

Se ha implementado un sistema completo de filtrado por grupos en el dashboard de promedios. Ahora los usuarios pueden navegar entre diferentes grupos y ver solo los datos de los alumnos que pertenecen a cada grupo.

## 📁 Archivos Modificados

### 1. **conexion_pdo_promedio_col.php** ✅

**Cambios principales:**

- ✨ Agregado parámetro `$grupo` para capturar la selección del usuario
- 🔄 Modificadas las 3 secciones principales para incluir filtrado por grupo:
  - **Detalle de alumno**: Mantiene la visualización de un alumno específico
  - **Resumen por materia**: Filtra materias para mostrar solo alumnos del grupo seleccionado
  - **Resumen por alumno**: Filtra la tabla de alumnos por grupo

**Estructura de filtrado:**

```php
$grupoWhere = '';
$grupoParams = [];
$grupoJoin = '';

if ($grupo > 0) {
    $grupoJoin = " LEFT JOIN grupo_alumno ga ON a.Numero_D_Cuenta = ga.id_alumno";
    $grupoWhere = " AND (ga.id_grupo = ? OR ? = 0)";
    $grupoParams = [$grupo, 0];
}
```

**Características:**

- Compatible con búsqueda por nombre
- Compatible con filtrado por semestre y parcial
- Los parámetros se preservan al cambiar de vista
- Barra de navegación de grupos con estilos activos

### 2. **gestionar_grupos.php** ✨ (NUEVO)

**Funcionalidad completa:**

- ✅ Crear nuevos grupos
- ✅ Asignar alumnos a grupos
- ✅ Desasignar alumnos
- ✅ Vista tabular de todas las asignaciones actuales

**Características de diseño:**

- Interfaz moderna con gradientes
- Validación en servidor para evitar duplicados
- Mensajes de éxito/error con animaciones
- Responsive design para móvil
- Tabla interactiva con opciones de eliminación

### 3. **index2.php** 🔄

**Cambios:**

- Agregado botón "👫 Gestionar Grupos" que enlaza a `gestionar_grupos.php`
- Colores consistentes con la paleta existente (púrpura)
- Mantiene el ancho de 200px como los otros botones

## 🗄️ Estructura de Base de Datos Requerida

Se requieren dos tablas nuevas:

### Tabla `grupos`

```sql
CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Tabla `grupo_alumno` (relación muchos-a-muchos)

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

## 🚀 Cómo Usar

### Paso 1: Crear las tablas

Ejecutar los comandos SQL del archivo `SETUP_GRUPOS.md`:

```bash
mysql -u root ejemplo2 < setup_grupos.sql
```

### Paso 2: Crear grupos

1. Ir a `index2.php`
2. Hacer clic en "👫 Gestionar Grupos"
3. En la sección "➕ Crear Grupo", ingresar nombres (Ej: Grupo A, Grupo B)
4. Hacer clic en "Crear grupo"

### Paso 3: Asignar alumnos

1. En "➕ Asignar Alumno", seleccionar grupo y alumno
2. Hacer clic en "Asignar alumno"
3. Ver confirmación en la tabla de asignaciones

### Paso 4: Filtrar en dashboard

1. Ir a "📊 Ver Promedios"
2. Usar la barra de navegación "👥 Grupos:"
3. Hacer clic en un grupo para filtrar
4. Hacer clic en "Todos" para ver todos los alumnos

## 🔗 Preservación de Parámetros

La navegación mantiene todos los filtros activos:

- ✅ Cambiar grupo → mantiene búsqueda, semestre, parcial
- ✅ Cambiar semestre → mantiene grupo, búsqueda, parcial
- ✅ Cambiar parcial → mantiene grupo, búsqueda, semestre
- ✅ Buscar → mantiene grupo, semestre, parcial

## 🛡️ Seguridad

- ✅ Todas las consultas usan prepared statements
- ✅ Validación de entrada en ambos lados (cliente/servidor)
- ✅ HTML escaping con función `h()`
- ✅ Foreign keys para mantener integridad referencial
- ✅ Claves únicas para evitar duplicados

## ⚙️ Funciones Principales

### En `conexion_pdo_promedio_col.php`:

- `url_with_grupo(int $g)`: Genera URL manteniendo otros parámetros
- `tryPrepareOptions()`: Intenta variantes de consultas (con semestre+parcial, solo parcial, sin filtro)

### En `gestionar_grupos.php`:

- Manejo de POST actions: `crear_grupo`, `asignar`, `desasignar`
- Validación de duplicados mediante UNIQUE constraint
- Mensajes dinámicos de éxito/error

## 📊 Flujo de Datos

```
Usuario accede a conexion_pdo_promedio_col.php
    ↓
Barra de navegación grupos muestra opciones de $grupos array
    ↓
Usuario hace clic en grupo → parámetro GET['grupo'] = id_grupo
    ↓
SQL JOIN con tabla grupo_alumno filtra resultados
    ↓
Mostrar solo alumnos asignados a ese grupo
    ↓
Mantener otros filtros (búsqueda, semestre, parcial)
```

## 📝 Notas Importantes

- **Sin grupo asignado**: Un alumno no aparecerá en el filtro si no está en `grupo_alumno`
- **Múltiples grupos**: Un alumno puede estar en varios grupos (mediante múltiples filas en `grupo_alumno`)
- **Eliminación**: Si se elimina un grupo, se eliminan automáticamente todas sus asignaciones
- **Rendimiento**: Para bases de datos grandes, considerar índices en `grupo_alumno(id_alumno, id_grupo)`

## ✅ Checklist de Implementación

- [x] Crear tablas en BD
- [x] Agregar parámetro $grupo a conexion_pdo_promedio_col.php
- [x] Implementar filtrado en consultas SQL (3 secciones)
- [x] Agregar barra de navegación de grupos
- [x] Crear gestionar_grupos.php con UI completa
- [x] Agregar botón en index2.php
- [x] Documentación en SETUP_GRUPOS.md
- [x] Preservación de parámetros URL

## 🐛 Posibles Mejoras Futuras

- Importación masiva de alumnos a grupos vía CSV
- Duplicación de alumnos entre grupos con un click
- Reporte de cobertura (alumnos sin grupo asignado)
- Edición de nombres de grupos
- Eliminación de grupos con confirmación
- Búsqueda/filtrado en tabla de asignaciones
- Permisos por grupo (profesor de grupo específico)
