#!/bin/sh

REPO_URL="https://github.com/caspero94/DAW.git"
TEMP_DIR="temp_repo"

# Clona el repositorio en la carpeta temporal
git clone $REPO_URL $TEMP_DIR

# Copia el contenido a la carpeta de trabajo, incluyendo archivos ocultos
cp -r $TEMP_DIR/. ./

# Elimina la carpeta temporal
rm -rf $TEMP_DIR

# Limpia archivos no rastreados (opcional)
git clean -fd
