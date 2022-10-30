<?php


namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;


// НУЖЕН ДЛЯ РАЗБОРА АДРЕСНОЙ СТРОКИ
// используется Паттерн проектирования Singleton (Одиночка)
// Singleton (Синглтон, одиночка) относиться к классу порождающих паттернов.
// Он используется для создания всего одного экземпляра класса, и гарантирует,
// что во время работы программы не появиться второй. Например в схеме MVC,
// зачастую этот паттерн используется для порождения главного контроллера (фронтового)
class RouteController
{

    static private $_instance;

    protected $routes; // маршруты


    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;


    private function __clone(){

    }


    static public function getInstance(){
        if(self::$_instance instanceof self){  // проверка существует ли уже объект класса
            return self::$_instance;
        }

        return self::$_instance = new self;  // если еще нет объекта, создать
    }


    private function __construct(){

        $address_str = $_SERVER['REQUEST_URI'];

        // если символ / стоит в конце строки, то перенаправляем пользователя на страницу без этого /
        if(strrpos($address_str, '/') === strlen($address_str) - 1 and strrpos($address_str, '/') !== 0){
            $this->redirect(rtrim($address_str, '/'), 301);
        }

        $path = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], 'index.php'));

        // PATH - константа из config.php
        if($path === PATH){

            // получаем данные из класса Settings с помощью геттера
            // это нежно чтобы знать пути к частям сайта
            $this->routes = Settings::get('routes');
            // если мы не получили данные
            if(!$this->routes){
                throw new RouteException('Сайт находится на тех обслуживание!');
            }

            // проверка на вход в админку
            // если сразу после корня сайта идет попытка входа в админ панель
            if(strrpos($address_str, $this->routes['admin']['alias']) === strlen(PATH)){
                /* Админка */
            }else{
                // обрезаем адресную строку и разбиваем путь
                $urs = explode('/', substr($address_str, strlen(PATH)));

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            // Метод для создания маршрута
            $this->createRoute($route, $urs);


            exit();

        }else{
            try{
                throw new \Exception("Не корректная директория сайта");
            }catch (\Exception $e){
                exit($e->getMessage());
            }
        }
    }


    // Метод для создания маршрута
    private function createRoute($var, $arr){
        $route = [];

        if(!empty($arr[0])){
            // проверка на алиасы  маршрутов
            if($this->routes[$var]['routes'][$arr[0]]){
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);
                $this->controller .= ucfirst($route[0].'Controller');
            } else { // если маршрут не описан, но есть контроллер
                $this->controller .= ucfirst($arr[0].'Controller');
            }
        } else{  // если массив пустой пользуемся дефолтным значением
            $this->controller .= $this->routes['default']['controller'];
        }

        // если есть значения, то используем их, если их нет, то используем значения по умолчанию
        $this->inputMethod = $route[1] ? $route[1] : $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ? $route[2] : $this->routes['default']['outputMethod'];

        return;
    }



}