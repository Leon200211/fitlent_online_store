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
     *  'fields' => ['id', 'name'],
        'where' => [
        'id' => 1,
        'name' => 'Leon'
        ],
        'operand' => ['=', '='],
        'condition' => ['AND'],
        'order' => ['id'],
        'order_direction' => ['ASC'],
        'limit' => '2'
     */
    public function read($table, $set = []){

        // получение полей
        $fields = $this->createFields($table, $set);
        // строим запрос
        $where = $this->createWhere($table, $set);
        // массив join
        $join_arr = $this->createJoin($table, $set);


        // объединяем запрос
        $fields .= $join_arr['fields'];
        $where .= $join_arr['where'];
        $join = $join_arr['join'];

        // удаляем последнюю запятую
        $fields = rtrim($fields, ',');

        // сортировку в запросе
        $order = $this->createOrder($table, $set);

        // лимит записей
        $limit = @$set['limit'] ? $set['limit'] : '';

        // запрос
        $query = "SELECT $fields FROM $table $join $where $order $limit";

        // Вызов базового метода обращения к БД
        return $this->my_query($query, 'r');

    }


    // группировка всех полей для вывода и работы
    protected function createFields($table = false, $set){
        // проверка на существование полей
        $set['fields'] = (!empty($set['fields']) and is_array($set['fields']))
            ? $set['fields'] : '*';

        $table = $table ? $table . '.' : '';


        $fields = '';

        foreach ($set['fields'] as $field){
            $fields .= $table . $field . ',';
        }

        return $fields;
    }


    // создание запроса сортировки
    protected function createOrder($table = false, $set){

        $table = $table ? $table . '.' : '';

        $order_by = '';
        if(!empty($set['fields']) and is_array($set['fields'])){

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

                $order_by .= $table . $order . ' ' . $order_direction . ',';
            }

            // обрезаем запятую
            $order_by = rtrim($order_by, ',');
        }
        return $order_by;
    }

}