<?php
use Core\Router;
use Controllers\SearchController;

$router = new Router();

$router->get('/api/search', [SearchController::class, 'globalSearch']);
$router->get('/api/search/products', [SearchController::class, 'productsSearch']);
$router->get('/api/search/categories', [SearchController::class, 'categoriesSearch']);
$router->get('/api/search/suggestions', [SearchController::class, 'suggestions']);
$router->get('/api/search/related', [SearchController::class, 'related']);
$router->get('/api/search/popular', [SearchController::class, 'popular']);
$router->post('/api/search/voice', [SearchController::class, 'voiceSearch']);

return $router;
