<?php


class Connect_db {


    private static $connection1;
    private static $connection2;
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
        }else{
            return null;
        }


    }
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