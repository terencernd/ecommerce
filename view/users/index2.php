<?php
require_once '../router.php';

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/balls', 'ProductController@index');
$router->get('/cart', 'CartController@index');

$router->run();
