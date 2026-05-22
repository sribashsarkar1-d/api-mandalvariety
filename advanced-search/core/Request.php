<?php
namespace Core;

class Request {
    public static function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getUri() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Normalize the path by removing the base directory part
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $uri = preg_replace('#^' . preg_quote($scriptDir, '#') . '/?#', '/', $uri);
        return $uri === '' ? '/' : $uri;
    }

    public static function getQuery($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    public static function getJson() {
        return json_decode(file_get_contents("php://input"), true);
    }
}
