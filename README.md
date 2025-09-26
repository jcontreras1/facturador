# Guía de Instalación del Facturador hecho en LARAVEL

Esta guía proporciona instrucciones paso a paso para configurar y ejecutar el proyecto Laravel desde el repositorio. Sigue estos pasos cuidadosamente para asegurar una instalación exitosa.

## Prerrequisitos

- **PHP**: Versión 8.3 o superior
- **Composer**: Última versión instalada
- **Git**: Para clonar el repositorio
- **Base de datos**: SQLite (recomendado por simplicidad) o MySQL
- **Node.js** (opcional): Para compilar activos CSS/JS
- **OpenSSL** (usuarios de Windows): Si compilas activos en Windows, podrías necesitar el plugin OpenSSL. Descarga la última serie 3.x (edición Light recomendada) desde [Shining Light Productions](https://slproweb.com/products/Win32OpenSSL.html).
  - **Requisitos del sistema para OpenSSL**:
    - Mínimo: Windows XP o posterior, 32MB RAM, CPU 200MHz, 30MB de espacio en disco
    - Recomendado: Windows XP o posterior, 128MB RAM, CPU 500MHz, 300MB de espacio en disco
  - Asegúrate de instalar OpenSSL 3.5.x (lanzado el 9 de abril de 2025), que es una versión LTS.

## Instrucciones de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/jcontreras1/facturador.git
   cd facturador
   ```

2. **Instalar dependencias de PHP**
   ```bash
   composer install
   ```

3. **Configurar el archivo de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar la base de datos**
   ```bash
   php artisan migrate
   ```
   - Si se te pregunta sobre la creación de la base de datos, selecciona "sí".
   - Usa SQLite por simplicidad. Si prefieres MySQL, modifica el conector en el archivo `.env` más adelante.
   - Para poblar la base de datos con datos iniciales:
     ```bash
     php artisan db:seed
     ```

5. **Crear enlace simbólico para almacenamiento**
   ```bash
   php artisan storage:link
   ```

6. **Configurar para entonrnos como cPanel**
   - Copia los archivos del directorio `public` a la carpeta `public_html`:
     ```bash
     cp -r public/* ../public_html/
     ```
   - Edita el archivo `index.php` en la carpeta `public_html/facturar`:
     ```bash
     nano ../public_html/facturar/index.php
     ```
   - Busca las siguientes líneas y modifícalas para que apunten al directorio correcto del proyecto:
     ```php
     require __DIR__.'/../facturador/vendor/autoload.php';
     $app = require_once __DIR__.'/../facturador/bootstrap/app.php';
     ```
   - Guarda los cambios. En este punto, tu aplicación debería estar **funcionando** en `http://tudominio.com/facturar`.

7. **Configurar el archivo `.env`**
   - Edita el archivo `.env`:
     ```bash
     nano .env
     ```
   - Configura las siguientes variables:
     ```env
     APP_NAME="Facturador" # O el nombre que quieras
     APP_TIMEZONE='America/Argentina/Cordoba' 
     APP_URL=        #<== tu sitio web
     APP_LOCALE=es
     APP_FALLBACK_LOCALE=en
     APP_FAKER_LOCALE=es_AR
     ASSET_URL=${APP_URL}
     SANCTUM_STATEFUL_DOMAINS=        #Tu sitio web sin https ni www. Ej: misitioweb.com
     SESSION_DOMAIN=                  #Tu sitio web sin https ni www. con un punto delante. Ej: .misitioweb.com
     VITE_APP_NAME="${APP_NAME}"
     SWEET_ALERT_MIDDLEWARE_AUTO_CLOSE=false
     SWEET_ALERT_MIDDLEWARE_TOAST_POSITION='top-end'
     SWEET_ALERT_MIDDLEWARE_TOAST_CLOSE_BUTTON=true
     SWEET_ALERT_AUTO_DISPLAY_ERROR_MESSAGES=true
     ```
   - Si usas un nombre con espacios, asegúrate de usar comillas, por ejemplo: `APP_NAME="Mi Facturador"`.

8. **Configurar el correo electrónico**
   - En el archivo `.env`, busca el bloque de configuración de correo y actualízalo según tu proveedor de correo. Ejemplo:
     ```env
     MAIL_MAILER=smtp
     MAIL_HOST=smtp.tuproveedor.com
     MAIL_PORT=587
     MAIL_USERNAME=tu_usuario
     MAIL_PASSWORD=tu_contraseña
     MAIL_ENCRYPTION=tls
     MAIL_FROM_ADDRESS="tucorreo@tudominio.com"
     MAIL_FROM_NAME="${APP_NAME}"
     ```

9. **Compilar activos CSS/JS**
   - Si tienes Node.js instalado, compila los activos:
     ```bash
     npm install
     npm run build
     ```
   - Si **no tienes Node.js** o estás usando cPanel:
     - Compila los activos manualmente en tu máquina local ejecutando los comandos anteriores.
     - Sube los archivos generados en `public/build` a la carpeta `public_html/facturar/build` en el servidor.
   - Nota para usuarios de Windows: Asegúrate de tener instalado el plugin OpenSSL (podes descargarlo desde [Shining Light Productions](https://slproweb.com/products/Win32OpenSSL.html)) si encuentras problemas relacionados con certificados SSL durante la compilación.

## Notas para cPanel

- Asegúrate de que la carpeta `public_html/facturar` sea accesible y que los permisos estén configurados correctamente (generalmente 755 para carpetas y 644 para archivos).
- Verifica que el archivo `index.php` apunte correctamente al directorio `facturador` como se indicó en el paso 6.
- Si usas MySQL en lugar de SQLite, configura las variables de conexión en el archivo `.env`:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=nombre_de_tu_base_de_datos
  DB_USERNAME=tu_usuario
  DB_PASSWORD=tu_contraseña
  ```

## Solución de Problemas

- **Errores de base de datos**: Asegúrate de que la base de datos esté creada y que las credenciales en `.env` sean correctas.
- **Errores de compilación de activos**: Si no puedes compilar en el servidor, hazlo localmente y sube los archivos generados.
- **Errores de OpenSSL en Windows**: Descarga e instala la versión más reciente de OpenSSL desde [Shining Light Productions](https://slproweb.com/products/Win32OpenSSL.html). Usa la edición Light de la serie 3.x.

¡Tu aplicación Laravel ya debería estar lista y funcionando! Si tienes problemas, contactame o revisá la documentación oficial de Laravel.
