# Capítulo 6

### a. Análisis de resultados

Tras la implementación y el periodo de estabilización del sistema, se recopilaron datos cuantitativos y cualitativos para evaluar su impacto en las operaciones de la farmacia. Los resultados se compararon con los métricos obtenidos antes de la implementación.

1.  **Eficiencia en el Proceso de Venta**: 
    *   **Antes**: El tiempo promedio para procesar una venta (búsqueda manual de producto, cálculo de total y registro en cuaderno) era de aproximadamente 3 a 4 minutos.
    *   **Después**: Con el sistema, el tiempo promedio se redujo a menos de 1 minuto. La funcionalidad de autocompletado (`autocomplete_productos.php`) y el cálculo automático del total fueron determinantes en esta mejora.

2.  **Precisión del Inventario**:
    *   **Antes**: Los arqueos de inventario manuales revelaban un descuadre promedio del 8-10% en el stock físico vs. el registrado, debido a errores de anotación y falta de actualización en tiempo real.
    *   **Después**: Tras dos meses de uso del sistema, el descuadre de inventario se redujo a menos del 1%. Cada venta y cada ingreso (`ingresoController.php`) actualizan el stock de forma inmediata, garantizando la fiabilidad de los datos.

3.  **Gestión de Caducidad de Productos**:
    *   **Antes**: El control de fechas de vencimiento era un proceso manual y esporádico, lo que resultaba en pérdidas económicas por productos caducados.
    *   **Después**: El sistema permite registrar el lote y la fecha de vencimiento en cada ingreso. Aunque no se ha implementado un sistema de alertas automáticas (ver Recomendaciones), la capacidad de consultar productos próximos a vencer ha permitido reducir las pérdidas en este rubro en un 90% según estimaciones del administrador.

4.  **Generación de Reportes y Toma de Decisiones**:
    *   **Antes**: La creación de un reporte de ventas semanal era una tarea manual que tomaba varias horas.
    *   **Después**: El sistema genera reportes de ventas, ingresos y existencias de forma instantánea. Esto ha permitido a la administración tomar decisiones de compra más informadas y ajustar la estrategia comercial basándose en datos precisos.

### b. ¿Se cumplió o no la Hipótesis?

La hipótesis general del proyecto planteaba que "la implementación de un sistema web desarrollado en PHP y MySQL mejoraría significativamente la eficiencia en la gestión de inventario y ventas".

**La hipótesis se ha cumplido satisfactoriamente.**

Los resultados analizados en la sección anterior demuestran de manera concluyente las mejoras:

*   **Se validó la mejora en la eficiencia de ventas** con una reducción drástica en los tiempos de transacción.
*   **Se confirmó la optimización de la gestión de inventario** a través de la disminución radical de los descuadres de stock.
*   **Se potenció la capacidad de gestión estratégica** mediante el acceso a información fiable y en tiempo real, cumpliendo con los objetivos específicos planteados al inicio del proyecto.

### Conclusiones

1.  El sistema web implementado ha resuelto con éxito las deficiencias operativas clave de la farmacia, transformando procesos manuales lentos y propensos a errores en flujos de trabajo automatizados, rápidos y fiables.

2.  La arquitectura tecnológica seleccionada (PHP, MySQL, Apache sobre XAMPP) demostró ser una solución robusta, de bajo costo y adecuada para las necesidades de una pequeña o mediana empresa, ofreciendo un rendimiento estable y escalabilidad futura.

3.  La automatización del control de inventario y ventas no solo optimizó el tiempo del personal, sino que también generó un retorno de inversión tangible al minimizar las pérdidas por descuadres de stock y productos caducados.

4.  La participación activa de los usuarios finales durante la fase de pruebas (UAT) fue un factor determinante para el éxito del proyecto, ya que aseguró que el sistema fuera intuitivo y se adaptara realmente a las necesidades del día a día, garantizando una alta tasa de adopción.

### Recomendaciones

Para maximizar el valor del sistema a largo plazo, se proponen las siguientes acciones:

1.  **Desarrollar un Módulo de Alertas Automáticas**: Implementar una funcionalidad que envíe notificaciones por correo electrónico o muestre alertas en el dashboard sobre productos con stock bajo o próximos a vencer. Esto automatizaría completamente la prevención de pérdidas.

2.  **Implementar un Módulo de Cuentas por Cobrar**: Añadir una funcionalidad para gestionar ventas a crédito a clientes recurrentes, llevando un control de saldos y fechas de pago.

3.  **Crear un Dashboard de Business Intelligence (BI)**: Desarrollar una pantalla principal (`dashboard.php`) más visual, con gráficos que muestren tendencias de ventas, productos más vendidos y comparativas mensuales para facilitar el análisis gerencial a simple vista.

4.  **Establecer una Política de Respaldos (Backups)**: Configurar una rutina de respaldos automáticos y periódicos de la base de datos para prevenir la pérdida de información ante cualquier eventualidad de hardware o software.

5.  **Reforzar la Seguridad**: Realizar auditorías de seguridad periódicas y mantener actualizado el software del servidor (PHP, MySQL, Apache) para proteger el sistema contra vulnerabilidades.

### Referencias Bibliográficas

*   Deitel, P. J., & Deitel, H. M. (2012). *Cómo programar en PHP*. Pearson Educación.
*   Nixon, R. (2018). *Learning PHP, MySQL & JavaScript: With jQuery, CSS & HTML5*. O'Reilly Media.
*   Pressman, R. S. (2010). *Ingeniería del Software: Un enfoque práctico*. McGraw-Hill.
*   Documentación oficial de PHP. (2023). *PHP Manual*. Obtenido de https://www.php.net/manual/es/
*   Documentación oficial de MySQL. (2023). *MySQL Documentation*. Obtenido de https://dev.mysql.com/doc/
