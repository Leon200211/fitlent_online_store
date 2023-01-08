<?php


namespace core\admin\controllers;


// контроллер редактирование содержимого страниц
class EditController extends BaseAdmin
{

    protected function inputData(){

        if(!$this->userId) $this->execBase();

    }



    // метод для проверки старых урл
    protected function checkOldAlias($id){

        $tables = $this->model->showTables();

        if(in_array('old_alias', $tables)){
            // извлекаем новый алиас
            $old_alias = $this->model->read($this->table, [
                'fields' => ['alias'],
                'where' => [$this->columns['id_row'] => $id]
            ])[0]['alias'];

            if($old_alias and $old_alias !== $_POST['alias']){
                // удаляем алиса относящийся именно к этой таблице
                $this->model->delete('old_alias', [
                    'where' => ['alias' => $old_alias, 'table_name' => $this->table]
                ]);

                $this->model->delete('old_alias', [
                    'where' => ['alias' => $_POST['alias'], 'table_name' => $this->table]
                ]);

                // добавляем новый алиас
                $this->model->add('old_alias', [
                   'fields' => ['alias' => $old_alias, 'table_name' => $this->table, 'table_id' => $id]
                ]);

            }

        }

    }



}