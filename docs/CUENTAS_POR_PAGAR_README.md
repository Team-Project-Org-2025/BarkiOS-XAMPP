# Módulo de Cuentas por Pagar

## 📋 Descripción

El módulo de **Cuentas por Pagar** gestiona los créditos que los distribuidores/proveedores del extranjero otorgan a la empresa Garage Barki para el pago de compras de ropa realizadas.

Este módulo actúa como una **concatenación de información** de las tablas de:
- **Proveedores**: Distribuidores que otorgan crédito
- **Compras**: Transacciones de compra (cuando se implemente el módulo)

## 🏗️ Arquitectura

### Diferencia con Cuentas por Cobrar

| Aspecto | Cuentas por Cobrar | Cuentas por Pagar |
|---------|-------------------|-------------------|
| **Propósito** | Crédito que la empresa otorga a clientes VIP | Crédito que proveedores otorgan a la empresa |
| **Relacionado con** | Clientes + Ventas | Proveedores + Compras |
| **Quién debe** | Clientes VIP a Garage Barki | Garage Barki a proveedores |
| **Tipo de producto** | Ropa en consignación para clientes | Ropa comprada a distribuidores |

## 📦 Estructura del Módulo

```
BarkiOS/
├── app/
│   ├── models/
│   │   └── AccountsPayable.php          # Modelo de datos
│   ├── controllers/
│   │   └── Admin/
│   │       └── AccountsPayableController.php  # Controlador
│   └── views/
│       └── admin/
│           └── accountsPayable.php      # Vista HTML
├── public/
│   └── assets/
│       └── js/
│           └── accountsPayable.js       # Lógica JavaScript
└── database/
    ├── create_cuentas_pagar.sql         # Script creación tabla
    └── migration_add_activo_proveedores.sql  # Migración proveedores
```

## 🚀 Instalación

### Paso 1: Actualizar tabla de proveedores

Ejecuta el script de migración para agregar el campo `activo`:

```sql
-- Ejecutar en phpMyAdmin
source c:/xampp/htdocs/BarkiOS/database/migration_add_activo_proveedores.sql
```

O copia y pega el contenido del archivo en phpMyAdmin.

### Paso 2: Crear tabla cuentas_pagar

Ejecuta el script de creación de tabla:

```sql
-- Ejecutar en phpMyAdmin
source c:/xampp/htdocs/BarkiOS/database/create_cuentas_pagar.sql
```

Este script crea:
- ✅ Tabla `cuentas_pagar`
- ✅ Vista `vista_cuentas_pagar` (para reportes)
- ✅ Trigger `trg_actualizar_estado_vencido` (actualiza automáticamente)
- ✅ Procedimiento `sp_estadisticas_cuentas_pagar` (estadísticas)

### Paso 3: Verificar instalación

```sql
-- Verificar que la tabla existe
SHOW TABLES LIKE 'cuentas_pagar';

-- Ver estructura
DESCRIBE cuentas_pagar;

-- Probar procedimiento de estadísticas
CALL sp_estadisticas_cuentas_pagar();
```

### Paso 4: Configurar rutas (si es necesario)

Asegúrate de que tu archivo de rutas incluye:

```php
// En tu router o index.php
case '/admin/cuentas-pagar':
    require_once __DIR__ . '/app/controllers/Admin/AccountsPayableController.php';
    break;
```

## 💾 Estructura de la Base de Datos

### Tabla: cuentas_pagar

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT(11) AUTO_INCREMENT | ID único de la cuenta |
| `proveedor_id` | VARCHAR(12) | RIF del proveedor (FK) |
| `factura_numero` | VARCHAR(50) | Número de factura del proveedor |
| `fecha_emision` | DATE | Fecha de emisión de la factura |
| `fecha_vencimiento` | DATE | Fecha límite de pago |
| `fecha_pago` | DATETIME | Fecha real del pago (NULL si no pagado) |
| `monto_total` | DECIMAL(10,2) | Monto total a pagar |
| `estado` | ENUM | Pendiente, Pagada, Vencida, Parcial |
| `activo` | TINYINT(1) | 1=activo, 0=eliminado |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última actualización |

### Estados de Cuenta

- **Pendiente**: Cuenta creada, aún no vencida ni pagada
- **Pagada**: Pago completado en su totalidad
- **Vencida**: Fecha de vencimiento pasada sin pago
- **Parcial**: Pago parcial realizado

## 🎯 Funcionalidades

### 1. Agregar Cuenta por Pagar

**Endpoint**: `POST /AccountsPayableController.php?action=add_ajax`

**Campos requeridos**:
- Número de factura (único)
- Proveedor (búsqueda dinámica)
- Fecha de emisión
- Fecha de vencimiento
- Monto total

**Validaciones**:
- ✅ Factura única por proveedor
- ✅ Proveedor existente en la BD
- ✅ Monto mayor a cero
- ✅ Fecha de vencimiento válida

### 2. Listar Cuentas

**Endpoint**: `GET /AccountsPayableController.php?action=get_accounts`

**Retorna**: Array JSON con todas las cuentas activas, ordenadas por fecha de vencimiento.

### 3. Búsqueda de Proveedores

**Endpoint**: `GET /AccountsPayableController.php?action=search_supplier&search=texto`

**Funcionalidad**: Búsqueda en tiempo real por:
- Nombre de empresa
- Nombre de contacto
- RIF

### 4. Registrar Pago

**Endpoint**: `POST /AccountsPayableController.php?action=register_payment`

**Parámetros**:
- `id`: ID de la cuenta
- `monto_pagado`: Cantidad pagada

**Lógica**:
- Si `monto_pagado >= monto_total` → Estado = "Pagada"
- Si `monto_pagado < monto_total` → Estado = "Parcial"

### 5. Eliminar (Lógica)

**Endpoint**: `POST /AccountsPayableController.php?action=delete_ajax`

Marca la cuenta como inactiva (`activo = 0`) sin eliminarla físicamente.

## 📊 Reportes y Estadísticas

### Vista de Reportes

```sql
-- Consultar todas las cuentas con información enriquecida
SELECT * FROM vista_cuentas_pagar;
```

Incluye:
- Datos del proveedor
- Días para vencimiento
- Clasificación de urgencia (Al día, Por vencer, Vencida)

### Estadísticas Generales

```sql
-- Obtener estadísticas completas
CALL sp_estadisticas_cuentas_pagar();
```

Retorna:
- Total de cuentas
- Cuentas pendientes/pagadas/vencidas
- Monto total pendiente
- Monto total pagado

## 🔄 Integración Futura con Módulo de Compras

Cuando implementes el módulo de **Compras**, podrás:

1. **Vincular automáticamente**: Al registrar una compra, crear automáticamente una cuenta por pagar si es a crédito
2. **Relacionar detalles**: Enlazar qué productos específicos se compraron en cada cuenta
3. **Tracking completo**: Ver historial de compras por proveedor

### Ejemplo de integración:

```php
// En el controlador de Compras
public function registrarCompra($datos) {
    // 1. Registrar la compra
    $compraId = $this->comprasModel->add($datos);
    
    // 2. Si es a crédito, crear cuenta por pagar
    if ($datos['forma_pago'] === 'credito') {
        $cuentaPorPagar = [
            'proveedor_id' => $datos['proveedor_id'],
            'factura_numero' => $datos['factura_numero'],
            'fecha_emision' => date('Y-m-d'),
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'monto_total' => $datos['total'],
            'estado' => 'Pendiente'
        ];
        $this->accountsPayableModel->add($cuentaPorPagar);
    }
}
```

## 🎨 Frontend (JavaScript)

El archivo `accountsPayable.js` incluye:

- ✅ Búsqueda en tiempo real de proveedores
- ✅ Validación de formularios
- ✅ Carga dinámica de tabla
- ✅ Alertas con SweetAlert2
- ✅ Manejo de estados de botones

### Ejemplo de uso:

```javascript
// El sistema automáticamente:
// 1. Carga las cuentas al cargar la página
// 2. Busca proveedores mientras escribes (min 2 caracteres)
// 3. Valida el formulario antes de enviar
// 4. Actualiza la tabla después de agregar
```

## 🔐 Seguridad

- ✅ Validación de entrada en servidor y cliente
- ✅ Uso de prepared statements (prevención SQL injection)
- ✅ Escape de HTML en JavaScript (prevención XSS)
- ✅ Headers AJAX verificados
- ✅ Eliminación lógica (no física)

## 🐛 Troubleshooting

### Error: "Proveedor no existe"

**Solución**: Verifica que la tabla `proveedores` tenga el campo `activo` y que el proveedor esté activo (`activo = 1`).

```sql
-- Verificar proveedores activos
SELECT * FROM proveedores WHERE activo = 1;
```

### Error: "Factura duplicada"

**Solución**: El número de factura debe ser único. Verifica:

```sql
-- Ver si la factura ya existe
SELECT * FROM cuentas_pagar WHERE factura_numero = 'FACT-123' AND activo = 1;
```

### Error en búsqueda de proveedores

**Solución**: Verifica que el endpoint esté correcto y que tengas proveedores registrados:

```sql
-- Insertar proveedor de prueba
INSERT INTO proveedores (id, nombre_empresa, nombre_contacto, direccion, tipo_rif, activo)
VALUES ('J123456789', 'Distribuidora Internacional', 'Juan Pérez', 'Calle Principal', 'J', 1);
```

## 📝 Notas Importantes

1. **Actualización automática de estados**: El trigger `trg_actualizar_estado_vencido` cambia automáticamente el estado de "Pendiente" a "Vencida" cuando la fecha de vencimiento pasa.

2. **Índices optimizados**: La tabla tiene índices en:
   - `proveedor_id` (búsquedas por proveedor)
   - `estado` (filtros por estado)
   - `fecha_vencimiento` (ordenamiento y alertas)

3. **Soft Delete**: Las cuentas eliminadas no se borran físicamente, solo se marca `activo = 0`.

## 🚧 Próximas Mejoras

- [ ] Módulo de Compras completo
- [ ] Reportes PDF de cuentas vencidas
- [ ] Notificaciones automáticas de vencimiento
- [ ] Dashboard con gráficas de cuentas
- [ ] Exportación a Excel
- [ ] Historial de pagos parciales
- [ ] Integración con sistema contable

## 📞 Soporte

Para cualquier duda sobre este módulo, consulta:
- Modelo: `app/models/AccountsPayable.php`
- Controlador: `app/controllers/Admin/AccountsPayableController.php`
- Scripts SQL: `database/create_cuentas_pagar.sql`

---

**Versión**: 1.0.0  
**Fecha**: Octubre 2025  
**Autor**: Equipo Garage Barki
