<?php


namespace core\base\controllers;


trait Singleton
{

    static private $_instance;

    private function __construct(){

    }

    private function __clone(){

    }

    static public function getInstance(){
        if(self::$_instance instanceof self){  // проверка существует ли уже объект класса
            return self::$_instance;
        }

        return self::$_instance = new self;  // если еще нет объекта, создать
    }

}