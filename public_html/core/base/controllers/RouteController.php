<?php


namespace core\base\controllers;

use core\base\settings\Settings;
use core\base\settings\ShopSettings;


// нужен для разбора адресной строки
// используется Паттерн проектирования Singleton (Одиночка)
// Singleton (Синглтон, одиночка) относиться к классу порождающих паттернов.
// Он используется для создания всего одного экземпляра класса, и гарантирует,
// что во время работы программы не появиться второй. Например в схеме MVC,
// зачастую этот паттерн используется для порождения главного контроллера (фронтового)
class RouteController
{

    static private $_instance;

    private function __clone(){

    }


    static public function getInstance(){
        if(self::$_instance instanceof self){  // проверка существует ли уже объект класса
            return self::$_instance;
        }

        return self::$_instance = new self;  // если еще нет объекта, создать
    }


    private function __construct(){

        $s = Settings::get('routes');
        $s1 = ShopSettings::get('routes');

        exit();
    }




}