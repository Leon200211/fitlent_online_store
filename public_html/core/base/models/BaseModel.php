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

}