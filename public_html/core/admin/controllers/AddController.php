<?php


namespace core\admin\controllers;



// контроллер для добавление информации через админ панель
class addController extends BaseAdmin
{

    protected function inputData()
    {

        if(!$this->userId) $this->execBase();

        $this->createTableData();

        // разбор колонок на блоки
        $this->createOutputData();

        $this->model->showForeignKeys($this->table);

    }





}