# Documentación de Implementación

## a. Implementación

### i. Cronograma de implementación

El proceso de implementación del sistema se dividió en varias fases clave para asegurar una transición ordenada y controlada.

| Fase | Actividad Principal | Duración Estimada | Estado |
| :--- | :--- | :--- | :--- |
| **Fase 1** | **Planificación y Preparación del Entorno** | 1 semana | Completado |
| | - Configuración del servidor web (XAMPP). | | |
| | - Creación de la base de datos en MySQL. | | |
| | - Verificación de versiones de PHP y dependencias. | | |
| **Fase 2** | **Despliegue y Migración de Base de Datos** | 3 días | Completado |
| | - Instalación de la aplicación en el servidor. | | |
| | - Ejecución de scripts de migración (`run_migrations.php`) para crear la estructura de tablas. | | |
| **Fase 3** | **Carga de Datos Iniciales** | 4 días | Completado |
| | - Migración de catálogos de productos, proveedores y clientes existentes. | | |
| | - Configuración de usuarios y roles iniciales. | | |
| **Fase 4** | **Pruebas de Usuario Final (UAT) y Capacitación** | 1 semana | Completado |
| | - Sesiones de prueba con personal de caja, bodega y administración. | | |
| | - Capacitación sobre los módulos de ventas, inventario y reportes. | | |
| **Fase 5** | **Puesta en Marcha y Soporte** | 2 semanas | En curso |
| | - Lanzamiento oficial del sistema para operaciones diarias. | | |
| | - Monitoreo, resolución de incidencias y soporte post-implementación. | | |

### ii. Descripción de la implementación

La implementación del sistema de farmacia se llevó a cabo siguiendo un enfoque estructurado para garantizar la correcta instalación y funcionamiento en el entorno de producción del cliente.

1.  **Preparación del Entorno del Servidor**: Se utilizó un entorno XAMPP estándar con PHP 8.x y MySQL. Se configuró el `virtual host` de Apache para apuntar al directorio raíz del proyecto. La conexión a la base de datos se ajustó en el archivo `config/conexion.php`.

2.  **Despliegue de la Aplicación**: El código fuente de la aplicación fue transferido al directorio `c:\xampp\htdocs\farmacia`. Se instalaron las dependencias del proyecto gestionadas a través de Composer (`composer install`).

3.  **Estructura de la Base de Datos**: Se ejecutó el script `run_migrations.php` desde la línea de comandos. Este script procesó secuencialmente los archivos en la carpeta `migraciones/` (`001_crear_tablas_basicas.php`, `002_crear_tablas_usuarioRol.php`, etc.) para construir el esquema completo de la base de datos, asegurando la integridad relacional.

4.  **Carga de Datos Maestros**: Se realizó una carga masiva de datos críticos para el arranque del sistema, incluyendo:
    *   Catálogo de productos.
    *   Lista de proveedores.
    *   Base de datos de clientes recurrentes.
    *   Configuración de bodegas y sucursales.

5.  **Configuración de Roles y Usuarios**: Se crearon los perfiles de usuario (Administrador, Cajero, Bodeguero) y se asignaron los permisos correspondientes a cada rol, utilizando las interfaces de gestión de usuarios del sistema.

### iii. Descripción de las pruebas realizadas con usuarios finales

Las Pruebas de Aceptación de Usuario (UAT) fueron cruciales para validar que el sistema cumplía con las necesidades operativas del día a día.

*   **Participantes**: Se seleccionó a personal clave de diferentes áreas: un cajero, un encargado de bodega y el administrador general.
*   **Metodología**: Se diseñaron casos de prueba basados en flujos de trabajo reales. A cada usuario se le asignó una serie de tareas para completar en el sistema.
*   **Escenarios de Prueba Validados**:
    1.  **Flujo de Venta**: Un cajero realizó una venta completa, desde la búsqueda de un producto (usando `autocomplete_productos.php`), agregarlo al carrito, aplicar un descuento, seleccionar forma de pago (`formapagoController.php`) y registrar la transacción.
    2.  **Gestión de Inventario**: El encargado de bodega registró el ingreso de nueva mercadería de un proveedor (`ingresoController.php`), actualizó el stock de varios productos y realizó un traslado entre bodegas (`sucursalBodegaController.php`).
    3.  **Administración y Reportes**: El administrador generó reportes de ventas del día, consultó el inventario actual (`inventarioController.php`) y registró un nuevo cliente (`clienteController.php`).
*   **Retroalimentación**: La retroalimentación fue positiva. Se solicitaron ajustes menores, como cambiar el nombre de una etiqueta en la vista de ventas y añadir un campo de búsqueda adicional en la pantalla de clientes, los cuales fueron implementados antes de la puesta en marcha.

### iv. Eventualidades ocurridas durante la implementación

Durante el proceso surgieron algunas eventualidades que fueron gestionadas para minimizar su impacto en el cronograma.

1.  **Inconsistencia en Datos de Origen**: Al realizar la carga masiva del catálogo de productos, se detectó que el archivo Excel proporcionado por el cliente contenía formatos de fecha inconsistentes y códigos de producto duplicados. Esto requirió un trabajo de limpieza y normalización de datos que retrasó la carga inicial en un día.

2.  **Ajustes de Usabilidad en Módulo de Ingresos**: Durante las pruebas UAT, el encargado de bodega señaló que el proceso para registrar un lote y su fecha de vencimiento podía ser más rápido. Se realizó un ajuste en la interfaz del formulario de ingresos (`views/ingreso.php`) para autocompletar ciertos campos y reducir el número de clics.

3.  **Configuración de Red**: Inicialmente, una de las terminales de punto de venta no podía acceder al sistema debido a una configuración incorrecta del firewall local. El problema fue diagnosticado y resuelto por el equipo de soporte técnico del cliente, sin requerir cambios en la aplicación.

4.  **Capacitación Adicional**: Se observó que el concepto de "categorías de productos" y su impacto en los reportes no fue completamente asimilado en la primera sesión. Se organizó una segunda capacitación de 30 minutos enfocada exclusivamente en la gestión de categorías (`categoriaProductoController.php`), lo que resolvió las dudas.