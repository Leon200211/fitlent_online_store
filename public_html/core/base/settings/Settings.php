<?php


namespace core\base\settings;


// класс настроек
// используется Паттерн проектирования Singleton (Одиночка)
use core\base\controllers\Singleton;

class Settings
{

    use Singleton;

    // геттер для получения данных
    static public function get($property){
        return self::getInstance()->$property;
    }



    // настройки пути
    private $routes = [
        'admin' => [
            'alias' => 'admin',
            'path' => 'core/admin/controllers/',
            'hrUrl' => false,
            'routes' => [

            ]
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
            'path' => 'core/user/controllers/',
            'hrUrl' => true,
            'routes' => [

            ]
        ],
        'default' => [
            'controller' => 'IndexController',
            'inputMethod' => 'inputData',
            'outputMethod' => 'outputData'
        ]
    ];

    // расширение
    private $expansion = 'core/admin/expansion/';

    private $projectTables = [
        'articles' => ['name' => 'Переводsdf таблицы', 'img' => 'pages.png'],
        'test' => [],
    ];
    private $defaultTable = 'articles';


    private $templateArr = [
        'text' => ['name', 'phone', 'address'],
        'textarea' => ['content', 'keywords']
    ];

    // для перевода названия таблиц
    private $translate = [
        'name' => ['Название', 'Не более 100 символов'],
        'content' => []
    ];

    // для работы с внешними данными, в рамках одной таблицы
    private $rootItems = [
        'name' => 'Корневая',
        'tables' => ['teacher']
    ];

    // для админ панели
    private $blockNeedle = [
        'vg-rows' => [],
        'vg-img' => ['id'],
        'vg-content' => ['content']
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
                if(is_array($value) and @is_array($base[$key])){
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