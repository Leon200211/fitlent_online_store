<?php


namespace core\base\models;

// класс хранит вспомогательные методы для crud
abstract class BaseModelMethods
{


    // ===================================================
    // Для SELECT
    // ===================================================

    // группировка всех полей для вывода и работы
    protected function createFields($set, $table = false){
        // проверка на существование полей
        $set['fields'] = (!empty($set['fields']) and is_array($set['fields']))
            ? $set['fields'] : '*';

        $table = $table ? $table . '.' : '';

        $fields = '';

        foreach ($set['fields'] as $field){
            $fields .= $table . $field . ',';
        }
        // обрезаем запятую
        $fields = rtrim($fields, ',');

        return $fields;
    }

    // создание запроса для конструкции Where
    protected function createWhere($set, $table = false, $instruction = 'WHERE'){

        $table = $table ? $table . '.' : '';

        $where = '';

        if(!empty($set['where']) and is_array($set['where'])){

            // пришли ли операнды
            $set['operand'] = (!empty($set['operand']) and is_array($set['operand'])) ? $set['operand'] : ['='];
            // пришли ли условия
            $set['condition'] = (!empty($set['condition']) and is_array($set['condition'])) ? $set['condition'] : ['AND'];

            $where = $instruction;

            $o_count = 0;
            $c_count = 0;

            foreach ($set['where'] as $key => $item){

                $where .= " ";

                // определяем операнд
                if(isset($set['operand'][$o_count])){
                    $operand = $set['operand'][$o_count];
                    $o_count++;
                }else{
                    $operand = $set['operand'][$o_count-1];
                }
                // определяем условие
                if(isset($set['condition'][$c_count])){
                    $condition = $set['condition'][$c_count];
                    $c_count++;
                }else{
                    $condition = $set['condition'][$c_count-1];
                }

                if($operand === 'IN' or $operand === 'NOT IN'){
                    if(is_string($item) and strpos($item, 'SELECT') === 0){
                        $in_str = $item;
                    }else{
                        if(is_array($item)){
                            $temp_item = $item;
                        }else{
                            $temp_item = explode(',', $item);
                        }
                        $in_str = '';

                        foreach ($temp_item as $v){
                            $in_str .= "'" . addslashes(trim($v)) . "',";
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . " (" . rtrim($in_str, ',') . ") " . $condition;

                }elseif(strpos($operand, 'LIKE') !== false){
                    $like_template = explode('%', $operand);

                    foreach ($like_template as $lt_key => $lt){
                        if(!$lt){
                            if(!$lt_key){
                                $item = '%' . $item;
                            }else{
                                $item .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . " LIKE '" . addslashes($item) . "' $condition";

                }else {
                    // проверка на подзапросы
                    if(strpos($item, 'SELECT') === 0){
                        $where .= $table . $key . $operand . '(' . $item . ') ' . $condition;
                    }else{
                        $where .= $table . $key . $operand . "'" . addslashes($item) . "' " . $condition;
                    }

                }

            }

            // убираем последнее условие
            $where = substr($where, 0, strrpos($where, $condition));

        }

        return $where;

    }

    // создание join запроса
    protected function createJoin($set, $table, $new_where = false){

        $fields = '';
        $join = '';
        $where = '';

        if(isset($set['join'])){

            $join_table = $table;

            foreach ($set['join'] as $key => $item) {

                if(is_int($key)){
                    if(!$item['table']){
                        continue;
                    }else{
                        $key = $item['table'];
                    }
                }

                if($join){
                    $join .= ' ';
                }

                if(isset($item['on']) and $item['on']){
                    $join_fields = [];

                    if(isset($item['on']['fields']) and is_array($item['on']['fields']) and count($item['on']['fields']) === 2){
                        $join_fields = $item['on']['fields'];
                    }else if(count($item['on']) === 2){
                        $join_fields = $item['on'];
                    }else{
                        // для скипа этой итерации в которые мы вложены
                        //continue 2;
                        continue;
                    }

                    if(!$item['type']){
                        $join .= 'LEFT JOIN ';
                    }else{
                        $join .= trim(strtoupper($item['type'])) . ' JOIN ';
                    }

                    $join .= $key . ' ON ';


                    // проверка с какой таблицей стыковаться
                    if(@$item['on']['table']){
                        $join .= $item['on']['table'];
                    }else{
                        $join .= $join_table;
                    }

                    // указания полей для стыковки
                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[1];

                    $join_table = $key;

                    if($new_where){
                        if($item['where']){
                            $new_where = false;
                        }
                        $group_condition = 'WHERE';
                    }else{
                        $group_condition = isset($item['group_condition']) ? strtoupper($item['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($item, $key);
                    $where .= $this->createWhere($item, $key, $group_condition);

                }
            }
        }

        return compact('fields', 'join', 'where');

    }

    // создание запроса сортировки
    protected function createOrder($set, $table = false){

        $table = $table ? $table . '.' : '';

        $order_by = '';
        if(!empty($set['order']) and is_array($set['order'])){

            $set['order_direction'] = (!empty($set['order_direction']) and is_array($set['order_direction']))
                ? $set['order_direction'] : ['ASC'];

            $order_by = 'ORDER BY ';
            $direct_count = 0;
            foreach ($set['order'] as $order){
                // направление сортировки
                if(@$set['order_direction'][$direct_count]){
                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;
                }else{
                    $order_direction = strtoupper($set['order_direction'][$direct_count-1]);
                }
                if(is_int($order)){
                    $order_by .= $order . ' ' . $order_direction . ',';
                }else{
                    $order_by .= $table . $order . ' ' . $order_direction . ',';
                }

            }

            // обрезаем запятую
            $order_by = rtrim($order_by, ',');
        }
        return $order_by;
    }





    // ===================================================
    // Для INSERT
    // ===================================================

    protected function createInsert($fields, $files, $except){

        if(!$fields){
            $fields = $_POST;
        }

        $insert_arr = [];

        if($fields){
            // массив встроенных функций в mySql
            $mySql_function = ['NOW()'];

            foreach ($fields as $row => $value){

                if($except and in_array($row, $except)) continue;

                @$insert_arr['fields'] .= $row . ',';
                if(in_array($value, $mySql_function)){
                    @$insert_arr['values'] .= $value . ',';
                }else{
                    @$insert_arr['values'] .= "'" . addslashes($value) . "',";
                }
            }
        }
        if($files){
            foreach ($files as $row => $file){

                @$insert_arr['fields'] .= $row . ',';

                if(is_array($file)){
                    @$insert_arr['values'] .= "'" . addslashes(json_encode($file)) . "',";
                }else{
                    @$insert_arr['values'] .= "'" . addslashes($file) . "',";
                }

                $file = 'main_img.jpg';
                $arr['gallery_img'] = ['1.jpg', '2.png'];
            }
        }


        // обрезаем запятую
        if($insert_arr){
            foreach ($insert_arr as $key => $arr){
                $insert_arr[$key] = rtrim($arr, ',');
            }
        }

        return $insert_arr;

    }




}