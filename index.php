<?php
//  PAGINA DE INICIO (EXPLORADOR DE PROJECTOS WEB)
$root = __DIR__;

$phpMyAdminUrl = "http://localhost:8080";

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
        .project-card, .phpmyadmin-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .project-card:hover, .phpmyadmin-card:hover {
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

$directories = array_filter(glob($root . '/*'), 'is_dir');

foreach ($directories as $directory) {
    $folderName = basename($directory);
    $files = array_diff(scandir($directory), array('..', '.'));
    echo "<div class='project-card'>";
    echo "<h2><a href='./$folderName'>$folderName</a></h2>";

    if (!empty($files)) {
        echo "<ul class='file-list'>";
        foreach ($files as $file) {
            echo "<li><a href='$folderName/$file' target='_blank'>$file</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No hay archivos en este proyecto.</p>";
    }

    echo "</div>";
}

echo "</div>"; 
echo "</body></html>";
