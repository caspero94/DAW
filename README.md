Entorno de desarrollo listo para realizar las clases del 1º curso de daw/dam

Contenedor devcontainer - docker compose

Requisitos previos:

- Si tenéis instalado mysql o xampp en vuestro pc, debéis desinstalarlo para evitar conflictos con el puerto 3306 o detener el servicio manualmente, también podéis cambiar el puerto en el archivo docker-compose.yml en el servicio mysql:  cambiar de  - "3306:3306"  por  - "3300:3306"

- Descargar e instalar:

  - [visual studio code](https://visualstudio.microsoft.com/es/thank-you-downloading-visual-studio/?sku=community&channel=release&version=vs2022&source=vslandingpage&cid=2030&passive=false)
  - [extension devcontainer para vscode](https://marketplace.visualstudio.com/items?itemname=ms-vscode-remote.remote-containers)
  - [docker](https://docs.docker.com/get-started/get-docker/)

Que incluye:

- soporte: java, python, mysql, html, css, js, php, phpmyadmin, apache, git (autocompletado, ejeecucción, depuración, etc)
- visual studio code configurado con extensiones necesarias
- explorador web de projectos php en localhost

- instalación:

  - 1º descargar carpeta daw o [realizar git clone ](https://github.com/caspero94/daw)
  - 2º abrir carpeta daw con vscode, vscode detecta el devcoontainer y preguntara si desea abrir el proyecto en un contenedor, aceptas o pulsa f1 y escriben reopen in container y enter, esperamos a que finalice la instalación y configuración de los contenedores y pulsamos enter.
  - 3º entra en [phpmyadmin [puerto 8080]](http://localhost:8080/) con user y pass "root", en la parte inferior informa que esta deshabilitado algunas funciones, dale clic, le dirá que necesita crear base de datos phpmyadmin, clic en crear, y la configuración esta finalizada.
  - 4º a programar!

Ajustes:

  - [explorador php [puerto 80]](http://localhost/)
  - [phpmyadmin [puerto 8080]](http://localhost:8080/) 
  - mysql [puerto 3306]
  - user: root - password: root

Autor: pedro pereira

:D:D
