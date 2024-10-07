# 📝 **Notas de Parche**
![banner](https://via.placeholder.com/1200x100.png/1f2426/e4b2ad?text=Notas+de+Parche+0.2)  

---

## 📦 **VERSIÓN 0.2.0**

### 🌐 **Estructura mejorada con nuevas imágenes Docker:**
- **Agregamos imágenes Docker:**
  - **Java**: Soporte para aplicaciones Java.
  - **Python**: Soporte para aplicaciones Python.
- **Mejora en el rendimiento de carga posterior:** Una vez que se realiza el `rebuild` o se recarga el entorno, la carga es mucho más rápida.

### 🖥️ **Mejoras en LocalHost:**
- **Transformación a explorador:** La página web de LocalHost ahora actúa como un explorador para navegar y visualizar materiales didácticos.
- **Nueva biblioteca sincronizada con GitHub:** Se ha añadido una biblioteca sincronizada con un repositorio de GitHub, que estará en constante actualización. Esto permitirá mantener actualizados los archivos y documentos relevantes para las clases.

### ⚡ **Mejoras de rendimiento y estabilidad:**
- **Mayor velocidad de carga:** Se han realizado optimizaciones internas que resultan en una carga más rápida del entorno de trabajo.
- **Estructura interna mejorada:** Se ha reorganizado la estructura interna del proyecto para darle más consistencia y robustez.
- **Correcciones de errores menores:** Se han solucionado varios errores pequeños, logrando una versión mucho más estable.

---

## 📦 **VERSIÓN 0.1.4**

### 🆕 **Extensiones añadidas:**
- **Prettier - Code formatter:** Formatea automáticamente el código, asegurando un estilo consistente.
- **Docker:** Gestión de contenedores y aplicaciones Docker directamente desde VSCode.

### 🔧 **Configuración persistente de Git:**
- Archivo de configuración añadido en la carpeta `.devcontainer`, lo que permite personalizar `user.name` y `user.email`.
- Para modificar estos valores, edita el archivo `.gitconfig` en `.devcontainer` con la información deseada.

---

## 📦 **VERSIÓN 0.1.3**

### 🆕 **Notas de Parche:**
- Ahora puedes consultar las novedades y cambios de cada versión directamente en el repositorio.

### 👀 **Live Preview en VSCode:**
- Extensión **Live Preview** añadida para la visualización de páginas web directamente en VSCode.

### ⬆️ **Aumento del límite de subida en phpMyAdmin:**
- Límite incrementado a **64M** para mejor manejo de archivos grandes.

### 🔧 **Configuración global de Git:**
- Configuración genérica de Git añadida:
  ```bash
  git config --global user.name "Your Name"
  git config --global user.email "you@example.com"
  ```
- 📁 **Tema 1 - Lenguaje de Marcas:** Se agregaron los archivos correspondientes al Tema 1 de Lenguaje de Marcas.
- 🚫 **Eliminación de `.gitignore`:** El archivo `.gitignore` se elimina automáticamente después de aplicar una actualización (rebuild) del contenedor.

---

## 🔧 **VERSIÓN 0.1.0**

- 📂 **Reorganización de estructura de archivos:** Se ha realizado un cambio interno en la estructura de los archivos del proyecto para optimizar su manejo y uso.
- 🔗 **Soporte para GitHub personal:** Se ha agregado soporte para que los usuarios puedan utilizar sus propios repositorios de GitHub.
- 🚀 **Optimización del contenedor:** El contenedor ha sido optimizado para mejorar el rendimiento y reducir tiempos de carga.
- ♻️ **Actualización automática:** Ahora, al realizar un `rebuild` del contenedor, este se actualizará automáticamente con las últimas configuraciones y archivos.
- 🛠️ **Soporte para MySQLi:** Se ha agregado soporte para la extensión **MySQLi** en PHP, mejorando la interacción con bases de datos.
- 💾 **Persistencia de archivos locales:** Los archivos locales añadidos al workspace se mantendrán incluso después de los cambios en el contenedor.
- 🐞 **Corrección de errores menores:** Se han solucionado bugs pequeños para mejorar la estabilidad general del entorno.

---

✨ Mantente atento a futuras versiones para disfrutar de más mejoras y funcionalidades.
