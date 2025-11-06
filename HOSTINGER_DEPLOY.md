# 🚀 GUÍA DE DESPLIEGUE EN HOSTINGER - FERRETERÍA COSTA SUR

## 🔍 **Diagnóstico del Error 403**

El error **403 Forbidden** después del login en Hostinger puede deberse a:

1. **Problemas de permisos de archivos**
2. **Configuración incorrecta de sesiones PHP**
3. **URLs mal formadas en las redirecciones**
4. **Configuración del .htaccess**
5. **Problemas con la base de datos**

---

## 🛠️ **PASOS PARA SOLUCIONAR**

### **1. Verificar Permisos de Archivos**
```bash
# En tu panel de Hostinger o via FTP:
- Carpetas: 755 (rwxr-xr-x)
- Archivos PHP: 644 (rw-r--r--)
- Archivo .htaccess: 644 (rw-r--r--)
```

### **2. Configurar Base de Datos**
Actualiza `config/conexion.php` con los datos de Hostinger:
```php
// Datos típicos de Hostinger
$host = 'localhost'; // O el que te proporcionen
$dbname = 'u123456789_farmacia'; // Tu nombre de BD
$username = 'u123456789_admin'; // Tu usuario
$password = 'TuPasswordSeguro'; // Tu contraseña
```

### **3. Usar el Archivo Diagnóstico**
Sube `diagnostico.php` y accede a:
```
https://tudominio.com/diagnostico.php
```

### **4. Archivos Creados para Hostinger**
- ✅ `hostinger_config.php` - Configuración específica
- ✅ `.htaccess` - Reglas de servidor
- ✅ `diagnostico.php` - Herramienta de diagnóstico
- ✅ `AuthController.php` - Redirecciones mejoradas

---

## 🔧 **CONFIGURACIONES ACTUALIZADAS**

### **AuthController.php**
- ✅ **Redirecciones compatibles** con hosting compartido
- ✅ **Manejo de errores** mejorado
- ✅ **Múltiples métodos** de redirección (headers + JavaScript)
- ✅ **URLs relativas** para evitar problemas de dominio

### **.htaccess**
- ✅ **Configuración de seguridad** básica
- ✅ **Redirecciones limpias** para URLs
- ✅ **Configuración PHP** para sesiones
- ✅ **Caché y compresión** optimizados

### **hostinger_config.php**
- ✅ **Detección automática** de Hostinger
- ✅ **Configuración de sesiones** optimizada
- ✅ **Funciones helper** para redirecciones
- ✅ **Configuración de errores** para producción

---

## 🎯 **PROCESO DE DESPLIEGUE**

### **Paso 1: Subir Archivos**
```
1. Comprimir toda la carpeta farmacia
2. Subir via File Manager de Hostinger
3. Extraer en public_html/
4. Verificar permisos de archivos
```

### **Paso 2: Configurar Base de Datos**
```
1. Crear base de datos en Hostinger
2. Importar tu archivo SQL
3. Actualizar config/conexion.php
4. Probar con diagnostico.php
```

### **Paso 3: Probar Sistema**
```
1. Acceder a https://tudominio.com/
2. Intentar login
3. Si hay error, usar diagnostico.php
4. Revisar logs de error de Hostinger
```

---

## 🚨 **SOLUCIONES ESPECÍFICAS PARA ERROR 403**

### **Si el error persiste después del login:**

#### **Opción 1: URLs Absolutas**
En `AuthController.php`, cambiar:
```php
header("Location: index.php?route=dashboard");
```
Por:
```php
$baseUrl = 'https://tudominio.com/';
header("Location: {$baseUrl}index.php?route=dashboard");
```

#### **Opción 2: Verificar Sesiones**
Agregar al inicio de `dashboard.php`:
```php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: index.php?error=session');
    exit;
}
```

#### **Opción 3: Debug Mode**
Activar temporalmente en `index.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## 📊 **CHECKLIST DE VERIFICACIÓN**

### **Antes de Contactar Soporte:**
- [ ] Permisos de archivos correctos (755/644)
- [ ] Base de datos configurada y funcionando
- [ ] `diagnostico.php` ejecutado sin errores
- [ ] `.htaccess` subido correctamente
- [ ] URLs de redirección verificadas
- [ ] Sesiones PHP funcionando
- [ ] Logs de error revisados

### **Información para Soporte de Hostinger:**
- **Error:** 403 Forbidden después de login
- **PHP Version:** (ver en diagnóstico)
- **Archivos subidos:** Sistema completo de farmacia
- **Base de datos:** Configurada y conectando
- **Problema específico:** Redirección post-login

---

## 🎯 **URLs DE PRUEBA**

Una vez desplegado, probar estas URLs:
```
https://tudominio.com/diagnostico.php
https://tudominio.com/index.php
https://tudominio.com/index.php?route=dashboard
```

---

## 💡 **NOTAS IMPORTANTES**

1. **Hostinger usa PHP 8.x** por defecto - verificar compatibilidad
2. **Las sesiones** pueden necesitar configuración específica
3. **Los paths absolutos** son más confiables que relativos
4. **El .htaccess** debe estar en la raíz del dominio
5. **Los logs de error** están en el panel de Hostinger

---

## 🆘 **SI NADA FUNCIONA**

### **Plan B - Configuración Mínima:**
1. Comentar todo el `.htaccess`
2. Usar solo redirecciones con `header()`
3. Verificar que PHP sessions funcionen
4. Probar con URLs completas y absolutas

### **Contactar Soporte:**
- **Email:** Con capturas del error y `diagnostico.php`
- **Información:** Versión PHP, configuración de sesiones
- **Archivos:** Enviar `AuthController.php` y `index.php`

---

¡Con estos cambios, el error 403 debería resolverse! 🚀✨