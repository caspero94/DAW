<?php
// index.php

require_once '.devcontainer/functions.php';

// Manejar solicitudes AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    switch ($_GET['action']) {
        case 'getContents':
            echo json_encode(getDirectoryContents($_GET['path']));
            break;
        case 'getFile':
            echo json_encode(['content' => getFileContent($_GET['path'])]);
            break;
        case 'search':
            $results = searchFiles($_GET['query'], '.');
            echo json_encode($results);
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAW / DAM</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💻</text></svg>">
    <link rel="stylesheet" href=".devcontainer/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/2.0.3/marked.min.js"></script>
</head>
<body>
    <div id="headbar">
        <button id="sidebar-toggle" aria-label="Toggle Sidebar">☰</button>
        <div id="logo">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" fill="var(--accent-color)" />
                <text x="50" y="70" font-size="60" text-anchor="middle" fill="white">💻</text>
            </svg>
            DAW/DAM
        </div>
        
        <div id="tabs"></div>
        <button id="copy-button" style="display:none;" aria-label="Copy Content">📋</button>
        <button id="mode-toggle" aria-label="Toggle Dark Mode">🌓</button>
    </div>
    <div id="main-container">
        <div id="sidebar">
            <a href="/" id="home-link"><span class="icon">🏠</span><span class="text">INICIO</span></a>
            <a href="http://localhost:8080" id="db-link" target="_blank"><span class="icon">🗄️</span><span class="text">BASE DE DATOS</span></a>
            <a href="#" id="home-link" onclick="openTab({name: 'INFORMACIÓN', path: 'README.md', type: 'file'}); return false;"><span class="icon">📖</span><span class="text">INFORMACIÓN</span></a>
            <a href="#" id="home-link" onclick="openTab({name: 'NOVEDADES', path:  'NotasParche.md', type: 'file'}); return false;"><span class="icon">📰</span><span class="text">NOVEDADES</span></a>
            <a href="#" id="home-link" onclick="openTab({name: 'BIBLIOTECA', path: 'Biblioteca/README.md', type: 'file'}); return false;"><span class="icon">🗃️</span><span class="text">BIBLIOTECA</span></a>
            <a href="#" id="classes-link"><span class="icon">🎥</span><span class="text">CLASES GRABADAS</span></a>
            <div id="classes-submenu" class="submenu"></div>
            <div id="toolbar">
                <div id="search-container">
                    <input type="text" id="search-bar" placeholder="   Buscar archivos..." aria-label="Search files">
                    <div id="search-results"></div>
                </div>
            </div>
            <div id="favorites">
                <h3>Favoritos</h3>
                <ul id="favorites-list"></ul>
            </div>
            <div id="favorites">
                <h3>Explorador</h3>
                <ul id="file-tree" class="tree"></ul>
            </div>
            <div id="recent-files">
                <h3>Archivos recientes</h3>
                <ul id="recent-files-list"></ul>
            </div>
        </div>
        <div id="content">
            <iframe name="content-frame" id="content-frame" title="File Content"></iframe>
        </div>
    </div>

    <script src=".devcontainer/script.js"></script>
</body>
</html>
