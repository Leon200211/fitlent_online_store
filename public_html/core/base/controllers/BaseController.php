<?php

namespace core\base\controllers;


use core\base\exceptions\RouteException;

abstract class BaseController
{

    protected $page;
    protected $errors;

    protected $controller;  // контроллеры
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;  // параметры


    public function route(){

        $controller = str_replace('/', '\\', $this->controller);


        try {
            // для проверок
            $object = new \ReflectionMethod($controller, 'request');

            $args = [
                'parameters' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];

            $object->invoke(new $controller, $args);
        }catch (\ReflectionException $e){
            throw new RouteException($e->getMessage());
        }

    }




    public function request($args){
        $this->parameters = $args['parameters'];

        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];

        $this->$inputData();

        $this->page = $this->$outputData();


        // логирование ошибок
        if($this->errors){
            $this->writeLog($this->errors);
        }


        $this->getPage();

    }


    // генератор шаблонов
    protected function render($path = '', $parameters = []){

        extract($parameters);


        if(!$path){
            $path = TEMPLATE . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0];
        }



        // работа с буфером обмена
        ob_start();
        if(!@include_once $path . '.php') {
            throw new RouteException('Отсутствует шаблон - ' . $path);
        }
        // возвращаем данные из буфера обмена
        return ob_get_clean();



    }

    // отображение страницы
    protected function getPage(){
        exit($this->page);
    }


}