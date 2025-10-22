# Módulo de Compras - BarkiOS

## 📋 Descripción

El módulo de **Compras** gestiona todas las adquisiciones de mercancía que Garage Barki realiza a proveedores/distribuidores internacionales. Este módulo permite registrar, rastrear y controlar el inventario de productos comprados.

## 🎯 Características Principales

### Información Registrada por Compra

- **N° de Factura**: Número de factura del proveedor (único)
- **Proveedor**: Distribuidor/comercio internacional
- **Productos**: Múltiples productos por compra con:
  - Nombre del producto
  - Descripción
  - Cantidad
  - Precio unitario
  - Subtotal automático
- **Tracking**: Número de guía/seguimiento del envío
- **Fecha de Compra**: Automática al registrar
- **Tipo**: Contado o Crédito
- **Método de Pago**: Transferencia, Tarjeta, Cheque, etc.
- **Monto Total**: Calculado automáticamente
- **Estado**: En espera, En tránsito, Recibida, Inventario, Cancelada
- **Observaciones**: Notas adicionales

## 📦 Estructura de Archivos

```
BarkiOS/
├── app/
│   ├── models/
│   │   └── Purchase.php                      # Modelo de Compras
│   ├── controllers/
│   │   └── Admin/
│   │       └── PurchaseController.php        # Controlador
│   └── views/
│       └── admin/
│           └── purchase-admin.php            # Vista HTML
├── public/
│   └── assets/
│       └── js/
│           └── purchase-admin.js             # Lógica JavaScript
├── database/
│   └── create_compras.sql                    # Script SQL
└── docs/
    └── MODULO_COMPRAS_README.md              # Esta documentación
```

## 🗄️ Base de Datos

### Tabla: `compras`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `compra_id` | INT(11) AUTO_INCREMENT | ID único |
| `proveedor_id` | VARCHAR(12) | RIF del proveedor (FK) |
| `factura_numero` | VARCHAR(50) | Número de factura (único) |
| `fecha_compra` | DATETIME | Fecha/hora de registro |
| `tracking` | VARCHAR(100) | Número de seguimiento |
| `tipo_compra` | ENUM | contado, credito |
| `metodo_pago` | ENUM | efectivo, transferencia, tarjeta_credito, etc. |
| `subtotal` | DECIMAL(12,2) | Suma de productos |
| `descuento` | DECIMAL(12,2) | Descuento aplicado |
| `monto_total` | DECIMAL(12,2) | Total a pagar |
| `estado_compra` | ENUM | en_espera, en_transito, recibida, inventario, cancelada |
| `observaciones` | TEXT | Notas adicionales |
| `activo` | TINYINT(1) | 1=activo, 0=cancelado |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última actualización |

### Tabla: `detalle_compra`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `detalle_id` | INT(11) AUTO_INCREMENT | ID único |
| `compra_id` | INT(11) | FK a compras |
| `producto_nombre` | VARCHAR(200) | Nombre del producto |
| `producto_descripcion` | TEXT | Descripción |
| `cantidad` | INT(11) | Cantidad comprada |
| `precio_unitario` | DECIMAL(10,2) | Precio por unidad |
| `subtotal` | DECIMAL(12,2) | Cantidad × Precio |

### Estados de Compra

| Estado | Descripción | Badge Color |
|--------|-------------|-------------|
| **En Espera** | Compra registrada, esperando confirmación | Gris (secondary) |
| **En Tránsito** | Mercancía en camino | Azul (info) |
| **Recibida** | Mercancía recibida, pendiente de inventariar | Amarillo (warning) |
| **Inventario** | Productos en inventario del sistema | Verde (success) |
| **Cancelada** | Compra anulada | Rojo (danger) |

## 🚀 Instalación

### Paso 1: Crear tablas en la base de datos

```sql
-- En phpMyAdmin, ejecutar:
SOURCE c:/xampp/htdocs/BarkiOS/database/create_compras.sql;
```

O copiar y pegar el contenido del archivo en la pestaña SQL.

### Paso 2: Verificar instalación

```sql
-- Ver tablas creadas
SHOW TABLES LIKE '%compra%';

-- Ver estructura
DESCRIBE compras;
DESCRIBE detalle_compra;

-- Probar estadísticas
CALL sp_estadisticas_compras();

-- Ver vista
SELECT * FROM vista_compras;
```

### Paso 3: Configurar rutas

Agregar en tu archivo de rutas (router/index.php):

```php
case '/admin/compras':
case '/admin/purchases':
    require_once __DIR__ . '/app/controllers/Admin/PurchaseController.php';
    break;
```

### Paso 4: Probar el módulo

```
http://localhost/BarkiOS/admin/compras
```

## 🎨 Funcionalidades del Frontend

### 1. Dashboard de Estadísticas

Muestra en tiempo real:
- Total de compras registradas
- Compras en tránsito
- Compras recibidas
- Monto total invertido

### 2. Búsqueda de Proveedores

- Búsqueda en tiempo real (mínimo 2 caracteres)
- Busca por: Nombre de empresa, Contacto, RIF
- Resultados dropdown con información completa

### 3. Gestión de Productos

- Agregar múltiples productos por compra
- Cálculo automático de subtotales
- Validación de cantidad y precio
- Eliminación dinámica de productos

### 4. Resumen Automático

- Subtotal calculado automáticamente
- Aplicación de descuentos
- Total en tiempo real

### 5. Tabla Interactiva

- Búsqueda por factura, proveedor o tracking
- Estados visuales con badges de colores
- Acciones rápidas: Ver, Actualizar Estado, Cancelar

## 📡 Endpoints AJAX

### GET Endpoints

#### `GET /PurchaseController.php?action=get_purchases`
Obtiene todas las compras activas.

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "compra_id": 1,
      "factura_numero": "FACT-USA-001",
      "proveedor": "Fashion Imports",
      "total_productos": 3,
      "cantidad_total_items": 50,
      "monto_total": "5000.00",
      "tracking": "TRACK123",
      "fecha_compra": "2025-10-22 15:30:00",
      "estado_compra": "en_transito"
    }
  ]
}
```

#### `GET /PurchaseController.php?action=search_supplier&search=texto`
Busca proveedores.

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": "J123456789",
      "nombre_empresa": "Fashion Imports USA",
      "nombre_contacto": "John Smith",
      "direccion": "Miami, FL"
    }
  ]
}
```

#### `GET /PurchaseController.php?action=get_by_id&id=1`
Obtiene detalles completos de una compra.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "compra_id": 1,
    "factura_numero": "FACT-USA-001",
    "proveedor_id": "J123456789",
    "nombre_empresa": "Fashion Imports",
    "monto_total": "5000.00",
    "productos": [
      {
        "producto_nombre": "Jeans Levi's 501",
        "cantidad": 10,
        "precio_unitario": "45.00",
        "subtotal": "450.00"
      }
    ]
  }
}
```

### POST Endpoints

#### `POST /PurchaseController.php?action=add_ajax`
Agrega una nueva compra.

**Parámetros:**
```
proveedor_id: J123456789
factura_numero: FACT-USA-001
tracking: TRACK123456
tipo_compra: credito
metodo_pago: transferencia
descuento: 100.00
estado_compra: en_espera
observaciones: Primera compra del mes
productos[0][producto_nombre]: Jeans
productos[0][cantidad]: 10
productos[0][precio_unitario]: 45.00
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Compra registrada correctamente",
  "compra_id": 1
}
```

#### `POST /PurchaseController.php?action=update_estado`
Actualiza solo el estado de una compra.

**Parámetros:**
```
compra_id: 1
estado: en_transito
```

#### `POST /PurchaseController.php?action=delete_ajax`
Cancela una compra (eliminación lógica).

**Parámetros:**
```
compra_id: 1
```

## 🔄 Integración con Cuentas por Pagar

Cuando se registra una compra **a crédito**, automáticamente se crea una cuenta por pagar en el módulo correspondiente (si está instalado).

**Flujo automático:**
```php
1. Registrar compra con tipo_compra = 'credito'
2. Sistema crea entrada en tabla 'cuentas_pagar'
   - proveedor_id: del proveedor de la compra
   - factura_numero: mismo número de factura
   - monto_total: monto total de la compra
   - fecha_vencimiento: 30 días después
   - estado: 'Pendiente'
```

## 📊 Reportes Disponibles

### Vista `vista_compras`

```sql
SELECT * FROM vista_compras;
```

Incluye información enriquecida:
- Datos del proveedor
- Total de productos
- Cantidad total de items
- Estado descriptivo

### Procedimiento `sp_estadisticas_compras()`

```sql
CALL sp_estadisticas_compras();
```

Retorna:
- Total de compras
- Compras por estado
- Monto total en crédito
- Monto total de contado
- Promedio de compra

### Procedimiento `sp_detalle_compra(compra_id)`

```sql
CALL sp_detalle_compra(1);
```

Retorna dos resultsets:
1. Información de la compra con datos del proveedor
2. Lista de productos de la compra

## 🔐 Validaciones Implementadas

### Backend (PHP)

- ✅ Proveedor debe existir y estar activo
- ✅ Número de factura único
- ✅ Al menos un producto requerido
- ✅ Cantidad mayor a cero
- ✅ Precio mayor a cero
- ✅ Monto total mayor a cero
- ✅ Estado válido

### Frontend (JavaScript)

- ✅ Proveedor seleccionado obligatorio
- ✅ Mínimo un producto agregado
- ✅ Validación de campos numéricos
- ✅ Cálculos automáticos de subtotales

## 🎨 Estilos y UX

### Colores del Módulo

- **Color principal**: Púrpura (#667eea)
- **Gradiente header**: #667eea → #764ba2
- **Estados**: Según criticidad

### Características UX

- ✅ Búsqueda en tiempo real con debounce (300ms)
- ✅ Spinners de carga
- ✅ Alertas toast con SweetAlert2
- ✅ Confirmación de eliminación
- ✅ Formato de moneda automático
- ✅ Hover effects en cards
- ✅ Responsive design

## 🐛 Troubleshooting

### Error: "Proveedor no existe"

**Solución**: Verificar que el proveedor esté activo:

```sql
SELECT * FROM proveedores WHERE activo = 1;
```

### Error: "Factura duplicada"

**Solución**: Verificar facturas existentes:

```sql
SELECT * FROM compras WHERE factura_numero = 'FACT-123' AND activo = 1;
```

### No aparecen proveedores en la búsqueda

**Solución**: Insertar proveedor de prueba:

```sql
INSERT INTO proveedores (id, nombre_empresa, nombre_contacto, direccion, tipo_rif, activo)
VALUES ('J123456789', 'Fashion Imports USA', 'John Smith', 'Miami, FL', 'J', 1);
```

### Error al crear cuenta por pagar

**Solución**: Si la tabla `cuentas_pagar` no existe, el sistema lo ignora sin afectar el registro de la compra. Instala primero el módulo de Cuentas por Pagar.

## 🔄 Workflow Recomendado

### 1. Registrar Compra
- Estado inicial: **En Espera**
- Registrar proveedor, productos, tracking

### 2. Confirmación de Envío
- Actualizar a: **En Tránsito**
- Agregar/actualizar número de tracking

### 3. Llegada de Mercancía
- Actualizar a: **Recibida**
- Verificar productos físicamente

### 4. Ingreso a Inventario
- Actualizar a: **Inventario**
- Registrar productos en sistema de inventario
- Mercancía disponible para venta

## 📝 Próximas Mejoras

- [ ] Integración completa con módulo de Inventario
- [ ] Cargar productos desde catálogo
- [ ] Importación masiva de compras (Excel/CSV)
- [ ] Reportes en PDF
- [ ] Gráficas de compras por período
- [ ] Alertas de productos en tránsito
- [ ] Comparación de precios entre proveedores
- [ ] Historial de cambios de estado

## 📞 Soporte

**Archivos principales:**
- Modelo: `app/models/Purchase.php`
- Controlador: `app/controllers/Admin/PurchaseController.php`
- Vista: `app/views/admin/purchase-admin.php`
- JS: `public/assets/js/purchase-admin.js`
- SQL: `database/create_compras.sql`

---

**Versión**: 1.0.0  
**Fecha**: Octubre 2025  
**Autor**: Equipo Garage Barki
