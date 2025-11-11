# 📚 Guía de Gestión de Alumnos y Calificaciones

## Archivos principales

### 1. **gestionar_alumnos.php** (NUEVO)

Panel de administración completo para gestionar alumnos.

#### Funcionalidades:

- **📋 Listar alumnos**: Vista de todos los alumnos registrados con búsqueda ordenada por nombre
- **✏️ Editar alumno**: Cambiar nombre o número de cuenta (con validación de duplicados)
- **🗑️ Eliminar alumno**: Eliminar alumno y todos sus registros en todas las materias (con confirmación)

#### Características de seguridad:

- ✅ Sentencias preparadas (mysqli->prepare) contra SQL injection
- ✅ Validación de entrada
- ✅ Confirmación antes de eliminar
- ✅ HTML escapado para prevenir XSS

#### Flujo de uso:

1. Desde `index2.php` → botón "👥 Gestionar Alumnos"
2. Ver lista de alumnos
3. Clic en "✏️ Editar" o "🗑️ Eliminar"
4. Llenar datos o confirmar eliminación
5. Volver a lista

---

### 2. **agregar_calificacion.php** (MEJORADO)

Ahora usa sentencias preparadas y detecta filas vacías.

#### Mejoras implementadas:

- ✅ Sentencias preparadas (mysqli->prepare)
- ✅ Detección de filas con ceros (0, 0, 0) creadas al inscribir alumno
- ✅ Si alumno es nuevo: actualiza fila vacía en lugar de duplicar
- ✅ Si alumno existe con datos: actualiza valores
- ✅ Mejor validación y mensajes de error

#### Flujo mejorado:

1. Verificar que alumno existe ✅
2. Si ya tiene registro para ese parcial:
   - Si está vacío (0,0,0) → **UPDATE** (reemplaza)
   - Si tiene datos → **UPDATE** (actualiza)
3. Si no existe → **INSERT**

**Resultado**: ❌ Se acabó el problema de filas duplicadas

---

### 3. **conexion_pdo_promedio_col.php** (MANTENIDO)

Sistema de visualización de promedios con:

- 📊 Tablas de promedios (alumnos y materias)
- 🔍 Búsqueda por nombre de alumno
- 📅 Filtros por semestre (1-6) y parcial (1-3)
- ❌ Lista de alumnos reprobados por materia
- 📈 Tabla resumen con porcentajes

---

### 4. **index2.php** (MEJORADO)

Panel principal con nuevos botones:

- "Registrar Alumno" (agregar_alumno.php)
- "Registrar Calificacion" (agregar_calificacion.php)
- **"👥 Gestionar Alumnos"** ⭐ NUEVO
- **"📊 Ver Promedios"** ⭐ NUEVO

---

## 🔄 Flujos de trabajo típicos

### ➕ Agregar alumno nuevo

1. `index2.php` → "Registrar Alumno"
2. Llenar formulario (Número de cuenta, Nombre)
3. ✅ Se crea alumno + filas vacías en todas las materias

### 📝 Agregar calificación

1. `index2.php` → "Registrar Calificacion"
2. Seleccionar materia, alumno, parcial
3. Ingresar calificaciones (Hetero, Co, Auto)
4. ✅ Se actualiza fila del alumno (ya no duplica)

### ✏️ Editar alumno

1. `index2.php` → "👥 Gestionar Alumnos"
2. Click en "✏️ Editar" en el alumno deseado
3. Cambiar nombre y/o número de cuenta
4. ✅ Se actualiza en todas las materias automáticamente

### 🗑️ Eliminar alumno

1. `index2.php` → "👥 Gestionar Alumnos"
2. Click en "🗑️ Eliminar" en el alumno deseado
3. Confirmar eliminación (⚠️ se elimina de todas las materias)
4. ✅ Alumno y sus registros se eliminan

---

## 🔐 Mejoras de seguridad

| Aspecto          | Antes                    | Ahora                    |
| ---------------- | ------------------------ | ------------------------ |
| SQL Queries      | Concatenación directa ❌ | Sentencias preparadas ✅ |
| SQL Injection    | Vulnerable ❌            | Protegido ✅             |
| Validación       | Mínima ❌                | Completa ✅              |
| Confirmación     | Sin confirmación ❌      | Con confirmación ✅      |
| Filas duplicadas | Sí ❌                    | No ✅                    |

---

## 🐛 Problemas solucionados

### Problema 1: Filas duplicadas

**Síntoma**: Al agregar alumno → agregar calificación aparecían 2 filas (una con 0,0,0 y otra con datos)

**Solución**: Detectar filas vacías en `agregar_calificacion.php` y hacer UPDATE en lugar de INSERT

### Problema 2: SQL Injection

**Síntoma**: Vulnerabilidad a inyección de SQL en todos los formularios

**Solución**: Reemplazar queries con sentencias preparadas (`mysqli->prepare()`)

---

## 📋 Checklist de pruebas recomendadas

- [ ] Agregar alumno nuevo y verificar que aparece en la lista
- [ ] Agregar calificación al alumno nuevo (sin duplicados)
- [ ] Editar nombre del alumno y verificar que se actualiza
- [ ] Editar número de cuenta y verificar que se actualiza en materias
- [ ] Intentar crear alumno con número de cuenta duplicado (debe rechazar)
- [ ] Eliminar alumno y verificar que desaparece de lista y materias
- [ ] Buscar alumno en "Ver promedios" con su nuevo nombre
- [ ] Verificar que reprobados aparecen correctamente

---

## 📝 Notas técnicas

- Todas las conexiones usan MySQLi preparadas (seguro)
- Se escapan todos los strings con `htmlspecialchars()` antes de mostrar
- Las transacciones podrían mejorarse con BEGIN/COMMIT para robustez
- Se recomienda validar colaciones en BD (utf8mb4_unicode_ci)

---

## 🎯 Próximas mejoras sugeridas

1. Agregar rol de usuario (admin/profesor/alumno)
2. Registrar auditoría de cambios (quién editó/eliminó)
3. Exportar datos a Excel/PDF
4. Importación masiva de alumnos con CSV
5. Dashboard con gráficos de desempeño
6. Notificaciones de alumnos reprobados
