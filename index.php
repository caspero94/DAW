<?php
// index.php
// Funciones PHP para manejar el sistema de archivos
function getDirectoryContents($dir) {
    $contents = [];
    $items = scandir($dir);
    $omitFiles = $dir === '.' ? ['index.php', 'README.md', 'NotasParche.md' ,'Biblioteca/README.md'] : [];
    foreach ($items as $item) {
        if ($item != "." && $item != ".." && $item[0] != "." && !in_array($item, $omitFiles)) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $type = is_dir($path) ? 'directory' : 'file';
            $contents[] = [
                'name' => $item,
                'path' => $path,
                'type' => $type
            ];
        }
    }
    return $contents;
}

function getFileContent($path) {
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $content = file_get_contents($path);
    
    switch (strtolower($extension)) {
        case 'md':
            return $content; // Devolvemos el contenido sin procesar, lo procesaremos en el cliente
        case 'txt':
            return '<pre>' . htmlspecialchars($content) . '</pre>';
        case 'json':
            return '<pre>' . formatJson($content) . '</pre>';
        case 'js':
        case 'css':
        case 'html':
        case 'php':
        case 'pdf':
            return "<iframe src='$path' style='width:100%;height:98vh;border:none;'></iframe>";
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return "<img src='$path' style='max-width:100%;max-height:98vh;object-fit:contain;' />";
        default:
            return "No se puede visualizar este tipo de archivo.";
    }
}

function formatJson($content) {
    $decoded = json_decode($content);
    if ($decoded === null) {
        return htmlspecialchars($content);
    }
    return htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

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

function searchFiles($query, $dir) {
    $results = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item != "." && $item != ".." && $item[0] != ".") {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (stripos($item, $query) !== false) {
                $results[] = [
                    'name' => $item,
                    'path' => $path,
                    'type' => is_dir($path) ? 'directory' : 'file'
                ];
            }
            if (is_dir($path)) {
                $results = array_merge($results, searchFiles($query, $path));
            }
        }
    }
    return $results;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAW / DAM</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💻</text></svg>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/2.0.3/marked.min.js"></script>
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #333333;
            --hover-color: #f0f0f0;
            --border-color: #e0e0e0;
            --sidebar-width: 30rem;
            --sidebar-collapsed-width: 60px;
            --accent-color: #e24a57;
            --secondary-color: #50e3c2;
            --headbar-height: 60px;
        }
        
        .dark-mode {
            --bg-color: #1e1e1e;
            --text-color: #ffffff;
            --hover-color: #2c2c2c;
            --border-color: #3c3c3c;
            --accent-color: #e24a57;
            --secondary-color: #50e3c2;
        }
        
        body, html {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            transition: background-color 0.3s, color 0.3s;
        }
        
        h3 {
            margin: 16px 0px 0px 0px;
            text-align: center;
            
        }

        #headbar {
            display: flex;
            align-items: center;
            height: var(--headbar-height);
            background-color: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
            padding: 0 10px;
        }
        
        #main-container {
            display: flex;
            height: calc(100% - var(--headbar-height));
        }
        
        #sidebar {
            width: var(--sidebar-width);
            height: 100%;
            overflow-y: auto;
            border-right: 1px solid var(--border-color);
            padding: 10px;
            box-sizing: border-box;
            transition: width 0.3s, transform 0.3s;
            background-color: var(--bg-color);
        }
        
        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        #sidebar.collapsed #toolbar,
        #sidebar.collapsed #favorites,
        #sidebar.collapsed #file-tree,
        #sidebar.collapsed #recent-files {
            display: none;
        }
        
        #content {
            flex-grow: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        #tabs {
            display: flex;
            overflow-x: auto;
            background-color: var(--bg-color);
            border-bottom: 0px solid var(--border-color);
            flex-grow: 1;
        }
        
        .tab {
            padding: 15px 25px;
            cursor: pointer;
            border-right: 1px solid var(--border-color);
            white-space: nowrap;
            display: flex;
            align-items: center;
            transition: background-color 0.2s, color 0.2s;
            position: relative;
            overflow: hidden;
        }
        
        .tab::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--accent-color);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .tab:hover::before, .tab.active::before {
            transform: scaleX(1);
        }
        
        .tab:hover {
            background-color: var(--hover-color);
        }
        
        .tab.active {
            color: var(--accent-color);
            font-weight: bold;
        }
        
        .tab-close {
            margin-left: 10px;
            cursor: pointer;
            opacity: 0.5;
            transition: opacity 0.2s;
        }
        
        .tab-close:hover {
            opacity: 1;
        }
        
        #content-frame {
            flex-grow: 1;
            border: none;
            background-color: #ffffff;
            height: 95vh;
        }
        
        .tree, .tree ul {
            list-style-type: none;
            padding-left: 10px;
        }
        
        .tree li {
            padding: 6px 0;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            position: relative;
            word-wrap: break-word;
        }
        
        .tree li:hover {
            background-color: var(--hover-color);
            transform: translateX(5px);
        }
        
        .collapsed > ul {
            display: none;
        }
        
        .expanded > ul {
            display: block;
        }
        
        #mode-toggle, #sidebar-toggle, #copy-button, #toggle-view-button {
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            font-size: 24px;
            padding: 10px;
            transition: color 0.2s, transform 0.2s;
        }
        
        #mode-toggle:hover, #sidebar-toggle:hover, #copy-button:hover, #toggle-view-button:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }
        
        #toolbar {
            display: flex;
            justify-content: center;
            padding: 10px 0px 10px 0px;
            background-color: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        #search-container {
            position: relative;
            width: 100%;
            max-width: 600px;
        }
        
        #search-bar {
            width: 100%;
            padding: 8px 0px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: box-shadow 0.3s, border-color 0.3s;
            font-size: 16px;
        }
        
        #search-bar:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--accent-color);
            border-color: var(--accent-color);
        }
        
        #home-link, #db-link {
            display: flex;
            align-items: center;
            padding: 10px 10px;
            text-decoration: none;
            color: var(--text-color);
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s, color 0.2s;
            position: relative;
            overflow: hidden;
        }
        
        #home-link::after, #db-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--secondary-color);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        #home-link:hover::after, #db-link:hover::after {
            transform: scaleX(1);
        }
        
        #home-link:hover, #db-link:hover {
            color: var(--accent-color);
        }
        
        .file-icon {
            margin-right: 10px;
            transition: transform 0.2s;
        }
        
        .directory::before {
            content: '📁';
            margin-right: 10px;
        }
        
        .file::before {
            content: '📄';
            margin-right: 10px;
        }
        
        #favorites {
            margin-bottom: 0px;
            padding-bottom: 0px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .favorite {
            display: flex;
            align-items: center;
            padding: 5px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            word-wrap: break-word;
        }
        #favorites-list {
            padding: 10px;
            margin: 0px;
        }
        
        .favorite:hover {
            background-color: var(--hover-color);
            transform: translateX(5px);
        }
        
        .remove-favorite {
            margin-left: auto;
            color: var(--text-color);
            cursor: pointer;
            opacity: 0.5;
            transition: opacity 0.2s;
        }
        
        .remove-favorite:hover {
            opacity: 1;
        }
        
        #recent-files {
            margin-top: 0px;
            padding-top: 0px;
            border-top: 0px solid var(--border-color);
        }
        
        #recent-files-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        #recent-files-list li {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            word-wrap: break-word;
        }
        
        #recent-files-list li:hover {
            background-color: var(--hover-color);
            transform: translateX(5px);
        }
        
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: var(--accent-color);
            color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: opacity 0.3s, transform 0.3s;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        #search-results {
            position: absolute;
            width: 98%;
            top: 100%;
            left: 0;
            right: 0;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-top: none;
            max-height: 500px;
            overflow-y: auto;
            z-index: 1000;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        #search-results .result-item {
            padding: 10px 12px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        #search-results .result-item:hover {
            background-color: var(--hover-color);
        }
        
        @media (max-width: 768px) {
            #sidebar {
                position: absolute;
                z-index: 1000;
                background-color: var(--bg-color);
            }
            
            #sidebar.collapsed {
                transform: translateX(-100%);
            }
        }
        
        #logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color);
            margin-right: 20px;
            padding-left: 20px;
        }
        
        #logo svg {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }
    </style>
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
            <a href="#" id="home-link" onclick="openTab({name: 'NOVEDADES', path: 'NotasParche.md', type: 'file'}); return false;"><span class="icon">📰</span><span class="text">NOVEDADES</span></a>
            <a href="#" id="home-link" onclick="openTab({name: 'BIBLIOTECA', path: 'Biblioteca/README.md', type: 'file'}); return false;"><span class="icon">🗃️</span><span class="text">BIBLIOTECA</span></a>
            <a href="https://thoth-provides.netlify.app/calendario" id="db-link" target="content-frame"><span class="icon">🎞️</span><span class="text">CLASES GRABADAS</span></a>
            <div id="toolbar">
                <div id="search-container">
                    <input type="text" id="search-bar" placeholder="   Buscar archivos..." aria-label="Search files">
                    <div id="search-results"></div>
                </div>
            </div>
            <div id="favorites">
                <h3>Favoritos</h3></center>
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

    <script>
        const sidebar = document.getElementById('sidebar');
        const contentFrame = document.getElementById('content-frame');
        const fileTree = document.getElementById('file-tree');
        const modeToggle = document.getElementById('mode-toggle');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const searchBar = document.getElementById('search-bar');
        const searchResults = document.getElementById('search-results');
        const tabs = document.getElementById('tabs');
        const copyButton = document.getElementById('copy-button');
        const favoritesList = document.getElementById('favorites-list');
        const recentFilesList = document.getElementById('recent-files-list');

        const MAX_TABS = 5;
        let darkMode = localStorage.getItem('darkMode') === 'true';
        let openTabs = [];
        let currentTab = null;
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        let recentFiles = JSON.parse(localStorage.getItem('recentFiles')) || [];

        function toggleDarkMode() {
            darkMode = !darkMode;
            document.body.classList.toggle('dark-mode', darkMode);
            localStorage.setItem('darkMode', darkMode);
        }

        if (darkMode) {
            document.body.classList.add('dark-mode');
        }

        modeToggle.addEventListener('click', toggleDarkMode);

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            document.querySelectorAll('#home-link .text, #db-link .text').forEach(el => {
                el.style.display = sidebar.classList.contains('collapsed') ? 'none' : 'inline';
            });
        });

        searchBar.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            if (searchTerm.length < 2) {
                searchResults.innerHTML = '';
                return;
            }
            fetch(`?action=search&query=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(results => {
                    searchResults.innerHTML = '';
                    results.forEach(item => {
                        const div = document.createElement('div');
                        div.classList.add('result-item');
                        div.textContent = item.path;
                        div.addEventListener('click', () => {
                            if (item.type === 'directory') {
                                fetchContents(item.path, fileTree);
                            } else {
                                openTab(item);
                            }
                            searchBar.value = '';
                            searchResults.innerHTML = '';
                        });
                        searchResults.appendChild(div);
                    });
                });
        });

        copyButton.addEventListener('click', () => {
            const content = contentFrame.contentDocument.body.innerText;
            navigator.clipboard.writeText(content).then(() => {
                showNotification('Contenido copiado al portapapeles');
            });
        });

        function createTreeNode(item) {
            const li = document.createElement('li');
            li.textContent = item.name;
            li.dataset.path = item.path;
            li.dataset.type = item.type;
            li.classList.add(item.type);
            
            const favoriteButton = document.createElement('span');
            favoriteButton.textContent = '⭐';
            favoriteButton.style.marginLeft = '10px';
            favoriteButton.style.cursor = 'pointer';
            favoriteButton.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleFavorite(item);
            });
            li.appendChild(favoriteButton);
            
            if (item.type === 'directory') {
                li.classList.add('collapsed');
                const ul = document.createElement('ul');
                li.appendChild(ul);
                
                li.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (li.classList.contains('collapsed')) {
                        fetchContents(item.path, ul);
                        li.classList.remove('collapsed');
                        li.classList.add('expanded');
                    } else {
                        li.classList.remove('expanded');
                        li.classList.add('collapsed');
                    }
                });
            } else {
                li.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openTab(item);
                    addToRecentFiles(item);
                });
            }
            
            return li;
        }

        function fetchContents(path, parentElement) {
            fetch(`?action=getContents&path=${encodeURIComponent(path)}`)
                .then(response => response.json())
                .then(data => {
                    parentElement.innerHTML = '';
                    data.forEach(item => {
                        parentElement.appendChild(createTreeNode(item));
                    });
                });
        }

        function fetchFileContent(path) {
            fetch(`?action=getFile&path=${encodeURIComponent(path)}`)
                .then(response => response.json())
                .then(data => {
                    if (path.endsWith('.md')) {
                        contentFrame.srcdoc = marked(data.content);
                    } else {
                        contentFrame.srcdoc = data.content;
                    }
                    copyButton.style.display = 'block';
                });
        }

        function openTab(item) {
            if (!openTabs.some(tab => tab.path === item.path)) {
                if (openTabs.length >= MAX_TABS) {
                    closeTab(openTabs[0]);
                }
                openTabs.push(item);
            }
            currentTab = item.path;
            renderTabs();
            fetchFileContent(item.path);
        }

        function renderTabs() {
            tabs.innerHTML = '';
            openTabs.forEach(tab => {
                const tabElement = document.createElement('div');
                tabElement.classList.add('tab');
                if (tab.path === currentTab) {
                    tabElement.classList.add('active');
                }
                tabElement.textContent = tab.name;
                tabElement.addEventListener('click', () => {
                    currentTab = tab.path;
                    renderTabs();
                    fetchFileContent(tab.path);
                });
                const closeButton = document.createElement('span');
                closeButton.textContent = '×';
                closeButton.classList.add('tab-close');
                closeButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeTab(tab);
                });
                tabElement.appendChild(closeButton);
                tabs.appendChild(tabElement);
            });
        }

        function closeTab(tab) {
            openTabs = openTabs.filter(t => t.path !== tab.path);
            if (currentTab === tab.path) {
                currentTab = openTabs.length > 0 ? openTabs[openTabs.length - 1].path : null;
            }
            renderTabs();
            if (currentTab) {
                fetchFileContent(currentTab);
            } else {
                contentFrame.srcdoc = '';
                copyButton.style.display = 'none';
            }
        }

        function toggleFavorite(item) {
            const index = favorites.findIndex(fav => fav.path === item.path);
            if (index === -1) {
                favorites.push(item);
            } else {
                favorites.splice(index, 1);
            }
            localStorage.setItem('favorites', JSON.stringify(favorites));
            renderFavorites();
        }

        function renderFavorites() {
            favoritesList.innerHTML = '';
            favorites.forEach(fav => {
                const li = document.createElement('li');
                li.classList.add('favorite');
                li.textContent = fav.name;
                li.addEventListener('click', () => {
                    if (fav.type === 'directory') {
                        fetchContents(fav.path, fileTree);
                    } else {
                        openTab(fav);
                    }
                });
                const removeButton = document.createElement('span');
                removeButton.textContent = '❌';
                removeButton.classList.add('remove-favorite');
                removeButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleFavorite(fav);
                });
                li.appendChild(removeButton);
                favoritesList.appendChild(li);
            });
        }

        function addToRecentFiles(item) {
            recentFiles = recentFiles.filter(file => file.path !== item.path);
            recentFiles.unshift(item);
            if (recentFiles.length > 5) {
                recentFiles.pop();
            }
            localStorage.setItem('recentFiles', JSON.stringify(recentFiles));
            renderRecentFiles();
        }

        function renderRecentFiles() {
            recentFilesList.innerHTML = '';
            recentFiles.forEach(file => {
                const li = document.createElement('li');
                li.classList.add(file.type);
                li.textContent = file.name;
                li.addEventListener('click', () => openTab(file));
                recentFilesList.appendChild(li);
            });
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.classList.add('notification');
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Inicializar el árbol de archivos
        fetchContents('.', fileTree);
        renderFavorites();
        renderRecentFiles();

        // Abrir pestañas por defecto al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            openTab({name: 'BIBLIOTECA', path: 'Biblioteca/README.md', type: 'file'});
            openTab({name: 'NOVEDADES', path: 'NotasParche.md', type: 'file'});
            openTab({name: 'INFORMACIÓN', path: 'README.md', type: 'file'});
        });

        // Mejorar la accesibilidad
        document.querySelectorAll('button, a').forEach(el => {
            if (!el.getAttribute('aria-label')) {
                el.setAttribute('aria-label', el.textContent);
            }
        });

        // Ocultar texto de accesos directos cuando la barra lateral está colapsada
        document.querySelectorAll('#home-link .text, #db-link .text').forEach(el => {
            el.style.display = sidebar.classList.contains('collapsed') ? 'none' : 'inline';
        });
    </script>
</body>
</html>
