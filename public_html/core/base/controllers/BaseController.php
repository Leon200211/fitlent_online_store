<?php

#/

namespace core\base\controllers;


use core\base\exceptions\RouteException;

abstract class BaseController
{
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
            throw new RouteException($e);
        }

    }




    public function request($args){

    }


}