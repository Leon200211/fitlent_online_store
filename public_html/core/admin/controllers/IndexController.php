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


        $res = $db->delete($table, [
            'where' => ['id' => 2],
            'join' => [
                [   'table' => 'student',
                    'on' => ['name', 'id']
                ]
            ]
        ]);

        exit("Hello");
    }

}