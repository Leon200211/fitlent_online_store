<?php


namespace core\base\settings;


// класс настроек
// используется Паттерн проектирования Singleton (Одиночка)
class Settings
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


    // геттер для получения данных
    static public function get($property){
        return self::getInstance()->$property;
    }



    // настройки пути
    private $routes = [
        'admin' => [
            'alias' => 'admin',
            'path' => 'core/admin/controllers/',
            'hrUrl' => false
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins' => [
            'path' => 'core/plugins/',
            'hrUrl' => false,
            'dir' => false
        ],
        'user' => [
            'path' => 'core/user/controllers',
            'hrUrl' => true,
            'routes' => [
                'catalog' => 'site'
            ]
        ],
        'default' => [
            'controller' => 'IndexController',
            'inputMethod' => 'inputData',
            'outputMethod' => 'outputData'
        ]
    ];


    private $templateArr = [
        'text' => ['name', 'phone', 'address'],
        'textarea' => ['content', 'keywords']
    ];


    // для склейки полей
    public function clueProperties($class){
        $baseProperties = [];

        foreach ($this as $name => $item) {
            $property = $class::get($name);

            if(is_array($property) and is_array($item)){
                //$baseProperties[$name] = array_merge_recursive($this->$name, $property); не подойдет для многомерных массивов
                //$baseProperties[$name] = array_replace_recursive($this->$name, $property); Заменяет везде значения, не подходит
                // поэтому придется написать свой метод arrayMergeRecursive
                $baseProperties[$name] = $this->arrayMergeRecursive($this->$name, $property);
                continue;
            }

            if(!$property){
                $baseProperties[$name] = $this->$name;
            }
        }

        return $baseProperties;

    }


    // Метод для склейки массивов с заменой значения по текстовому ключу
    public function arrayMergeRecursive(){

        $arrays = func_get_args();  // получение аргументов функции

        $base = array_shift($arrays);  // возвращает первый элемент массива и удаляет его из исходного массива

        foreach ($arrays as $array){
            foreach ($array as $key => $value){
                if(is_array($value) and is_array($base[$key])){
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                }else{
                    if(is_int($key)){
                        // склейка
                        // если еще нет такого элемента, то добавим его
                        if(!in_array($value, $base)){
                            array_push($base, $value);
                        }
                        continue;
                    }
                    // замена
                    $base[$key] = $value;
                }
            }
        }
        return $base;

    }


}