
**Instrucciones para importar la base de datos de ejemplo Sakila**:

```md
# 📥 Instrucciones para Importar la Base de Datos de Ejemplo Sakila

Esta base de datos de ejemplo está parseada específicamente para phpMyAdmin y es utilizada en las clases de Bases de Datos.

## Pasos para la importación:

1. **Accede a phpMyAdmin:**
   - Dirígete a [localhost:8080](http://localhost:8080) e inicia sesión.

2. **Asegúrate de no tener ninguna base de datos seleccionada:**
   - En la parte superior del panel, asegúrate de que ninguna base de datos esté seleccionada antes de proceder.

3. **Selecciona la opción de importar:**
   - En la barra superior de phpMyAdmin, selecciona la pestaña `Importar`.
   - Seleciona el archivo sakila-data_phpmyadmin.zip

4. **Configura la importación:**
   - Desactiva las siguientes opciones:
     - **Importación parcial**
     - **Revisión de claves foráneas**
   - Configura el formato del archivo como **SQL**.
   - En la modalidad SQL, selecciona **NONE** como compatibilidad SQL.
   - Asegúrate de que la opción **No utilizar AUTO_INCREMENT con valor 0** esté activada.

5. **Haz clic en "Importar":**
   - Una vez que completes los ajustes, haz clic en el botón `Importar`.
   - Deberías ver un mensaje confirmando que la importación se realizó con éxito.

6. **Verifica la importación:**
   - Comprueba que la base de datos **sakila** se haya importado correctamente. Deberías ver muchas tablas y consultas en la base de datos, las cuales serán utilizadas durante las clases.

---

Con estos pasos, tendrás lista la base de datos para trabajar en los ejercicios de la clase.
