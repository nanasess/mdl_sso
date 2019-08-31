<?php
/** このファイルは html/router.php にコピーして使用してください */
require_once __DIR__.'/require.php';

$routes = require_once __DIR__.'/../data/vendor/nanasess/mdl_sso/routes.php';
$dispatcher = \FastRoute\simpleDispatcher($routes);

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

list($status, $handler, $vars) = $dispatcher->dispatch($httpMethod, $uri);

switch ($status) {
    case FastRoute\Dispatcher::NOT_FOUND:
        if (php_sapi_name() === 'cli-server') {
            return false;
        } else {
            header('HTTP', true, 404);
        }
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        header('HTTP', true, 405);
        break;
    case FastRoute\Dispatcher::FOUND:
        echo $handler($vars);
    default:
}
