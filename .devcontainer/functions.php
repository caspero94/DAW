<?php
// functions.php

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
            return $content;
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