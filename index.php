<?php
// PAGINA DE INICIO (EXPLORADOR DE PROJECTOS WEB)
$root = __DIR__;

$phpMyAdminUrl = "http://localhost:8080";

// Obtener el directorio actual desde la URL
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Explorador de Proyectos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        p {
            text-align: center;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .project-card, .phpmyadmin-card, .file-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .project-card:hover, .phpmyadmin-card:hover, .file-card:hover {
            transform: scale(1.05);
        }
        a {
            text-decoration: none;
            color: #007BFF;
        }
        .file-list {
            list-style-type: none;
            padding: 0;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Explorador de Proyectos PHP</h1>
    <div class='grid-container'>";

echo "<div class='phpmyadmin-card'>";
echo "<h2><a href='$phpMyAdminUrl' target='_blank'>phpMyAdmin</a></h2>";
echo "<p>Acceso a base de datos de proyectos.</p>";
echo "</div>";

// Determinar el directorio base
$baseDir = $root . ($currentDir ? '/' . $currentDir : '');

// Obtener directorios y archivos en el directorio actual
$directories = array_filter(glob($baseDir . '/*'), 'is_dir');
$files = array_diff(scandir($baseDir), array('..', '.', '.DS_Store')); // Ignorar archivos ocultos

// Mostrar directorios
foreach ($directories as $directory) {
    $folderName = basename($directory);
    echo "<div class='project-card'>";
    echo "<h2><a href='?dir=" . urlencode($currentDir ? $currentDir . '/' . $folderName : $folderName) . "'>$folderName</a></h2>";
    echo "</div>";
}

// Mostrar archivos en el directorio actual
if (!empty($files)) {
    foreach ($files as $file) {
        if (is_file($baseDir . '/' . $file)) {
            echo "<div class='file-card'>";
            echo "<h3><a href='$currentDir/$file' target='_blank'>$file</a></h3>";
            echo "</div>";
        }
    }
} else {
    echo "<p>No hay archivos en este proyecto.</p>";
}

echo "</div>"; 
echo "</body></html>";
