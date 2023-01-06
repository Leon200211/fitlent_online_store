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
    protected $foreignData;

    protected $adminPath;

    protected $menu;  // меню для админ панели
    protected $title;  // title для страницы

    protected $translate;
    protected $blocks = [];

    protected $templateArr;
    protected $formTemplates;

    // разрешение на удаление
    protected $noDelete;

    protected function inputData(){

        $this->init(true);  // настраиваем internal_settings.php для админа

        $this->title = 'Fitlent engine';

        if(!$this->model) $this->model = Model::getInstance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');
        if(!$this->adminPath) $this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';


        if(!$this->templateArr) $this->templateArr = Settings::get('templateArr');
        if(!$this->formTemplates) $this->formTemplates = Settings::get('formTemplates');

        // запрет на кеширование админки
        $this->sendNoCacheHeaders();

    }


    // вывод шаблона
    protected function outputData(){

        if(!$this->content){
            $args = func_get_arg(0);
            $vars = $args ? $args : [];

            // доп проверка, можно убрать так как есть render в BaseController
            //if(!$this->template) $this->template = ADMIN_TEMPLATE . 'show';

            $this->content = $this->render($this->template, $vars);
        }

        $this->header = $this->render(ADMIN_TEMPLATE . 'include/header');
        $this->footer = $this->render(ADMIN_TEMPLATE . 'include/footer');

        return $this->render(ADMIN_TEMPLATE . 'layout/default');
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


    protected  function createTableData($settings = false){

        if(!$this->table){
            if($this->parameters){
                $this->table = array_keys($this->parameters)[0];
            }else{
                if(!$settings){
                    $settings = Settings::getInstance();
                }
                $this->table = Settings::get('defaultTable');
            }
        }

        // вернет список всех полей из таблицы
        $this->columns = $this->model->showColumns($this->table);

        // если не пришли данные
        if(!$this->columns) new RouteException('Не найдены поля в таблице - ' . $this->table, 2);

    }





    // расширение для нашего фреймворка
    protected function expansion($args = [], $settings = false){

        // на всякий случай проверяем на наличие _ в названии таблицы
        $filename = explode('_', $this->table);
        $className = '';

        // Создаем имя для класс в нормализованном формате
        foreach ($filename as $item) $className .= ucfirst($item);


        if(!$settings){
            $path = Settings::get('expansion');
        }elseif(is_object($settings)){
            $path = $settings::get('expansion');
        }else{
            $path = $settings;
        }

        $class = $path . $className . 'Expansion';


        if(is_readable($_SERVER['DOCUMENT_ROOT'] . PATH . $class . '.php')){

            $class = str_replace('/', '\\', $class);

            $exp = $class::getInstance();


            // динамическое создание свойств у объекта
            foreach ($this as $name => $value) {
                $exp->$name = &$this->$name;
            }

            return $exp->expansion($args);

        }else{

            $file = $_SERVER['DOCUMENT_ROOT'] . PATH . $path . $this->table . '.php';

            extract($args);

            if(is_readable($file)){
                return include $file;
            }

        }

        return false;

    }


    // разбор колонок на блоки
    protected function createOutputData($settings = false){

        if(!$settings) $settings = Settings::getInstance();

        $blocks = $settings::get('blockNeedle');
        $this->translate = $settings::get('translate');

        if(!$blocks or !is_array($blocks)){

            foreach ($this->columns as $name => $item){
                // если айдишник, то пропускаем
                if($name === 'id_row') continue;

                if(!$this->translate[$name]) $this->translate[$name][] = $name;

                $this->blocks[0][] = $name;
            }

            return;

        }else{

            // определение дефолтного блока
            $default = array_keys($blocks)[0];

            foreach ($this->columns as $name => $item) {
                // если айдишник, то пропускаем
                if($name === 'id_row') continue;

                // проверяем, произошла ли вставка
                $insert = false;
                foreach ($blocks as $block => $value){
                    if(!array_key_exists($block, $this->blocks)){
                        $this->blocks[$block] = [];
                    }

                    // если произошла вставка
                    if(in_array($name, $value)){
                        $this->blocks[$block][] = $name;
                        $insert = true;

                        break;
                    }
                }

                if(!$insert) $this->blocks[$default][] = $name;
                if(!$this->translate[$name]) $this->translate[$name][] = $name;

            }

        }

        return;

    }


    // метод для работы с radio
    protected function createRadio($settings = false){

        if(!$settings) $settings = Settings::getInstance();

        $radio = $settings::get('radio');

        if($radio){
            foreach ($this->columns as $name => $item){
                if($radio[$name]){
                    $this->foreignData[$name] = $radio[$name];
                }
            }
        }

    }


    // Работа с данными из Post
    protected function checkPost($settings = false){

        // если метод Post
        if($this->isPost()){

            // валидация данных

            $this->clearPostFields($settings);
            $this->table = $this->clearStr($_POST['table']);
            unset($_POST['table']);

            // проверяем пришла ли таблица
            if(isset($this->table)){
                $this->createTableData($settings);

                // редактирование или добавление новых данных
                $this->editData();

            }

        }


    }


    // очищение полученных полей из Post
    protected function clearPostFields($settings, &$arr = []){

        // в случае ошибки валидации, будет происходить редирект

        if(!$arr){
            // ссылка на суперглобальный массив POST
            $arr = &$_POST;
        }
        if(!$settings) $settings = Settings::getInstance();

        // идентификатор
        $id = $_POST[$this->columns['id_row']] ?: false;

        $validation = $settings::get('validation');
        if(!$this->translate) $this->translate = $settings::get('translate');


        // проходимся по всем данным
        foreach ($arr as $key => $item){

            if(is_array($item)){
                $this->clearPostFields($settings, $item);
            }else{
                // если пришел числовой код
                if(is_numeric($item)){
                    $arr[$key] = $this->clearNum($item);
                }

                // начало валидации
                if(isset($validation)){

                    if($validation[$key]){
                        // если есть псевдоним у поля
                        if($this->translate[$key]){
                            $answer = $this->translate[$key][0];
                        }else{
                            $answer = $key;
                        }

                        // проверка на шифрование
                        if($validation[$key]['crypt']){
                            if($id){
                                if(empty($item)){
                                    // разрегистрация поля
                                    unset($arr[$key]);
                                    continue;
                                }

                                // кеширование
                                $arr[$key] = md5($item);

                            }
                        }

                        if($validation[$key]['empty']){
                            $this->emptyFields($item, $answer);
                        }

                        if($validation[$key]['trim']){
                            // зачищаем пробелы
                            $arr[$key] = trim($item);
                        }

                        if($validation[$key]['int']){
                            // переводим в int
                            $arr[$key] = $this->clearNum($item);
                        }

                        if($validation[$key]['count']){
                            $this->countChar($item, $validation[$key]['count'], $answer);
                        }


                    }

                }

            }

        }

        return true;

    }


    // редактирование или добавление новых данных
    protected function editData(){

    }


}