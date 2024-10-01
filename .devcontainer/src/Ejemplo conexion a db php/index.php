<?php
$servername = "mysql:3306";  
$username = "root";
$password = "root";     
$dbname = "dawdb";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
echo "Conexión exitosa a la base de datos!";
?>
