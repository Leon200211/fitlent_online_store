<?php


namespace core\admin\controllers;



use core\base\controllers\BaseController;
use core\admin\models\Model;


// индексный контроллер для админа
class IndexController extends BaseController
{

    protected function inputData(){

        $db = Model::getInstance();

        $query = "SELECT * FROM `articles`";
        $res = $db->my_query($query);

        exit("Hello");
    }

}