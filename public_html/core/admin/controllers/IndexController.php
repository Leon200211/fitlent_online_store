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
                'name' => "Leo"
            ],
            'operand' => ['IN', 'NOT IN', 'LIKE%', '=', 'IN'],
            'condition' => ['AND', 'OR'],
            'order' => ['name'],
            'order_direction' => ['DESC'],
            'limit' => '2',
            'join' => [
                [
                    'table' => 'join_table1',
                    'fields' => ['id as j_id', 'name as j_name'],
                    'type' => 'left',
                    'where' => ['name' => 'leon'],
                    'operand' => ['='],
                    'condition' => ['OR'],
                    'on' => [
                        'table' => 'join_table1',
                        'fields' => ['id', 'namessea']
                    ],
                    'group_condition' => 'AND'
                ],
                'join_table2' => [
                    'table' => 'join_table1',
                    'fields' => ['id as j2_id', 'name as j2_name'],
                    'type' => 'left',
                    'where' => ['name' => 'leon'],
                    'operand' => ['!='],
                    'condition' => ['OR'],
                    'on' => ['id', 'namessea'],
                ]
            ]
        ]);

        exit("Hello");
    }

}