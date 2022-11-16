<?php
#/

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;


// НУЖЕН ДЛЯ РАЗБОРА АДРЕСНОЙ СТРОКИ
// используется Паттерн проектирования Singleton (Одиночка)
// Singleton (Синглтон, одиночка) относиться к классу порождающих паттернов.
// Он используется для создания всего одного экземпляра класса, и гарантирует,
// что во время работы программы не появиться второй. Например в схеме MVC,
// зачастую этот паттерн используется для порождения главного контроллера (фронтового)
class RouteController extends BaseController
{

    use Singleton;

    protected $routes; // маршруты


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
                throw new RouteException('Отсутствуют маршруты в базовых настройках', 1);
            }

            // обрезаем адресную строку и разбиваем путь
            $url = explode('/', substr($address_str, strlen(PATH)));

            // проверка на вход в админку
            // если сразу после корня сайта идет попытка входа в админ панель
            if(!empty($url[0]) and $url[0] === $this->routes['admin']['alias']){

                /* Админка */

                // обрезаем адресную строку и разбиваем путь
                // после корень/admin/ ...
                //$url = explode('/', substr($address_str, strlen(PATH . $this->routes['admin']['alias']) + 1));

                array_shift($url); // удаляем нулевой элемент 'ключевое слово для админа'

                // проверка на обращение к плагину

                if(@$url[0] and is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . @$this->routes['plugins']['path'] . @$url[0])){

                    $plugin = array_shift($url); // array_shift() извлекает первое значение массива и удаляет его из массива

                    // проверка есть ли настройки конкретно для этого плагина
                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin . 'Settings'); // ucfirst — Преобразует первый символ строки в верхний регистр
                    if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')){
                        $pluginSettings = str_replace('/', '\\' , $pluginSettings);
                        // имя класса плагина $pluginSettings
                        $this->routes = $pluginSettings::get('routes');
                    }

                    // если есть директория записываем в нее путь
                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
                    // защита против использования некорректных данных
                    $dir = str_replace('//', '/', $dir);


                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;
                    $hrUrl = $this->routes['plugins']['hrUrl'];  // human readable url
                    $route = 'plugins';

                }else{ // если это не плагин
                    $this->controller = $this->routes['admin']['path']; // путь к админ контроллеру
                    $hrUrl = $this->routes['admin']['hrUrl'];  // human readable url
                    $route = 'admin';
                }



            }else{

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path']; // human readable url

                $route = 'user';
            }

            // Метод для создания маршрута
            $this->createRoute($route, $url);


            // работа с алиасами
            if(@$url[1]){
                $count = count($url);
                $key = '';

                if(!$hrUrl){
                    $i = 1;
                }else{
                    $this->parameters['alias'] = $url[1];
                    $i = 2;
                }

                // для записи аргументов по принципу ключ => значение [id => 1] в url id/1
                for( ; $i < $count; $i++){
                    if(!$key){
                        $key = $url[$i];
                        $this->parameters[$key] = '';
                    }else{
                        $this->parameters[$key] = $url[$i];
                        $key = '';
                    }
                }

            }
        }else{
            throw new RouteException("Не корректная директория сайта", 1);
        }
    }


    // Метод для создания маршрута
    private function createRoute($var, $arr){
        $route = [];

        if(!empty($arr[0])){
            // проверка на алиасы  маршрутов
            if(@$this->routes[$var]['routes'][$arr[0]]){
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);
                $this->controller .= ucfirst($route[0].'Controller');
            } else { // если маршрут не описан, но есть контроллер
                $this->controller .= ucfirst($arr[0].'Controller');
            }
        } else{  // если массив пустой пользуемся дефолтным значением
            $this->controller .= $this->routes['default']['controller'];
        }

        // если есть значения, то используем их, если их нет, то используем значения по умолчанию
        @$this->inputMethod = @$route[1] ? @$route[1] : @$this->routes['default']['inputMethod'];
        @$this->outputMethod = @$route[2] ? @$route[2] : @$this->routes['default']['outputMethod'];

        return;
    }



}