<?php


namespace core\base\models;



// класс для работы с базовой моделью
use core\base\controllers\Singleton;
use core\base\exceptions\DbException;

class BaseModel
{

    // трейт для паттерна Singleton
    use Singleton;


    protected $db;

    private function __construct()
    {
        // подключение к БД
        $this->db = @new \mysqli(HOST, USER, PASS, DB_NAME);

        // отлов и логирование ошибок
        if($this->db->connect_error){
            throw new DbException("Ошибка подключение к БД: " .
                $this->db->connect_errno . " " . $this->db->connect_error);
        }

        $this->db->query("SET NAMES UTF8");


    }


    // базовый метод обращения к БД
    // запрет переопределения у наследников
    final public function my_query($query, $crud = 'r', $return_id = false){

        $result = $this->db->query($query);

        if($this->db->affected_rows === -1){
            throw new DbException("Ошибка в SQL запросе: $query - {$this->db->errno} {$this->db->error}");
        }

        switch ($crud){

            case 'r':
                if($result->num_rows){
                    $res = [];

                    for($i = 0; $i < $result->num_rows; $i++){
                        $res[] = $result->fetch_assoc();
                    }
                    return $res;
                }
                return false;
                break;

            case 'c':

                if($return_id){
                    // вернуть id вставленного элемента
                    return $this->db->insert_id;
                }
                return true;
                break;

            default:
                return true;
                break;


        }
    }



    // чтение из БД
    /**
     * @param $table - Таблица из БД
     * @param array $set
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
        'limit' => '2',
        'join' => [
            [
            'table' => 'join_table1',
            'fields' => ['id as j_id', 'name as j_name'],
            'type' => 'left',
            'where' => ['name' => 'leon'],
            'operand' => ['='],
            'condition' => ['OR'],
            'on' => ['id', 'namessea']
        ],
        'join_table2' => [
            'table' => 'join_table1',
            'fields' => ['id as j2_id', 'name as j2_name'],
            'type' => 'left',
            'where' => ['name' => 'leon'],
            'operand' => ['!='],
            'condition' => ['OR'],
            'on' => [
                'table' => 'join_table1',
                'fields' => ['id', 'namessea']
            ]
        ]
    ]
     */
    public function read($table, $set = []){

        // получение полей
        $fields = $this->createFields($set, $table);
        // строим запрос
        $where = $this->createWhere($set, $table);

        if(!isset($where)){
            $new_where = true;
        }else{
            $new_where = false;
        }

        // массив join
        $join_arr = $this->createJoin($set, $table, $new_where);


        // объединяем запрос
        $fields .= $join_arr['fields'];
        $where .= $join_arr['where'];
        $join = $join_arr['join'];

        // удаляем последнюю запятую
        $fields = rtrim($fields, ',');

        // сортировку в запросе
        $order = $this->createOrder($set, $table);

        // лимит записей
        $limit = @$set['limit'] ? 'LIMIT ' . $set['limit'] : '';

        // запрос
        $query = "SELECT $fields FROM $table $join $where $order $limit";
        // Вызов базового метода обращения к БД
        return $this->my_query($query, 'r');

    }


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

}