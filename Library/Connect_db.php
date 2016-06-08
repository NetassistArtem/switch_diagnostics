<?php

/**
 * \brief Class Connect_db
 *
 * Class Connect_db созадет подключение к базам данных используя стандартный класс PDO. Используется паттерн Singleton.
 * Конструктор класса (privat property - допустимо в пределах шаблона Singleton чтоб полностью исключить создание
 * еще одного объекта подключения).Создает объект подключения класса PDO и устанавливает дополнительные атрибуты
 *
 */
class Connect_db {

    private static $connection1;
    private static $connection2;
    private static $connection3;
    private $PDO;
    private function __clone(){}
    private function __wakeup(){}

    private   function __construct($dsn, $user, $pass){
        //try{
        $this->PDO = new PDO($dsn, $user, $pass);
        $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          //  }catch (PDOException $e){
           // throw new Exception($e->getMessage(),1044);
        //}
    }
    /**
     * \param $db_number номер базы данных (1,2,3), по умолчанию база данных 1- тестовая для dev версии
     *
     * \return self::$connection1 свойство содержащее подключение
     *
     * Проверяет есть ли свойство содержащее подключение self::$connection1, если нет создает его.
     * В зависимости от номера базы данных создает и\или возвращает подключение private static $connection1,
     * private static $connection2, private static $connection3
     * Соответствующие данные для подключения будут взяты из конфигурационных данных:
     *\code
     * $db_data = Config::get('db_'.$db_number);
     *\endcode
     */
    public static function getConnection($db_number = 1)
    {

        $db_data = Config::get('db_'.$db_number);

        if($db_number == 1){
            if(!self::$connection1){
                self::$connection1 = new Connect_db($db_data['dsn'], $db_data['user'], $db_data['pass']);
            }
            return self::$connection1;

        }elseif($db_number == 2){
            if(!self::$connection2){
                self::$connection2 = new Connect_db($db_data['dsn'], $db_data['user'], $db_data['pass']);
            }
            return self::$connection2;

        }elseif($db_number == 3){
            if(!self::$connection3){
                self::$connection3 = new Connect_db($db_data['dsn'], $db_data['user'], $db_data['pass']);
            }
            return self::$connection3;
        }else{
            return null;
        }
    }

    /**
     * \param $sql  SQL запрос
     * \param array $placeholders=array() массив параметров которые используются для подстановки в sql запросе
     * \return $date запрашиваемые данные из базы данных
     *
     * Используя методы объекта PDO  prepare($sql), execute($placeholders), fetchAll(PDO::FETCH_ASSOC)
     * плучает данные из базы данных на основании sql запроса, использование $placeholders исключает возможность sql инъекций
     */
    public function getDate($sql, array $placeholders=array())
    {
        //$this->PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $sth = $this->PDO->prepare($sql);

        //$sth->bindParam(':from', $from, PDO::PARAM_INT);
       // $sth->bindParam(':count', $count, PDO::PARAM_INT);

        $sth->execute($placeholders);
        $date = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $date;

    }

    /**
     * \return $this->PDO возвращает подключение с помощью PDO
     *
     * Возвращает объект подключения PDO. Это дает возможность обращаться к свойствам и методам класса PDO
     */
    public function getPDO()
    {
        return $this->PDO;
    }



/**
    private static $db;
    private $connect;

    public function __construct($dsn, $user, $pass)
    {
        try{
        if(!is_object(self::$db)){
        self::$db = new PDO($dsn, $user, $pass);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }
            $this->connect = self::$db;
        }catch (PDOException $e){
            throw new Exception($e->getMessage(),1044);
        }
    }

**/

    
}