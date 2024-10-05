<?php
// index.php
// Funciones PHP para manejar el sistema de archivos
function getDirectoryContents($dir) {
    $contents = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item != "." && $item != ".." && $item[0] != ".") {
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
    
    $renderableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
    $codeExtensions = ['html', 'css', 'js', 'php', 'java', 'py', 'c', 'cpp', 'cs', 'go', 'rb', 'swift', 'ts', 'xml', 'json', 'yaml', 'sql', 'sh', 'bat', 'ps1'];
    
    if (in_array(strtolower($extension), $renderableExtensions)) {
        return [
            'type' => 'renderable',
            'content' => "<iframe src='$path' style='width:100%;height:90vh;border:none;'></iframe>"
        ];
    } elseif (in_array(strtolower($extension), $codeExtensions)) {
        return [
            'type' => 'code',
            'content' => htmlspecialchars($content),
            'language' => $extension
        ];
    } else {
        return [
            'type' => 'text',
            'content' => nl2br(htmlspecialchars($content))
        ];
    }
}

function parseMarkdown($content) {
    // Parser básico de Markdown
    $content = htmlspecialchars($content);
    $content = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $content);
    $content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);
    $content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
    $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
    $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);
    $content = preg_replace('/`(.+?)`/', '<code>$1</code>', $content);
    $content = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $content);
    $content = preg_replace('/(<li>.*<\/li>)/', '<ul>$1</ul>', $content);
    return nl2br($content);
}

// Manejar solicitudes AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    switch ($_GET['action']) {
        case 'getContents':
            echo json_encode(getDirectoryContents($_GET['path']));
            break;
        case 'getFile':
            echo json_encode(getFileContent($_GET['path']));
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
    <title>DAW / DAM Explorer</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚀</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #333333;
            --hover-color: #f0f0f0;
            --border-color: #e0e0e0;
            --sidebar-width: 360px;
            --sidebar-collapsed-width: 60px;
            --accent-color: #4a90e2;
            --secondary-color: #50e3c2;
            --headbar-height: 60px;
        }
        
        .dark-mode {
            --bg-color: #1e1e1e;
            --text-color: #ffffff;
            --hover-color: #2c2c2c;
            --border-color: #3c3c3c;
            --accent-color: #6ab0ff;
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
        
        #headbar {
            display: flex;
            align-items: center;
            height: var(--headbar-height);
            background-color: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
            padding: 0 20px;
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
            padding: 20px;
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
            border-bottom: 1px solid var(--border-color);
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
            height: 90vh;
        }
        
        .tree, .tree ul {
            list-style-type: none;
            padding-left: 20px;
        }
        
        .tree li {
            padding: 8px 0;
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
        
        #mode-toggle, #sidebar-toggle, #copy-button, #view-source-button {
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            font-size: 24px;
            padding: 10px;
            transition: color 0.2s, transform 0.2s;
        }
        
        #mode-toggle:hover, #sidebar-toggle:hover, #copy-button:hover, #view-source-button:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }
        
        #toolbar {
            display: flex;
            justify-content: center;
            padding: 20px 0;
            background-color: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        #search-container {
            position: relative;
            width: 100%;
            max-width: 320px;
        }
        
        #search-bar {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
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
            padding: 15px 10px;
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
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .favorite {
            display: flex;
            align-items: center;
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            word-wrap: break-word;
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
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
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
            top: 100%;
            left: 0;
            right: 0;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-top: none;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        #search-results .result-item {
            padding: 12px 15px;
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
        }
        
        #logo svg {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }
        
        #code-container {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            margin-top: 20px;
            overflow: auto;
            max-height: calc(100vh - 200px);
        }
        
        #code-container pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        #code-container code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div id="headbar">
        <div id="logo">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" fill="var(--accent-color)" />
                <text x="50" y="70" font-size="60" text-anchor="middle" fill="white">🚀</text>
            </svg>
            DAW/DAM
        </div>
        <button id="sidebar-toggle" aria-label="Toggle Sidebar">☰</button>
        <div id="tabs"></div>
        <button id="copy-button" style="display:none;" aria-label="Copy Content">📋</button>
        <button id="view-source-button" style="display:none;" aria-label="View Source">📄</button>
        <button id="mode-toggle" aria-label="Toggle Dark Mode">🌓</button>
    </div>
    <div id="main-container">
        <div id="sidebar">
            <a href="/" id="home-link"><span class="icon">🏠</span><span class="text">Home</span></a>
            <a href="http://localhost:8080" id="db-link" target="_blank"><span class="icon">🗄️</span><span class="text">Database</span></a>
            <div id="toolbar">
                <div id="search-container">
                    <input type="text" id="search-bar" placeholder="Buscar archivos..." aria-label="Search files">
                    <div id="search-results"></div>
                </div>
            </div>
            <div id="favorites">
                <h3>Favoritos</h3>
                <ul id="favorites-list"></ul>
            </div>
            <ul id="file-tree" class="tree"></ul>
            <div id="recent-files">
                <h3>Archivos recientes</h3>
                <ul id="recent-files-list"></ul>
            </div>
        </div>
        <div id="content">
            <iframe name="content-frame" id="content-frame" title="File Content"></iframe>
            <div id="code-container" style="display:none;"></div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const contentFrame = document.getElementById('content-frame');
        const codeContainer = document.getElementById('code-container');
        const fileTree = document.getElementById('file-tree');
        const modeToggle = document.getElementById('mode-toggle');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const searchBar = document.getElementById('search-bar');
        const searchResults = document.getElementById('search-results');
        const tabs = document.getElementById('tabs');
        const copyButton = document.getElementById('copy-button');
        const viewSourceButton = document.getElementById('view-source-button');
        const favoritesList = document.getElementById('favorites-list');
        const recentFilesList = document.getElementById('recent-files-list');

        const MAX_TABS = 5;
        let darkMode = localStorage.getItem('darkMode') === 'true';
        let openTabs = [];
        let currentTab = null;
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        let recentFiles = JSON.parse(localStorage.getItem('recentFiles')) || [];
        let currentViewMode = 'render'; // 'render' or 'source'

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
            let content;
            if (currentViewMode === 'render') {
                content = contentFrame.contentDocument.body.innerText;
            } else {
                content = codeContainer.innerText;
            }
            navigator.clipboard.writeText(content).then(() => {
                showNotification('Contenido copiado al portapapeles');
            });
        });

        viewSourceButton.addEventListener('click', () => {
            if (currentViewMode === 'render') {
                currentViewMode = 'source';
                contentFrame.style.display = 'none';
                codeContainer.style.display = 'block';
                viewSourceButton.textContent = '🖼️';
                viewSourceButton.setAttribute('aria-label', 'View Rendered');
            } else {
                currentViewMode = 'render';
                contentFrame.style.display = 'block';
                codeContainer.style.display = 'none';
                viewSourceButton.textContent = '📄';
                viewSourceButton.setAttribute('aria-label', 'View Source');
            }
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
                    if (data.type === 'renderable') {
                        contentFrame.srcdoc = data.content;
                        contentFrame.style.display = 'block';
                        codeContainer.style.display = 'none';
                        currentViewMode = 'render';
                        viewSourceButton.style.display = 'none';
                    } else if (data.type === 'code') {
                        codeContainer.innerHTML = `<pre><code class="language-${data.language}">${data.content}</code></pre>`;
                        hljs.highlightElement(codeContainer.querySelector('code'));
                        contentFrame.style.display = 'none';
                        codeContainer.style.display = 'block';
                        currentViewMode = 'source';
                        viewSourceButton.style.display = 'inline-block';
                    } else {
                        contentFrame.srcdoc = `<pre>${data.content}</pre>`;
                        contentFrame.style.display = 'block';
                        codeContainer.style.display = 'none';
                        currentViewMode = 'render';
                        viewSourceButton.style.display = 'none';
                    }
                    copyButton.style.display = 'inline-block';
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
                codeContainer.innerHTML = '';
                copyButton.style.display = 'none';
                viewSourceButton.style.display = 'none';
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

        // Implementar arrastrar y soltar para reordenar pestañas
        let dragTab = null;

        tabs.addEventListener('dragstart', (e) => {
            dragTab = e.target;
            e.dataTransfer.setData('text/plain', '');
        });

        tabs.addEventListener('dragover', (e) => {
            e.preventDefault();
            const targetTab = e.target.closest('.tab');
            if (targetTab && targetTab !== dragTab) {
                const rect = targetTab.getBoundingClientRect();
                const midpoint = (rect.left + rect.right) / 2;
                if (e.clientX < midpoint) {
                    targetTab.parentNode.insertBefore(dragTab, targetTab);
                } else {
                    targetTab.parentNode.insertBefore(dragTab, targetTab.nextSibling);
                }
            }
        });

        tabs.addEventListener('dragend', () => {
            dragTab = null;
            openTabs = Array.from(tabs.children).map(tab => {
                return openTabs.find(t => t.path === tab.dataset.path);
            });
        });

        // Teclas de acceso rápido
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                sidebar.classList.toggle('collapsed');
            }
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