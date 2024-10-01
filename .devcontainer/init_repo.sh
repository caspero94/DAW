#!/bin/bash

REPO_URL="https://github.com/caspero94/DAW.git"
TEMP_DIR="temp_repo"

# Clona el repositorio en la carpeta temporal
git clone $REPO_URL $TEMP_DIR

# Usa rsync para mover el contenido a la carpeta de trabajo, excluyendo la carpeta .git
rsync -a --exclude='.git' $TEMP_DIR/ ./

# Elimina la carpeta temporal
rm -rf $TEMP_DIR

# Limpia archivos no rastreados (opcional)
git clean -fd
