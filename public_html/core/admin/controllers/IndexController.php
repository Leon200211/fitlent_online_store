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
                'name' => 'leon, as, ad',
                'id' => 'Leon , sadd, sd',
                'fio' => 'test',
                'cat' => 'with',
                'color' => ['red', 'blue', 'black']
            ],
            'operand' => ['IN', 'NOT IN', 'LIKE%', '=', 'IN'],
            'condition' => ['AND', 'OR'],
            'order' => ['id', 'name'],
            'order_direction' => ['ASC', 'DESC'],
            'limit' => '2'
        ]);

        exit("Hello");
    }

}