<?php


namespace core\admin\controllers;

use core\admin\models\Model;
use core\base\controllers\BaseController;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;


// базовый контроллер для админки
abstract class BaseAdmin extends BaseController
{

    protected $model;  // для обращения к моделям

    protected $table;
    protected $columns;
    protected $data;

    protected $menu;  // меню для админ панели
    protected $title;  // title для страницы



    protected function inputData(){

        $this->init(true);  // настраиваем internal_settings.php для админа

        $this->title = 'Fitlent engine';

        if(!$this->model){
            $this->model = Model::getInstance();
        }
        if(!$this->menu){
            $this->menu = Settings::get('projectTables');
        }

        // запрет на кеширование админки
        $this->sendNoCacheHeaders();

    }

    protected function outputData(){

    }


    // запрет на кеширование админки
    protected function sendNoCacheHeaders(){

        @header("Last-Modified: " . gmdate("D, d m Y H:i:s") . " GMT");
        @header("Cache-Control: no-cache, mush-revalidate");
        @header("Cache-Control: max-age=0");
        @header("Cache-Control: post-check=0,pre-check=0");  // for explore only

    }


    protected function execBase(){
        self::inputData();
    }


    protected  function createTableData(){

        if(!$this->table){
            if($this->parameters){
                $this->table = array_keys($this->parameters)[0];
            }else{
                $this->table = Settings::get('defaultTable');
            }
        }

        // вернет список всех полей из таблицы
        $this->columns = $this->model->showColumns($this->table);

        // если не пришли данные
        if(!$this->columns) new RouteException('Не найдены поля в таблице - ' . $this->table, 2);

    }


    /**
     * @param array $arr
     * @param bool $add если true и пришел arr, то добавить это к основному запросу
     */
    // получаем данные из БД
    protected function createData($arr = [], $add = true){

        $fields = [];
        $order = [];
        $order_direction = [];

        if($add){
            if(!$this->columns['id_row']) return $this->data = [];

            $fields[] = $this->columns['id_row'] . ' as id';

            if($this->columns['name']) $fields['name'] = 'name';
            if($this->columns['img']) $fields['img'] = 'img';

            if(count($fields) < 3){
                foreach ($this->columns as $key => $item){
                    if(!$fields['name'] and strpos($key, 'name') !== false){
                        $fields['name'] = $key . ' as name';
                    }
                    // если в бд название строго начинается с "img"
                    if(!$fields['img'] and strpos($key, 'img') === 0){
                        $fields['img'] = $key . ' as img';
                    }
                }
            }

            // склеиваем массивы
            if($arr['fields']){
                if(is_array($arr['fields'])){
                    $fields = Settings::getInstance()->arrayMergeRecursive($fields, $arr['fields']);
                }else{
                    $fields[] = $arr['fields'];
                }
            }


            // сортировка элементов в панели
            if($this->columns['parent_id']){
                if(!in_array('parent_id', $fields)){
                    $fields[] = 'parent_id';
                }
                $order[] = 'parent_id';
            }
            if($this->columns['menu_position']){
                $order[] = 'menu_position';
            }else if($this->columns['date']){
                if($order){
                    $order_direction = ['ASC', 'DESC'];
                }else{
                    $order_direction = ['DESC'];
                }
                $order[] = 'date';
            }


            // склеиваем массивы
            if(isset($arr['order'])){
                if(is_array($arr['order'])) {
                    $order = Settings::getInstance()->arrayMergeRecursive($order, $arr['order']);
                }else{
                    $order[] = $arr['order'];
                }
            }
            if(isset($arr['order_direction'])){
                if(is_array($arr['order_direction'])) {
                    $order_direction = Settings::getInstance()->arrayMergeRecursive($order_direction, $arr['order_direction']);
                }else{
                    $order_direction[] = $arr['order_direction'];
                }
            }


        }else{
            if(!$arr){
                return $this->data = [];
            }

            $fields = $arr['fields'];
            $order = $arr['order'];
            $order_direction = ['order_direction'];

        }


        // проверки
        $this->data = $this->model->read($this->table,[
            'fields' => $fields,
            'order' => $order,
            'order_direction' => $order_direction
        ]);

    }


    // расширение для нашего фреймворка
    protected function expansion($args = []){

        // на всякий случай проверяем на наличие _ в названии таблицы
        $filename = explode('_', $this->table);
        $className = '';

        // Создаем имя для класс в нормализованном формате
        foreach ($filename as $item) $className .= ucfirst($item);

        $class = Settings::get('expansion') . $className . 'Expansion';

        if(is_readable($_SERVER['DOCUMENT_ROOT'] . PATH . $class . '.php')){

            $class = str_replace('/', '\\', $class);

            
            $exp = $class::getInstance();
            $res = $exp->expansion($args);

        }

    }

}