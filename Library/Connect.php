<?php


class Connect {


    private static $connection;
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

    public static function getConnection()
    {
        if(!self::$connection){
            self::$connection = new Connect(DSN, USER, PASS);
        }
        return self::$connection;
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