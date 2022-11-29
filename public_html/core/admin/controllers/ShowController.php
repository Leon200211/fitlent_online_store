<?php


namespace core\admin\controllers;



// контроллер для админ панели
class ShowController extends BaseAdmin
{

    protected function inputData(){

        parent::execBase();

        $this->createTableData();

        $this->createData(['fields' => ['content']]);

        exit();
    }




    protected function outputData(){

    }


}