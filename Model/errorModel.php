<?php


class errorModel
{
    public $user_id = null;
    public $switch_id = null;
    public $port_id = null;
    public $date;
    public $time;

    public function __construct($account_id = null, $switch_id = null, $port_id = null, $switch_port_id = null)
    {
        $request = new Request();

        if ($account_id && !$switch_port_id) {
            $this->user_id = $account_id;
            $this->switch_id =$switch_id;
            $this->port_id = $port_id;
        } elseif (Router::getAccountId() && !$switch_port_id) {
            $this->user_id = Router::getAccountId();
        } elseif($request->post('account_id')&& !$switch_port_id) {
            $this->user_id = $request->post('account_id');
        } elseif($switch_id && $port_id){
            $this->switch_id = $switch_id;
            $this->port_id = $port_id;
            $this->user_id = $account_id;
        }elseif(Router::getSwitchId() && Router::getPortId()){
            $this->switch_id = Router::getSwitchId();
            $this->port_id = Router::getPortId();
        }elseif($request->post('switch_id')&& $request->post('port_id')){
            $this->user_id = $request->post('switch_id');
            $this->user_id = $request->post('port_id');
        }
        //   $this->user_id = Router::getAccountId()? Router::getAccountId() : $request->post('account_id');
        $this->date = strtotime(date('Y-m-d'));
        $this->time = date('h:i:s');
    }

    public function writeError($error)

    {
        if ($error) {

            $error_string = '';
            foreach ($error as $k => $v) {
                $error_string .= '<p class="' . $v['warning_class'] . '">' . ($k + 1) . '. ' . $v['warning_level'] . ': ' . $v['message'] . '</p>' . PHP_EOL;
            }
            $dbc = Connect_db::getConnection();
            $placeholders = array(
                'date' => $this->date,
                'time' => $this->time,
                'user_id' => $this->user_id,
                'switch_id' => $this->switch_id,
                'port_id' => $this->port_id,
                'error' => $error_string
            );


            $sql = "INSERT INTO `user_error`(`date`, `time`, `user_id`, `error`, `switch_id`, `port_id`) VALUES (:date,:time,:user_id,:error,:switch_id,:port_id)";
            $sth = $dbc->getPDO()->prepare($sql);
            $sth->execute($placeholders);
        }

    }

    public function getErrorData($date)
    {
        $dbc = Connect_db::getConnection();

        if ($date) {
            //  if(strpos($date, '_')){
            $date = strtotime(date('Y-m-d'));
            // }

            $sql = "SELECT * FROM `user_error` WHERE `date`= :date";
            $placeholders = array(
                'date' => $date
            );
        } else {
            $sql = "SELECT * FROM `user_error`";
            $placeholders = array();
        }

        $data = $dbc->getDate($sql, $placeholders);
        return $data;
    }

    public function cleanUserError()
    {
        $date_now = strtotime(date('Y-m-d'));
        $date_oldest = $date_now - Config::get('time_clean_user_error');

        $dbc = Connect_db::getConnection();
        $sql = "DELETE FROM `user_error` WHERE `date`< :date_oldest";
        $placeholders = array(
            'date_oldest' => $date_oldest
        );

        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }

}