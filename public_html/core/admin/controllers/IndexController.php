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

        $res = $db->read($table, [
            'fields' => ['id', 'name'],
            'where' => [
                'id' => 1,
                'name' => 'Leon'
            ],
            'operand' => ['=', '='],
            'condition' => ['AND'],
            'order' => ['id', 'name'],
            'order_direction' => ['ASC', 'DESC'],
            'limit' => '2'
        ]);

        exit("Hello");
    }

}