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

        // разбор колонок на блоки
        $this->createOutputData();

    }

    protected function createForeignData($settings = false){

        if(!$settings) $settings = Settings::getInstance();

        $rootItems = $settings::get('rootItems');

        // получаем все внешние ключи
        $keys = $this->model->showForeignKeys($this->table);

        if($keys){

            foreach ($keys as $item){

                if(in_array($this->table, $rootItems['tables'])){
                    $this->foreignData[$item['COLUMN_NAME']][0]['id'] = 0;
                    $this->foreignData[$item['COLUMN_NAME']][0]['name'] = $rootItems['name'];
                }

                // получаем все названия полей в таблице
                $columns = $this->model->showColumns($item['REFERENCED_TABLE_NAME']);

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
                    if($item['REFERENCED_TABLE_NAME'] === $this->table){
                        $where[$this->columns['id_row']] = $this->data[$this->columns['id_row']];
                        $operand[] = "!=";
                    }
                }

                // получаем имя поля на которое ссылаемся из внешней таблицы
                $foreign[$item['COLUMN_NAME']] = $this->model->read($item['REFERENCED_TABLE_NAME'], [
                    'fields' => [$item['REFERENCED_COLUMN_NAME'] . ' as id', $name],
                    'where' => $where,
                    'operand' => $operand
                ]);

                if($foreign[$item['COLUMN_NAME']]){

                    // если это не первое значение
                    if($this->foreignData[$item['COLUMN_NAME']]){
                        foreach ($foreign[$item['COLUMN_NAME']] as $value){
                            $this->foreignData[$item['COLUMN_NAME']][] = $value;
                        }
                    }else{
                        $this->foreignData[$item['COLUMN_NAME']] = $foreign[$item['COLUMN_NAME']];
                    }

                }

            }

        }elseif ($this->columns['th_id']){        // если нет никаких внешних ключей

            if(in_array($this->table, $rootItems['tables'])){
                $this->foreignData['parent_id'][0]['id'] = 0;
                $this->foreignData['parent_id'][0]['name'] = $rootItems['name'];
            }


            $name = '';
            if($this->columns['name']){
                $name = 'name';
            }else{

                foreach ($this->columns as $key => $column){
                    if(strpos($key, 'name') !== false){
                        $name = $key . ' as name';
                    }
                }

                // если вообще не получили ни одного имени
                if(!$name) $name = $this->columns['id_row'] . ' as name';

            }

            // если таблица ссылается сама на себя
            if($this->data){
                $where[$this->columns['id_row']] = $this->data[$this->columns['id_row']];
                $operand[] = "!=";
            }


            // получаем имя поля на которое ссылаемся из внешней таблицы
            $foreign = $this->model->read($this->table, [
                'fields' => [$this->columns['id_row'] . ' as id', $name],
                'where' => $where,
                'operand' => $operand
            ]);

            if($foreign){

                // если это не первое значение
                if($this->foreignData['parent_id']){
                    foreach ($foreign as $value){
                        $this->foreignData['parent_id'][] = $value;
                    }
                }else{
                    $this->foreignData['parent_id'] = $foreign;
                }

            }


        }

        return;

    }



}