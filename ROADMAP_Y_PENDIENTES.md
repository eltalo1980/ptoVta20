# 📌 Roadmap de Tareas y Plan de Implementación (ptoVta20)

Este documento detalla las tareas prioritarias, especificaciones de diseño y arquitectura para los siguientes módulos del sistema **ptoVta20**.

---

## 1. 🚀 Proceso de Onboarding para Nuevos Locales

El objetivo es guiar al nuevo usuario/dueño del negocio inmediatamente después de completar el formulario de registro (`Register`), asegurando que su punto de venta quede 100% operativo en menos de 5 minutos.

### 📋 Flujo del Wizard de Configuración Inicial (5 Pasos)

1. **Paso 1: Identidad del Negocio**
   - Nombre de fantasía y razón social.
   - Carga de Logotipo comercial (para cabecera y boletas/tickets térmicos).
   - Dirección física, teléfono de contacto y RUT tributario.

2. **Paso 2: Modo de Operación y Venta**
   - **Tipo de negocio:** Minimarket / Almacén, Verdulería / Pesables, Panadería, Botillería, Tienda General.
   - **Modalidad de Caja:**
     - *Caja Unificada:* El vendedor cobra directamente en su terminal.
     - *Caja Pagadora:* Vendedores generan ventas aparcadas con código de barra / folio para cobro en caja central.
   - **Balanza / Pesables:** Definir si opera en kilos (`divisorPeso = 1`) o gramos (`divisorPeso = 1000`).
   - **Monto inicial de apertura de caja:** Fondo fijo para sencillo/vuelto.

3. **Paso 3: Carga Inicial de Inventario**
   - **Opción A:** Carga rápida desde el catálogo maestro global (`tbl_master_productos`).
   - **Opción B:** Importador masivo mediante plantilla Excel / CSV (Código, Descripción, Costo, Venta, Stock).
   - **Opción C:** Registro manual de los primeros 5 productos principales.

4. **Paso 4: Equipo y Usuarios**
   - Creación de cajeros y vendedores adicionales para el local.
   - Asignación de credenciales rápidas / PIN de acceso al punto de venta.

5. **Paso 5: Primera Venta Guiada (Tour Interactivo)**
   - Simulación paso a paso: Escaneo de código de barra, selección de medio de pago (Efectivo/Tarjeta), cálculo de vuelto e impresión de ticket demo.

---

## 2. 🎨 Configuración de Colores y Personalización Visual (Theming)

Permitir que cada local adapte la interfaz del punto de venta a los colores corporativos de su marca.

### 🛠️ Parámetros en Base de Datos (`tbl_ptovta20_configuracion`)
- `colorTemaPrimario`: Color principal para barras de navegación, botones primarios y encabezados (ej: `#0d6efd`, `#198754`, `#d63384`).
- `colorTemaSecundario`: Color de acento para totales, alertas destacadas y botones de acción rápida.
- `modoOscuroPorDefecto`: `0` (Tema Claro / Blanco POS) o `1` (Tema Oscuro MDB Dark).
- `logoUrl`: Ruta del archivo de logotipo del local.
- `buttonSize`: Tamaño de botones táctiles (`chico`, `mediano`, `grande`).
- `textSize`: Tipografía táctil (`h1`, `h2`, `h3`).

### 💻 Implementación Técnica en Front-End
- Inyección de variables CSS dinámicas en `layouts/app.blade.php` y `layouts/layout.blade.php`:
  ```html
  <style>
    :root {
      --ptovta-primary: {{ $colorTemaPrimario ?? '#0d6efd' }};
      --ptovta-secondary: {{ $colorTemaSecundario ?? '#6c757d' }};
      --ptovta-pos-bg: {{ $colorPosBg ?? '#f8f9fa' }};
    }
  </style>
  ```
- **Panel de Personalización en `Configuracion/index.blade.php`:**
  - Paletas de colores preconfiguradas con 1 clic (Azul Clásico, Verde Market, Rojo / Naranja Fast Food, Morado Boutique, Modo Dark Neon).
  - Selector de color interactivo (*Color Picker*) con previsualización en vivo.

---

## 3. 📊 Reportería y Analítica de Negocio

Módulo integral de reportería para control de gestión, inventario y contabilidad por local.

### 📈 Informes Prioritarios

| Reporte | Descripción | Métricas / Filtros |
| :--- | :--- | :--- |
| **Cierre y Arqueo Diario de Caja** | Control de dinero físico vs registrado en sistema por turno/fecha. | Total Efectivo, Tarjeta, Transferencia, Venta Interna, Vueltos, Descuadres (+/-). |
| **Ventas por Período** | Evolución temporal de ingresos y tickets emitidos. | Filtro por día, rango de fechas, mes y año. Gráficos comparativos de crecimiento. |
| **Ranking de Productos (Top Ventas & ABC)** | Identificación de productos estrella y de baja rotación. | Cantidad de unidades/kilos vendidos, recaudación total y margen de ganancia por ítem. |
| **Rentabilidad y Márgenes** | Cálculo de utilidad bruta real del negocio. | Ventas Totales vs Costo de Venta (`precio_venta - precio_costo`) cruzado con pagos de facturas (`tbl_ptovta20_facturas`). |
| **Stock Crítico y Reposición** | Detección preventiva de quiebre de stock. | Productos con `cantidad <= cantidad_minima`. Botón de traspaso automático a `tbl_ptovta20_stock_pendiente`. |
| **Valorización Total de Inventario** | Patrimonio total del negocio en mercadería. | Monto total valorizado a precio de costo, valorizado a precio de venta y ganancia proyectada. |
| **Auditoría de Bajas y Devoluciones** | Control interno y prevención de pérdidas/mermas. | Ítems borrados en ventas temporales (`tbl_ptovta20_ventas_detalle_tmp_borrada`), devoluciones procesadas y usuario responsable. |

### 📤 Formatos de Exportación
- **PDF Térmico (58mm / 80mm):** Resumen de cierre de caja para impresora de boleta.
- **PDF Formato Carta:** Informes gerenciales con tablas y gráficos para imprimir o enviar por correo.
- **Excel / CSV:** Exportación limpia compatible con software contable y hojas de cálculo.

---

## 4. ⚙️ Tareas Técnicas Inmediatas de Código

1. [ ] **ParametriaController:** Actualizar los métodos para retornar los nombres `tbl_ptovta20_*`.
2. [ ] **Modelos Eloquent:** Actualizar `User.php`, `Configuracion.php`, `Stock.php` con las nuevas tablas.
3. [ ] **RegisterController:** Implementar la transacción de registro que crea `tbl_ptovta20_locales` + usuario `tbl_ptovta20_usuarios` + parámetros por defecto.
4. [ ] **Middleware / Sesión Multi-Local:** Asegurar que las consultas siempre aíslen datos por `Auth::user()->id_local`.
