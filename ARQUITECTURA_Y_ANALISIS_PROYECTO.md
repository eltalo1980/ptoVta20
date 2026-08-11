# ANÁLISIS INTEGRAL DEL PROYECTO: DONDE EL MARCO (POS)

> **Documento de Referencia Técnica y Arquitectura**  
> *Generado para consulta y mantenimiento del sistema punto de venta.*

---

## 1. RESUMEN EJECUTIVO Y STACK TECNOLÓGICO

**Donde El Marco** es un sistema Web de **Punto de Venta (POS) y Gestión de Inventarios** multisede (multi-tenant por `id_local`), diseñado para operaciones de venta rápida en locales comerciales con soporte para:
- Lectores de código de barras físico.
- Balanzas y productos pesables (códigos de barra con prefijo `210`).
- Precios mayoristas automáticos por volumen.
- Sistema de ventas aparcadas / "Caja Pagadora" (separación entre vendedor y cajero central).
- Devoluciones cruzadas con ventas.
- Cierres diarios de caja con reportes analíticos y exportación a CSV.
- Consulta de precios en modo kiosco/pop-up.
- Sincronización asíncrona hacia base de datos maestra/remota.

### Stack Tecnológico
- **Framework Backend:** Laravel (PHP 8.x).
- **Base de Datos:** MySQL / MariaDB (utiliza Query Builder `DB::table()`, SQL raw y locks consultables con `GET_LOCK()`).
- **Frontend:** Blade Templates, Vanilla JavaScript, jQuery, Bootstrap 3.3.6 (con estilos personalizados modernos tipo dashboard "Premium"), FontAwesome.
- **Autenticación:** Laravel Auth estándar con campos extendidos en `tbl_local_marco_usuarios`.

---

## 2. ARQUITECTURA GENERAL Y PATRÓN MULTI-TENANT

### 2.1 Aislamiento de Datos por `id_local`
Todas las consultas del sistema filtran de manera estricta por `Auth::user()->id_local`. Esto permite que una misma base de datos o estructura sirva a múltiples sucursales con datos aislados.

### 2.2 Patrón de Parametría Centralizada (`ParametriaController`)
En lugar de depender exclusivamente de modelos Eloquent tradicionales o nombres de tablas estáticos en el código, el sistema utiliza `ParametriaController` como proveedor central de nombres de tablas y variables del local:
- `fncTraeTablaStock()` $\rightarrow$ `'tbl_local_marco_stock'`
- `fncTraeTablaVentas()` $\rightarrow$ `'tbl_local_marco_ventas'`
- `fncTraeTablaVentasTotales()` $\rightarrow$ `'tbl_local_marco_ventas_total'`
- `fncTraeTablaVentasDetalleTmp()` $\rightarrow$ `'tbl_local_marco_ventas_detalle_tmp'`
- `fncTraeTablaCaja()` $\rightarrow$ `'tbl_local_marco_caja'`
- `fncTraeTablaFacturas()` $\rightarrow$ `'tbl_local_marco_facturas'`
- `fncTraeTablaStockPack()` $\rightarrow$ `'tbl_local_marco_stock_pack'`
- `fncTraeTablaStockPendiente()` $\rightarrow$ `'tbl_local_marco_stock_pendiente'`
- `fncTraeTablaDevolucionDetalleTmp()` $\rightarrow$ `'tbl_local_marco_devolucion_detalle_tmp'`
- `fncTraeTablaConsultaPrecio()` $\rightarrow$ `'tbl_local_marco_consulta_precio'`
- `fncTraeTablaConfiguracion()` $\rightarrow$ `'tbl_local_marco_cofiguracion'`
- `fncTraeConfiguracion($clave)` $\rightarrow$ Consulta dinámica de opciones (`valorIVA`, `cajaPagadora`, `codBarraEfectivo`, `codBarraTarjeta`, `divisorPeso`, `consultaPrecio`, etc.).

---

## 3. MATRIZ COMPLETA DE RUTAS (ROUTERS)

### 3.1 Rutas Web (`routes/web.php`)

| Método HTTP | URI | Nombre de Ruta | Controlador y Acción | Descripción / Propósito |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | - | Closure $\rightarrow$ redirect `login` | Redirección inicial |
| `GET`/`POST` | `/login`, `/logout`, `/register`, etc. | `Auth::routes()` | `Auth\*` | Autenticación y control de sesión |
| `GET` | `/home` | `home` | `VentaController@index` | Pantalla principal tras login |
| `GET` | `/logout` | - | `Auth\LoginController@logout` | Cierre de sesión explícito |
| `RESOURCE` | `/venta` | `venta.*` | `VentaController` | CRUD y flujo principal de venta POS |
| `POST` | `/venta/park` | `venta.park` | `CajaController@park` | Envía venta temporal a estado pendiente en Caja |
| `RESOURCE` | `/Caja` | `Caja.*` | `CajaController` | Gestión y cobro de ventas aparcadas por cajero |
| `POST` | `/ConsultaPrecio/clear` | `ConsultaPrecio.clear` | `ConsultaPrecioController@clear` | Limpieza AJAX de consultas de precio |
| `RESOURCE` | `/ConsultaPrecio` | `ConsultaPrecio.*` | `ConsultaPrecioController` | Kiosco de verificación de precios |
| `RESOURCE` | `/pago` | `pago.*` | `PagoController` | Procesamiento del cobro, vuelto y persistencia |
| `RESOURCE` | `/stock` | `stock.*` | `StockController` | Maestro de productos e inventario |
| `RESOURCE` | `/StockPack` | `StockPack.*` | `StockPackController` | Creación y listado de packs de productos |
| `RESOURCE` | `/StockPackDetalle` | `StockPackDetalle.*` | `StockPackDetalleController` | Asignación de productos dentro de un pack |
| `RESOURCE` | `/StockPendiente` | `StockPendiente.*` | `StockPendienteController` | Gestión de compras/reposiciones pendientes |
| `DELETE` | `/stock-pendiente/{id}` | `stock-pendiente.destroy` | `StockPendienteController@destroyPendiente` | Elimina producto individual de reposición |
| `DELETE` | `/StockPendiente/destroy-all` | `StockPendiente.destroyAll` | `StockPendienteController@destroyAll` | Vaciado masivo de lista de reposición |
| `RESOURCE` | `/Configuracion` | `Configuracion.*` | `ConfiguracionController` | Parámetros del sistema y variables de negocio |
| `RESOURCE` | `/Locales` | `Locales.*` | `LocalController` | Administración de locales y suscripciones |
| `RESOURCE` | `/ResumenVenta` | `ResumenVenta.*` | `ResumenVentaController` | Histórico y detalle de tickets emitidos |
| `RESOURCE` | `/Factura` | `Factura.*` | `FacturaController` | Registro de pagos y facturas a proveedores |
| `GET` | `/CierreDiaExport/csv` | `CierreDia.exportCSV` | `CierreDiaController@exportCSV` | Exporta cierre de ventas a CSV/Excel |
| `RESOURCE` | `/CierreDia` | `CierreDia.*` | `CierreDiaController` | Reporte cuadratura de caja y ventas del día |
| `RESOURCE` | `/Devolucion` | `Devolucion.*` | `DevolucionVentaController` | Módulo de devolución de productos al stock |
| `RESOURCE` | `/VentaAnalisis` | `VentaAnalisis.*` | `VentaAnalisisController` | Estadísticas por hora, día y ranking de productos |

### 3.2 Rutas API (`routes/api.php`)

| Método HTTP | URI | Controlador / Handler | Descripción |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/user` | `auth:sanctum` | Obtener usuario autenticado vía token |
| `POST` | `/api/sincronizar-db` | `Api\SyncDBController@receive` | Endpoint receptor de sincronización de tablas maestras |

---

## 4. CATÁLOGO DETALLADO DE CONTROLADORES Y MÉTODOS

### 4.1 `VentaController`
Controlador central del punto de venta en tiempo real.
- **`index(Request $request)`:**
  - Carga la vista principal `Ventas.index`.
  - Procesa parámetros de sesión `folio_caja` y `vendedor_id_original` si la venta fue abierta por un cajero.
  - Obtiene el listado de productos temporales desde `tbl_local_marco_ventas_detalle_tmp`.
  - Agrupa productos por código, descripción y precio unitario calculando subtotales.
  - Cruza con `DevolucionVentaController::fncTraertotalDevolucion()` para calcular el monto final (`$TotaFinal`).
  - Obtiene parámetros de configuración (`codBarraEfectivo`, `codBarraTarjeta`, `cajaPagadora`, `consultaPrecio`).
- **`store(Request $request)`:**
  - Procesa la lectura de códigos de barra ingresados manual o por pistola.
  - **Detección de códigos de pago directo:** Si el código coincide con `codBarraEfectivo` o `codBarraTarjeta`, actualiza el `tipo_pago`. Si `cajaPagadora == 1` y `nivel < 10`, realiza parking automático de la venta.
  - **Detección de productos pesables (Balanza - Prefijo `210`):** Extrae código de producto (primeros 7 dígitos) y calcula peso/cantidad a partir de los dígitos 8 a 13 divididos por `(10000 * divisorPeso)`.
  - **Manejo de precios por mayor:** Si el producto tiene configurado `cantidad_venta_mayor > 0` y la cantidad acumulada lo supera, aplica automáticamente `precio_venta_mayor`.
  - **Acciones `addProducto` y `delProducto`:** Incrementa o decrementa unidades en la tabla temporal.
  - Redirige a `venta.index` con mensajes flash de retroalimentación.
- **`destroy(Request $request, $id)`:** Elimina un producto de la venta temporal actual.
- **`show($id)`:** Variante alternativa para eliminar un producto de la venta temporal.
- **`fncTraerProductosCodigo($codigo)`:** Busca en `tbl_local_marco_stock` (exacto y `LIKE`) o en `tbl_local_marco_stock_pack`.
- **`fncBuscarProductoVentasTMP($codigo)` / `fncTraeProductoVentasTMP($codigo)`:** Consultan la cantidad y datos del producto en la sesión temporal activa.
- **`fncVentasRespaldasVentasNoRealizadas()`:** Respalda la venta temporal en `tbl_local_marco_ventas_detalle_tmp_borrada` al cambiar a devolución.

---

### 4.2 `PagoController`
Controla el cierre de la transacción, cálculo de vuelto, inserción final y consistencia transaccional.
- **`store(Request $request)`:**
  - Actualiza la `forma_pago` seleccionada en la venta temporal.
  - Si hay devoluciones activas, procesa el cálculo neto.
  - Retorna la vista `Pagos.index` mostrando el resumen de la compra, monto a pagar y botones de cobro/vuelto.
- **`update(Request $request)`:**
  - Valida si existen registros temporales antes de procesar para evitar dobles cobros.
  - Ejecuta `fncGuardaVenta()` pasando el vendedor original, local, monto total, sencillo, vuelto y forma de pago.
  - Si el usuario es cajero (`nivel >= 10`), redirige a `Caja.index`; si es vendedor regular, redirige a `venta.index`.
- **`show($Codigo)`:** Elimina un producto específico desde la pantalla de pagos y recalcula totales.
- **`fncGuardaVenta($v_idLocal, $v_idUsuario, $v_MontoSencillo, $v_total_venta, $v_forma_pago, $v_vuleto, $v_folio = null)`:**
  - **Mecanismo de Concurrencia:** Utiliza MySQL Advisory Lock (`GET_LOCK("guarda_venta_lock_{local}_{usuario}", 10)`).
  - Inicia transacción `DB::beginTransaction()`.
  - Bloquea filas temporales con `lockForUpdate()`.
  - Inserta cabecera en `tbl_local_marco_ventas_total` (con captura de error SQL 23000 de duplicados).
  - Inserta ítems en `tbl_local_marco_ventas`.
  - Descuenta existencias en `tbl_local_marco_stock` (`cantidad = cantidad - V_CANTIDAD`).
  - Limpia la venta temporal de `tbl_local_marco_ventas_detalle_tmp`.
  - Actualiza el monto acumulado del día en `tbl_local_marco_caja` para ventas en efectivo (`forma_pago == 2`).
  - Registra trazas en `tbl_local_marco_log` marcadas con `actualizacion_estado = 'pendiente'` para sincronización.
  - Confirma con `DB::commit()` y libera el bloqueo con `RELEASE_LOCK()`.

---

### 4.3 `CajaController`
Gestión de ventas aparcadas en locales con arquitectura Vendedor-Caja Central.
- **`index()`:**
  - Requiere `nivel >= 10`.
  - Agrupa y lista las ventas en `estado_caja = 1` por `folio_caja`, vendedor, ítems, total y fecha.
  - Retorna la vista `Caja.index`.
- **`show($folio)`:**
  - Carga el folio seleccionado en la sesión (`folio_caja`, `vendedor_id_original`).
  - Redirige a `venta.index` permitiendo al cajero revisar la venta, agregar/quitar ítems o proceder al cobro.
- **`park(Request $request)`:**
  - Genera un folio único: `'F' . $id_usuario . '-' . time()`.
  - Actualiza todos los ítems en `estado_caja = 0` del vendedor actual a `estado_caja = 1` y les asigna el folio.
  - Permite al vendedor liberar el terminal para atender al siguiente cliente.

---

### 4.4 `CierreDiaController`
Control de arqueo de caja, análisis de venta diaria y exportación contable.
- **`index(Request $request)`:**
  - Resuelve la fecha consultada (`Ymd`) o toma el día actual con Carbon.
  - Ejecuta los reportes de soporte:
    - `fncVentasPorMedio($fecha)`: Tarjeta (1), Efectivo (2), Transferencia (3), Venta Interna (4) con desglose de Neto, IVA y Total.
    - `fncVentasBorradas($fecha)`: Auditoría de ítems eliminados/cancelados.
    - `fncVentasPorEmpresa($fecha)` / `fncVentasPorEmpresaTotal($fecha)`: Agrupación por proveedor/marca.
    - `fncVentasPorProducto($fecha)`: Ventas por unidades.
    - `fncVentasPorProductoPeso($fecha)`: Ventas por peso/kilos.
    - `fncPagoPorEmpresa($fecha)`: Pagos de facturas a proveedores del día.
    - `fncValorStockTotal()`: Valorización del inventario activo y margen de ganancia proyectado.
  - Retorna `InformeVentas.index`.
- **`exportCSV(Request $request)`:**
  - Genera un archivo CSV en streaming (`informe_ventas_{fecha}.csv`) con codificación UTF-8 + BOM para compatibilidad directa con Excel.

---

### 4.5 `StockController`
Gestión del catálogo de productos e inventario.
- **`index(Request $request)`:**
  - Permite buscar productos por código, descripción o empresa.
  - Si el código no existe y tiene más de 3 dígitos, redirige automáticamente a `Stock.create` precargando datos.
  - Si encuentra coincidencia exacta, redirige a `Stock.edit`.
  - Retorna `Stock.index` con listado de stock y alertas de quiebre (`cantidad <= cantidad_minima`).
- **`store(Request $request)`:**
  - Inserta nuevo producto en `tbl_local_marco_stock` con precios (`precio_neto`, `precio_costo`, `precio_venta`), cantidad mínima y flag `venta_por_unidad`.
  - Inserta en `tbl_master_productos` si el código es mayor o igual a 10 dígitos.
  - Registra auditoría en `tbl_local_marco_log`.
- **`edit($idProducto)`:** Formulario de edición con cálculo automático de IVA y márgenes de ganancia.
- **`update(Request $request, $id)`:** Actualiza datos, precios, precios mayoristas y estado activo.
- **`destroy(Request $request, $id)`:** Desactiva lógicamente el producto (`activo = 0`).
- **`show(Request $request, $id)`:** Soporta actualización rápida de stock vía URI con formato estructurado `parametros[1]=idProducto`, `parametros[2]='cambiastock'`, `parametros[3]=cantidad`.

---

### 4.6 `StockPackController` y `StockPackDetalleController`
Módulo de packs y combos promocionales.
- **`StockPackController`:** Administra el producto cabecera del pack (`codigo = 0` en `tbl_local_marco_stock_pack`).
- **`StockPackDetalleController`:**
  - Asocia productos individuales al combo (`codigo != 0`).
  - **`show()` / `index()`:** Agrega o incrementa cantidades de productos dentro del pack.
  - **`destroy()`:** Elimina un producto integrante del pack.

---

### 4.7 `StockPendienteController`
Módulo de lista de compras y reposición de mercadería.
- **`index(Request $request)`:** Lista productos marcados para reposición, con filtros por código/descripción y empresa proveedora.
- **`show(Request $request, $id)`:** Agrega un producto a la lista de pendientes mediante acción `addCantidad` enviada en la URI (`|addCantidad|idProducto|cantidad|codigo`).
- **`destroyPendiente($id)`:** Elimina un producto puntual de la lista de pendientes.
- **`destroyAll($empresa)`:** Elimina de forma masiva todos los pendientes del local o de una empresa seleccionada, registrando log de auditoría.

---

### 4.8 `DevolucionVentaController`
Gestión de devoluciones de productos.
- **`index()`:** Inicializa la sesión de devolución, limpiando temporales y respaldando ventas no realizadas.
- **`store(Request $request)`:** Agrega ítems a devolver en `tbl_local_marco_devolucion_detalle_tmp`, soporta productos por peso y recalcula el monto a favor del cliente.
- **`fncTraertotalDevolucion()`:** Devuelve la suma formateada de los productos devueltos en la sesión actual.

---

### 4.9 `ConsultaPrecioController`
Verificador de precios para terminales de autoservicio o consultas del cajero.
- **`index()`:** Lista los productos escaneados recientemente en la tabla `tbl_local_marco_consulta_precio` con el tiempo de expiración configurado (`tiempoVerConsultaPrecio`).
- **`store(Request $request)`:** Busca el producto mediante `VentaController::fncTraerProductosCodigo()` e inserta el resultado para visualización.
- **`destroy($id)`:** Elimina una o todas (`id='all'`) las consultas del usuario.
- **`clear()`:** Endpoint AJAX para limpiar el historial de consulta de precios sin recargar la página.

---

### 4.10 `ConfiguracionController`
Parámetros del sistema según nivel de privilegio.
- **`index()`:** Lista parámetros visibles para el nivel del usuario (`Auth::user()->nivel`).
- **`create()`:** Solo para superusuarios (`nivel >= 100`) para registrar nuevas variables.
- **`store(Request $request)`:** Si `accion == "crearVariable"`, crea la variable; de lo contrario, realiza un update masivo de las variables existentes del local.

---

### 4.11 `LocalController`
Administración global de sucursales (Nivel $\ge 100$).
- **`index()` / `edit()` / `update()`:** Modifica estado activo, fecha de expiración y valor del plan de los locales en `tbl_locales` y actualiza la vigencia en cascada a `tbl_local_marco_usuarios`.

---

### 4.12 `ResumenVentaController`
Histórico de tickets y boletas emitidas.
- **`index(Request $request)` / `edit(Request $request, $idVenta)`:** Permite filtrar ventas por fecha (`fecha_venta`) y visualizar el desglose detallado de productos vendidos por ticket (`id_ventas_total`).

---

### 4.13 `VentaAnalisisController`
Dashboard estadístico para toma de decisiones.
- **`fncTotalVentasPorDia($fini, $ffin)`:** Evolución de ventas en rango de fechas.
- **`fncHoraMayorVentasPorDia($fini, $ffin)`:** Horas peak de venta del negocio.
- **`fncRankingProductoMasVendido($fini, $ffin)`:** Top 20 de productos con mayor volumen y recaudación.

---

### 4.14 `Api\SyncDBController` & Comandos de Sincronización
- **`Api\SyncDBController@receive`:** Recibe payloads con `table`, `pk_name`, `pk_value` y `data`, valida el header `X-Sync-Token` e inyecta los datos con `updateOrInsert()` marcando `actualizacion_estado = 'sincronizado'`.
- **`app/Console/Commands/SyncDirectCommand.php` (`sync:run`):** Recorre las tablas del sistema buscando registros con `actualizacion_estado = 'pendiente'` y los envía vía HTTP POST hacia `SYNC_TARGET_URL`.
- **`app/Console/Commands/RetrySyncCommand.php` (`sync:retry`):** Reintenta la sincronización a nivel de modelos Eloquent mediante el trait `SyncsToRemote`.

---

## 5. MAPA DE VISTAS (BLADE TEMPLATES)

```
resources/views/
├── layouts/
│   ├── app.blade.php                 # Layout principal (CSS variables, navbar responsive, soporte minimal)
│   ├── layout.blade.php              # Layout alternativo legacy
│   └── layout.blade_dark.php         # Layout en tema oscuro
├── Ventas/
│   ├── index.blade.php               # Vista principal POS (Escaneo, lista productos, totales, eventos JS)
│   ├── index.blade_js.php            # Variante con soporte JS modular
│   └── index2button.balde.php        # Variante con teclado táctil de 2 botones
├── Pagos/
│   ├── index.blade.php               # Pantalla de cobro, teclado numérico de vuelto y selección de medio
│   └── index.blade copy.php          # Respaldo histórico
├── Caja/
│   └── index.blade.php               # Panel de recepción y cobro de folios aparcados
├── ConsultaPrecio/
│   └── index.blade.php               # Vista Kiosco / Pop-up de verificación de precios
├── Devolucion/
│   └── index.blade.php               # Interfaz de escaneo y balance de productos devueltos
├── InformeVentas/
│   └── index.blade.php               # Reporte diario (Medios, empresas, productos, kilos, compras)
├── Stock/
│   ├── index.blade.php               # Catálogo de stock con buscador y alertas de mínimo
│   ├── create.blade.php              # Alta de nuevo producto
│   └── edit.blade.php                # Modificación de producto y precios
├── StockPack/
│   ├── index.blade.php               # Listado de packs/combos
│   ├── create.blade.php              # Alta de cabecera de pack
│   └── edit.blade.php                # Edición de cabecera
├── StockPackDetalle/
│   ├── index.blade.php               # Detalle de productos asignados al pack
│   └── edit.blade.php                # Asignación y cambio de cantidades en combo
├── StockPendiente/
│   └── index.blade.php               # Listado de reposición con filtros y acciones masivas
├── Factura/
│   ├── index.blade.php               # Histórico de pagos de facturas por fecha y empresa
│   └── create.blade.php              # Registro de nuevo pago a proveedor
├── ResumenVenta/
│   └── index.blade.php               # Consulta de tickets históricos y su detalle
├── VentaAnalisis/
│   └── index.blade.php               # Gráficos y tablas de ventas por hora, día y top 20
├── Configuracion/
│   ├── index.blade.php               # Formulario de parámetros del local
│   └── create.blade.php              # Formulario para crear nuevas claves de configuración
├── Locales/
│   ├── index.blade.php               # Listado de sucursales (Nivel >= 100)
│   └── edit.blade.php                # Modificación de suscripción de local
└── auth/                             # Vistas de login, registro y recuperación de clave
```

---

## 6. MODELOS DE DATOS Y ESTRUCTURA DE BASE DE DATOS

| Modelo / Entidad | Tabla en BD | Llave Primaria | Propósito Principal |
| :--- | :--- | :--- | :--- |
| `App\Models\User` | `tbl_local_marco_usuarios` | `id_usuario` | Usuarios del sistema, roles, niveles y asignación a local |
| `App\Models\Configuracion` | `tbl_local_marco_cofiguracion` | `idConfiguracion` | Parámetros clave-valor por local (`categoria`, `valor`, `nivel`) |
| `App\Models\Ventas` | `tbl_local_marco_ventas` | `id_ventas` | Detalle ítem por ítem de cada venta cerrada |
| - | `tbl_local_marco_ventas_total` | `id_ventas` | Cabecera de venta (total, forma de pago, vuelto, fecha) |
| - | `tbl_local_marco_ventas_detalle_tmp` | `id_ventas` | Carrito de venta temporal en curso por usuario/local |
| - | `tbl_local_marco_stock` | `id_producto` | Inventario maestro del local (precios, stock, mínimos, mayorista) |
| - | `tbl_local_marco_stock_pack` | `id_pack` | Cabeceras y detalles de combos/packs promocionales |
| - | `tbl_local_marco_stock_pendiente` | `id` | Lista de reposición y compras pendientes |
| - | `tbl_local_marco_caja` | `id_caja` | Movimientos y balance acumulado de caja por fecha |
| - | `tbl_local_marco_facturas` | `id_factura` | Registro de pagos de facturas a proveedores |
| - | `tbl_local_marco_log` | `id_log` | Trazas de auditoría y cola de sincronización |
| - | `tbl_locales` | `id_local` | Catálogo de locales/sucursales y estado de suscripción |

### Campos de Auditoría y Sincronización
La mayoría de las tablas transaccionales cuentan con los campos:
- `ultima_actualizacion`: Timestamp del último cambio en la fila.
- `actualizacion_estado`: Estado de replicación (`'pendiente'`, `'sincronizado'`, `'CONSULTA'`).

---

## 7. MATRIZ DE ROLES Y NIVELES DE USUARIO

| Nivel de Usuario | Rol | Alcance y Permisos |
| :---: | :--- | :--- |
| **$< 10$** | **Vendedor / Operador** | - Acceso a `Ventas` y `ConsultaPrecio`.<br>- Si `cajaPagadora == 1`: Permite aparcar ventas para que sean cobradas en caja central.<br>- No puede ver ni modificar configuraciones, inventarios ni reportes de cierre. |
| **$\ge 10$** | **Cajero / Administrador de Local** | - Todos los permisos de vendedor.<br>- Acceso a módulo de `Caja` (cobro y liberación de folios aparcados).<br>- Gestión completa de `Stock`, `StockPack` y `StockPendiente`.<br>- Acceso a `CierreDia`, exportación CSV y `ResumenVenta`.<br>- Modificación de variables de su local en `Configuracion`. |
| **$\ge 100$** | **Superadministrador del Sistema** | - Todos los permisos anteriores.<br>- Gestión de todos los locales en `Locales`.<br>- Creación de nuevas variables globales en `Configuracion.create`.<br>- Visibilidad transversal sobre todas las sucursales. |

---

## 8. GUÍA RÁPIDA PARA DESARROLLADORES Y MANTENIMIENTO

### ¿Dónde tocar según lo que necesites modificar?

1. **Si necesitas cambiar la lógica de cálculo de precios, pesables o códigos de barra:**
   $\rightarrow$ Archivos: `app/Http/Controllers/VentaController.php` (métodos `store` y `fncTraerProductosCodigo`) y `resources/views/Ventas/index.blade.php` (función JS `fncBuscaProducto`).
2. **Si necesitas modificar las formas de pago, vuelto o persistencia de ventas:**
   $\rightarrow$ Archivo: `app/Http/Controllers/PagoController.php` (métodos `update` y `fncGuardaVenta`).
3. **Si necesitas agregar o modificar una variable de configuración del local:**
   $\rightarrow$ Archivos: `app/Http/Controllers/ParametriaController.php` (método `fncTraeConfiguracion`) y `app/Http/Controllers/ConfiguracionController.php`.
4. **Si necesitas ajustar el informe de cierre diario o exportación Excel/CSV:**
   $\rightarrow$ Archivo: `app/Http/Controllers/CierreDiaController.php` (métodos `index`, subconsultas y `exportCSV`).
5. **Si necesitas modificar la interfaz visual o navegación:**
   $\rightarrow$ Archivos: `resources/views/layouts/app.blade.php` (menú y tokens CSS) y las vistas correspondientes dentro de `resources/views/`.
6. **Si necesitas revisar o agregar tablas a la sincronización remota:**
   $\rightarrow$ Archivos: `app/Console/Commands/SyncDirectCommand.php` y `app/Http/Controllers/Api/SyncDBController.php`.
