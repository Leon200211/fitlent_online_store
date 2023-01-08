<?php

#/

namespace core\base\settings;


// настройки плагина Shop
// используется Паттерн проектирования Singleton (Одиночка)
class ShopSettings
{

    // подключаем базовый трейт для настроек
    use BaseSettings;

    
    // настройки пути
    // Здесь значения из Settings будут заменяться на эти
    private $routes = [
        'plugins' => [
            'path' => 'core/plugins/',
            'hrUrl' => false,
            'dir' => false,
            'routes' => [
            ]
        ]
    ];

    // расширение
    private $expansion = 'core/plugin/expansion/';

    // Здесь значения будут складываться с Settings
    private $templateArr = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

}