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