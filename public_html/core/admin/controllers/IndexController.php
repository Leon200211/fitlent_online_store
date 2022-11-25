<?php


namespace core\admin\controllers;



use core\base\controllers\BaseController;
use core\admin\models\Model;


// индексный контроллер для админа
class IndexController extends BaseController
{

    protected function inputData(){

        $db = Model::getInstance();

        $table = 'articles';


        $_POST['id'] = 3;
        $_POST['name'] = '';

        $files['price'] = 1000;

        $res = $db->update($table, ['files' => $files]);

        exit("Hello");
    }

}