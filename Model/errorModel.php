<?php


class errorModel
{
    public $user_id;
    public $date;
    public $time;

    public function __construct($account_id = null)
    {
        $request = new Request();
        if($account_id){
            $this->user_id = $account_id;
        }elseif(Router::getAccountId()){
            $this->user_id = Router::getAccountId();
        }else{
            $this->user_id = $request->post('account_id');
        }
     //   $this->user_id = Router::getAccountId()? Router::getAccountId() : $request->post('account_id');
        $this->date = strtotime(date('Y-m-d'));
        $this->time = date('h:i:s');
    }
    public function writeError($error)

    {
        $error_string = '';
        foreach($error as $k => $v){
            $error_string .= ($k+1).'. '.$v['warning_class'].': '.$v['message'].PHP_EOL;
        }
        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'date' => $this->date,
            'time' => $this->time,
            'user_id' =>$this->user_id ,
            'error' => $error_string
        );

        $sql = "INSERT INTO `user_error`(`date`, `time`, `user_id`, `error`) VALUES (:date,:time,:user_id,:error)";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }

    public function getErrorData($date)
    {
        $dbc = Connect_db::getConnection();

        if($date){
          //  if(strpos($date, '_')){
                $date = strtotime(date('Y-m-d'));
           // }

            $sql = "SELECT `date`, `time`, `user_id`, `error` FROM `user_error` WHERE `date`= :date";
            $placeholders = array(
                'date' => $date
        );
        }else{
            $sql = "SELECT `date`, `time`, `user_id`, `error` FROM `user_error`";
            $placeholders = array();
        }

        $data = $dbc->getDate($sql, $placeholders);
        return $data;
    }

    public function cleanUserError()
    {
        $date_now = strtotime(date('Y-m-d'));
        $date_oldest = $date_now -Config::get('time_clean_user_error');

        $dbc = Connect_db::getConnection();
        $sql = "DELETE FROM `user_error` WHERE `date`< :date_oldest";
        $placeholders = array(
            'date_oldest' => $date_oldest
        );

        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }

}