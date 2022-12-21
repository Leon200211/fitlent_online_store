<?php


namespace core\admin\controllers;

use core\base\settings\Settings;



// контроллер для добавление информации через админ панель
class addController extends BaseAdmin
{

    protected function inputData()
    {

        if(!$this->userId) $this->execBase();

        $this->createTableData();

        // получение данных из связных таблиц
        $this->createForeignData();

        $this->createRadio();

        // разбор колонок на блоки
        $this->createOutputData();

    }


    

    // получение данных из связных таблиц
    protected function createForeignProperty($arr, $rootItems){
        if(in_array($this->table, $rootItems['tables'])){
            $this->foreignData[$arr['COLUMN_NAME']][0]['id'] = 0;
            $this->foreignData[$arr['COLUMN_NAME']][0]['name'] = $rootItems['name'];
        }

        // получаем все названия полей в таблице
        $columns = $this->model->showColumns($arr['REFERENCED_TABLE_NAME']);

        $name = '';
        if($columns['name']){
            $name = 'name';
        }else{

            foreach ($columns as $key => $column){
                if(strpos($key, 'name') !== false){
                    $name = $key . ' as name';
                }
            }

            // если вообще не получили ни одного имени
            if(!$name) $name = $columns['id_row'] . ' as name';

        }

        // если таблица ссылается сама на себя
        if($this->data){
            if($arr['REFERENCED_TABLE_NAME'] === $this->table){
                $where[$this->columns['id_row']] = $this->data[$this->columns['id_row']];
                $operand[] = "!=";
            }
        }

        // получаем имя поля на которое ссылаемся из внешней таблицы
        $foreign = $this->model->read($arr['REFERENCED_TABLE_NAME'], [
            'fields' => [$arr['REFERENCED_COLUMN_NAME'] . ' as id', $name],
            'where' => $where,
            'operand' => $operand
        ]);

        if($foreign){

            // если это не первое значение
            if($this->foreignData[$arr['COLUMN_NAME']]){
                foreach ($foreign as $value){
                    $this->foreignData[$arr['COLUMN_NAME']][] = $value;
                }
            }else{
                $this->foreignData[$arr['COLUMN_NAME']] = $foreign;
            }

        }
    }

    // получение данных из связных таблиц
    protected function createForeignData($settings = false){

        if(!$settings) $settings = Settings::getInstance();

        $rootItems = $settings::get('rootItems');

        // получаем все внешние ключи
        $keys = $this->model->showForeignKeys($this->table);

        if($keys){

            foreach ($keys as $item){
                $this->createForeignProperty($item, $rootItems);
            }

        }elseif ($this->columns['th_id']){        // если нет никаких внешних ключей

            $arr['COLUMN_NAME'] = $this->columns['id_row'];
            $arr['REFERENCED_COLUMN_NAME'] = $this->columns['id_row'];
            $arr['REFERENCED_TABLE_NAME'] = $this->table;

            $this->createForeignProperty($arr, $rootItems);

        }

        return;

    }



}