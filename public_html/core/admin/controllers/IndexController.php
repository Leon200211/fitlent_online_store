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


        $files['gellery_img'] = ["red''.jpg", "blue.jpg", 'black.jpg'];
        $files['img'] = 'main_img.jpg';
        $res = $db->add($table, [
            'fields' => ['name' => 'sd',  'content' => 'dsg', 'price' => 100],
            'except' => ['name'],
        ]);

        exit("Hello");
    }

}