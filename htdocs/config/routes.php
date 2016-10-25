<?php
/**
 * Пользовательские правила маршрутизации
 */
$routes = array(
    // Путь к каталогу
    '/product/@catalogue' => array(
        'controller' => 'product',
        'catalogue' => '\w+',
    ),
    
    // Путь к товару
    '/product/@catalogue/@id' => array(
        'controller' => 'product',
        'catalogue' => '\w+',
        'action' => 'item',
    ),
    
    // Путь к маркеру
    '/product/marker/@marker' => array(
        'controller' => 'product',
        'marker' => '\w+',
        'action' => 'marker_list',
    ),
    
    // Путь для голосования
    '/product/vote/@id' => array(
        'controller' => 'product',
        'action' => 'vote',
    ),
);
