<?php
// PAGINA DE INICIO (EXPLORADOR DE PROJECTOS WEB)
$root = __DIR__;

$phpMyAdminUrl = "http://localhost:8080";

// Obtener el directorio actual desde la URL
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';
$fileToOpen = isset($_GET['file']) ? $_GET['file'] : '';

// Función básica para convertir Markdown a HTML
function convertMarkdownToHtml($markdown) {
    // Convertir títulos
    $markdown = preg_replace('/\#\#\# (.*)/', '<h3>$1</h3>', $markdown);
    $markdown = preg_replace('/\#\# (.*)/', '<h2>$1</h2>', $markdown);
    $markdown = preg_replace('/\# (.*)/', '<h1>$1</h1>', $markdown);

    // Convertir negritas y cursivas
    $markdown = preg_replace('/\*\*(.*)\*\*/', '<strong>$1</strong>', $markdown);
    $markdown = preg_replace('/\*(.*)\*/', '<em>$1</em>', $markdown);

    // Convertir listas
    $markdown = preg_replace('/\n\* (.*)/', '<li>$1</li>', $markdown);
    $markdown = preg_replace('/(<li>.*<\/li>)/', '<ul>$1</ul>', $markdown);

    // Convertir enlaces
    $markdown = preg_replace('/\[(.*)\]\((.*)\)/', '<a href="$2">$1</a>', $markdown);

    return $markdown;
}

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
            word-wrap: break-word;
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
        /* Ajustar el tamaño de letra según el ancho del grid */
        h2, h3 {
            font-size: clamp(14px, 2.5vw, 20px);
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <h1>Explorador de Proyectos PHP</h1>";

// Si se selecciona un archivo, verificar si es .md
if ($fileToOpen && substr($fileToOpen, -3) === '.md') {
    // Leer el archivo Markdown
    $filePath = $root . '/' . $currentDir . '/' . $fileToOpen;
    if (file_exists($filePath)) {
        $markdownContent = file_get_contents($filePath);
        // Convertir el contenido Markdown a HTML
        $htmlContent = convertMarkdownToHtml($markdownContent);
        echo "<div style='padding: 20px; background-color: white; border: 1px solid #ddd; margin: 20px;'>";
        echo $htmlContent;
        echo "</div>";
    } else {
        echo "<p>El archivo no existe.</p>";
    }
} else {
    echo "<div class='grid-container'>";

    echo "<div class='phpmyadmin-card'>";
    echo "<h2><a href='$phpMyAdminUrl' target='_blank'>Base de datos MySQL</a></h2>";
    echo "</div>";

    // Determinar el directorio base
    $baseDir = $root . ($currentDir ? '/' . $currentDir : '');

    // Obtener directorios y archivos en el directorio actual
    $directories = array_filter(glob($baseDir . '/*'), 'is_dir');
    $files = array_diff(scandir($baseDir), array('..', '.', '.DS_Store', 'index.php')); // Ignorar index.php en nivel raíz

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
                $fileLink = "?dir=" . urlencode($currentDir) . "&file=" . urlencode($file);
                echo "<div class='file-card'>";
                echo "<h3><a href='$fileLink' target='_blank'>$file</a></h3>";
                echo "</div>";
            }
        }
    } else {
        echo "<p>No hay archivos en este proyecto.</p>";
    }

    echo "</div>";
}

echo "</body></html>";
