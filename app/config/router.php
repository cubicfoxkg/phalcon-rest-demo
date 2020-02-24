<?php

$router = $di->getRouter();
// Define your routes here


$router->add(
    '/products',
    [
        'controller' => 'products',
        'action'     => 'index',
    ]
);

$router->add(
    '/product/{id}',
    [
        'controller' => 'products',
        'action'     => 'edit',
    ]
);

$router->add(
    '/rate/{id}',
    [
        'controller' => 'rates',
        'action'     => 'rate',
    ]
);

//Dunno how to get this to work..
//$router->notFound(['controller' => 'index', 'action' => 'notfound']);


$router->handle($_SERVER['REQUEST_URI']);
