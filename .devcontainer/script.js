// script.js

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
let videoStates = {};

function toggleDarkMode() {
    darkMode = !darkMode;
    document.body.classList.toggle('dark-mode', darkMode);
    localStorage.setItem('darkMode', darkMode);
}

function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
    document.querySelectorAll('#home-link .text, #db-link .text').forEach(el => {
        el.style.display = sidebar.classList.contains('collapsed') ? 'none' : 'inline';
    });
}

function handleSearch(e) {
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
}

function copyContent() {
    const content = contentFrame.contentDocument.body.innerText;
    navigator.clipboard.writeText(content).then(() => {
        showNotification('Contenido copiado al portapapeles');
    });
}

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
    if (path.startsWith('https://drive.google.com/file')) {
        const videoId = path.split('/')[5];
        const savedTime = videoStates[videoId] || 0;
        contentFrame.srcdoc = `
            <html>
                <head>
                    <style>
                        body { margin: 0; padding: 0; overflow: hidden; }
                        iframe { border: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
                    </style>
                </head>
                <body>
                    <iframe src="https://drive.google.com/file/d/${videoId}/preview?start=${savedTime}" 
                            width="100%" height="100%" 
                            allow="autoplay" 
                            id="videoFrame">
                    </iframe>
                    <script>
                        window.addEventListener('message', function(event) {
                            if (event.origin !== 'https://drive.google.com') return;
                            if (event.data.type === 'videoTime') {
                                window.parent.postMessage({type: 'videoTimeUpdate', videoId: '${videoId}', time: event.data.time}, '*');
                            }
                        });
                        
                        // Suprimir errores de CSP
                        console.error = console.warn = function() {};
                    </script>
                </body>
            </html>`;
        copyButton.style.display = 'none';
    } else if (path.startsWith('https://www.youtube.com/') || path.startsWith('https://youtu.be/')) {
        contentFrame.srcdoc = `
            <html>
                <head>
                    <style>
                        body { margin: 0; padding: 0; overflow: hidden; }
                        iframe { border: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
                    </style>
                </head>
                <body>
                    <iframe src="${path}" 
                            width="100%" height="100%" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
                    </iframe>
                </body>
            </html>`;
        copyButton.style.display = 'none';
    } else {
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
}

function loadClassesSubmenu() {
    fetch('.devcontainer/historial_clase.json')
        .then(response => response.json())
        .then(data => {
            const submenu = document.getElementById('classes-submenu');
            submenu.innerHTML = '';
            data.asignaturas.forEach(asignatura => {
                const asignaturaElement = document.createElement('div');
                asignaturaElement.classList.add('asignatura');
                asignaturaElement.textContent = asignatura.nombre;
                const clasesElement = document.createElement('div');
                clasesElement.classList.add('submenu');
                asignaturaElement.addEventListener('click', () => {
                    clasesElement.classList.toggle('active');
                });
                submenu.appendChild(asignaturaElement);

                asignatura.clases.forEach(clase => {
                    const claseElement = document.createElement('div');
                    claseElement.classList.add('clase');
                    claseElement.textContent = `${clase.nombre} (${clase.fecha})`;
                    claseElement.addEventListener('click', () => {
                        openClassTab(clase);
                    });
                    clasesElement.appendChild(claseElement);
                });
                submenu.appendChild(clasesElement);
            });
        });
}

function openClassTab(clase) {
    openTab({
        name: `${clase.nombre} (${clase.fecha})`,
        path: clase.link,
        type: 'video'
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
        const li =  document.createElement('li');
        li.classList.add('favorite');
        li.textContent = fav.name;
        li.addEventListener('click', () => openTab(fav));
        const removeButton = document.createElement('span');
        removeButton.textContent = '×';
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
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Event listeners
if (darkMode) {
    document.body.classList.add('dark-mode');
}

modeToggle.addEventListener('click', toggleDarkMode);
sidebarToggle.addEventListener('click', toggleSidebar);
searchBar.addEventListener('input', handleSearch);
copyButton.addEventListener('click', copyContent);

// Agregar un event listener para recibir mensajes del iframe
window.addEventListener('message', function(event) {
    if (event.data.type === 'videoTimeUpdate') {
        videoStates[event.data.videoId] = event.data.time;
    }
});

fetchContents('.', fileTree);
renderFavorites();
renderRecentFiles();
loadClassesSubmenu();

document.getElementById('classes-link').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('classes-submenu').classList.toggle('active');
});
