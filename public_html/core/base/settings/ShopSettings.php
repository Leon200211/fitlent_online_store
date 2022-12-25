<?php

#/

namespace core\base\settings;

use core\base\controllers\Singleton;
use core\base\settings\Settings;

// настройки плагина Shop
// используется Паттерн проектирования Singleton (Одиночка)
class ShopSettings
{
    use Singleton{
        getInstance as traitGetInstance;
    }

    private $baseSettings;  // для доступа к экземпляру Settings


    static public function getInstance(){
        if(self::$_instance instanceof self){  // проверка существует ли уже объект класса
            return self::$_instance;
        }

        // для доступа к экземпляру Settings
        self::traitGetInstance()->baseSettings = Settings::getInstance();
        // для склейки полей
        $baseProperties = self::$_instance->baseSettings->clueProperties(get_class());
        // для записи значений склейки
        self::$_instance->setProperty($baseProperties);

        return self::$_instance;  // если еще нет объекта, создать
    }

    // геттер для получения данных
    static public function get($property){
        return self::getInstance()->$property;
    }


    // для записи значений после склейки
    protected function setProperty($properties){
        if($properties){
            foreach ($properties as $name => $property){
                $this->$name = $property;
            }
        }
    }



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