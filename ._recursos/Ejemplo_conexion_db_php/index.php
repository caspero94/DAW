<?php
$servername = "mysql";  // Nombre del servicio de MySQL en Docker Compose
$username = "root";      // Nombre de usuario de MySQL
$password = "root";      // Contraseña de MySQL
$dbname = "sakila";      // Nombre de la base de datos Sakila

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Realizar una consulta para obtener datos de películas
$sql = "SELECT title, release_year, rating FROM film LIMIT 30"; // Limitar a 30 películas
$result = $conn->query($sql);

// Verificar si se obtuvieron resultados
if ($result->num_rows > 0) {
    // Mostrar los resultados en una tabla HTML
    echo "<h2>Lista de Películas</h2>";
    echo "<table border='1'>
            <tr>
                <th>Título</th>
                <th>Año de Estreno</th>
                <th>Clasificación</th>
            </tr>";
    // Salida de cada fila
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["title"]) . "</td>
                <td>" . htmlspecialchars($row["release_year"]) . "</td>
                <td>" . htmlspecialchars($row["rating"]) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No se encontraron resultados.";
}

// Cerrar la conexión
$conn->close();
?>
