# Entorno de Desarrollo para el Primer Curso de DAW/DAM

Este repositorio proporciona un entorno de desarrollo completo para las clases de primer curso de Desarrollo de Aplicaciones Web (DAW) y Desarrollo de Aplicaciones Multiplataforma (DAM). Utiliza DevContainer y Docker Compose para garantizar un entorno estandarizado y fácil de desplegar.

## 🚀 **Características del Entorno**

- **Lenguajes y tecnologías incluidas:**
  - Java
  - Python
  - MySQL
  - PHP (con soporte para phpMyAdmin)
  - HTML, CSS, JavaScript
  - Apache
  - Git (con autocompletado, ejecución, depuración, etc.)
  
- **Integración con Visual Studio Code:**
  - Configuración automática con las extensiones necesarias para desarrollo web y de software.
  - Explorador web de proyectos PHP en localhost.

## ⚙️ **Requisitos Previos**

Antes de comenzar, asegúrate de cumplir con los siguientes requisitos:

1. **Conflictos de puerto MySQL (3306):**
   - Si tienes MySQL o XAMPP instalados en tu equipo, debes desinstalarlos o detener manualmente el servicio para evitar conflictos en el puerto 3306.
   - Alternativamente, puedes modificar el puerto en el archivo `docker-compose.yml`. Cambia la línea:
     ```yaml
     - "3306:3306"
     ```
     por:
     ```yaml
     - "3300:3306"
     ```

2. **Descargar e instalar las siguientes herramientas:**
   - [Visual Studio Code](https://visualstudio.microsoft.com/es/thank-you-downloading-visual-studio/?sku=community&channel=release&version=vs2022&source=vslandingpage&cid=2030&passive=false)
   - [Extensión DevContainer para VSCode](https://marketplace.visualstudio.com/items?itemname=ms-vscode-remote.remote-containers)
   - [Docker](https://docs.docker.com/get-started/get-docker/)

## 🛠️ **Instalación y Configuración del Entorno**

Sigue estos pasos para instalar y configurar el entorno de desarrollo:

1. **Descargar el proyecto:**
   - Clona este repositorio o descarga la carpeta `daw`:
     ```bash
     git clone https://github.com/caspero94/daw
     ```

2. **Abrir el proyecto en Visual Studio Code:**
   - Abre la carpeta `daw` en Visual Studio Code.
   - VSCode detectará el DevContainer y te preguntará si deseas abrir el proyecto dentro del contenedor. Acepta la solicitud o pulsa `F1`, escribe `Reopen in Container` y presiona `Enter`.
   - Espera a que finalice la instalación y configuración de los contenedores.

3. **Configurar phpMyAdmin:**
   - Accede a [phpMyAdmin](http://localhost:8080/) (puerto 8080) con las siguientes credenciales:
     - **Usuario:** `root`
     - **Contraseña:** `root`
   - Si aparece un mensaje indicando que algunas funciones están deshabilitadas, haz clic en la opción correspondiente para crear la base de datos `phpmyadmin`.
   - ¡Listo! La configuración estará completada.

4. **¡Comienza a programar!** 🎉

## 🔧 **Ajustes del Entorno**

- **Explorador PHP (puerto 80):**  
  [http://localhost/](http://localhost/)
  
- **phpMyAdmin (puerto 8080):**  
  [http://localhost:8080/](http://localhost:8080/)
  
- **MySQL (puerto 3306):**  
  - **Usuario:** `root`  
  - **Contraseña:** `root`

## 📚 **Documentación Adicional**

- [Documentación de Docker](https://docs.docker.com/)
- [Documentación de Visual Studio Code](https://code.visualstudio.com/docs)

## 📩 **Contacto**

Si tienes alguna duda o sugerencia sobre este repositorio, no dudes en contactar a:

**Pedro Pereira**  
Email: pedropereira.email@gmail.com