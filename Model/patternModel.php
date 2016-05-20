<?php


class patternModel
{
    public $account_id;

    public function __construct($account_id)
    {
        $this->account_id = $account_id;
    }

    public function switchData()
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM  switches";
        $placeholders = array();
        $data = $dbc->getDate($sql, $placeholders);



        if (!$data) {
            throw new Exception(" Switch models data is missing", 404);
        }
        return $data;
    }

    private function switchUserData()
    {
        $dbc = Connect_db::getConnection(2);

        $sql = "SELECT `switch_model`, `firmware` FROM `users` WHERE `id`= :id_user";
        $placeholders = array(
            'id_user' => $this->account_id
        );
        $data = $dbc->getDate($sql, $placeholders);


        if (!$data) {
            throw new Exception(" Switch models and firmware data is missing in billing DB", 404);
        }
        return $data[0];
    }

    public function getPatternUserData()
    {
        $dbc = Connect_db::getConnection();
        $switchUserData = $this->switchUserData();
        if (!$switchUserData['firmware']) {
            $placeholders = array(
                'model_name' => $switchUserData['switch_model']
            );
            $sql = "SELECT `pattern_id` FROM `switches` WHERE `model_name` = :model_name";
        } else {
            $placeholders = array(
                'model_name' => $switchUserData['switch_model'],
                'firmware' => $switchUserData['firmware']
            );
            $sql = "SELECT `pattern_id` FROM `switches` WHERE `model_name` = :model_name AND `firmware`= :firmware";
        }

        $data = $dbc->getDate($sql, $placeholders);

        return $data[0];
    }

    public function getSwitchDataByName($switch_model)
    {
        $dbc = Connect_db::getConnection();


        $sql = "SELECT * FROM `switches` WHERE `model_name`= :model_name";
        $placeholders = array(
            'model_name' => $switch_model,

        );
        $data_switch = $dbc->getDate($sql, $placeholders);

        return $data_switch;

    }

    public function PatternData($port_number, $pattern_id)
    {

     //   $data_switch = $this->getSwitchDataByName($switch_model);

        $dbc = Connect_db::getConnection();

        $sql = "SELECT * FROM `patterns` WHERE `id`= :pattern_id";
        $placeholders = array(
            'pattern_id' => $pattern_id
        );
        $data = $dbc->getDate($sql, $placeholders);

        $port_coefficient_array = IndexModel::getPortCoeff();

        /*
        if($port_number <= $data_switch[0]['simple_port']){

            $port = $port_number + $data[0]['port_coefficient'];
        }else{
            $port = $port_number + $data[0]['gig_port_coefficient'];
        }

*/
        $port = $port_number + $port_coefficient_array['port_coefficient_simple_gig'];




        foreach ($data[0] as $k => $v) {
            if ($k != 'id' /* && $k != 'port_coefficient'&&  $k != 'gig_port_coefficient' */&& $k != 'mac_all'&& $k != 'macs_ports') {

                $data[0][$k] = $data[0][$k] . $port;

                if(empty($v)){
                    unset($data[0][$k]);
                }

            }
        }


        return $data[0];

    }
    public function macData($pattern_id)
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT `mac_all` FROM `patterns` WHERE  `id`= :pattern_id";
        $placeholders = array(
            'pattern_id' => $pattern_id
        );
        $data = $dbc->getDate($sql, $placeholders);

        return $data[0];
    }
/*
    public function getPortCoefficient($pattern_id,$port_number, $switch_model)
    {

        $data_switch = $this->getSwitchDataByName($switch_model);

        $dbc = Connect_db::getConnection();

        $sql = "SELECT `port_coefficient`, `gig_port_coefficient` FROM `patterns` WHERE  `id`= :pattern_id";
        $placeholders = array(
            'pattern_id' => $pattern_id
        );
        $data = $dbc->getDate($sql, $placeholders);

        if($port_number <= $data_switch[0]['simple_port']){

            $data[0]['port_coefficient_simple_gig'] = $data[0]['port_coefficient'];
        }else{

            $data[0]['port_coefficient_simple_gig'] = $data[0]['gig_port_coefficient'];
        }

        return $data[0];
    }
*/
    public function patternsId()
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT `id` FROM `patterns`";
        $placeholders = array();
        $data = $dbc->getDate($sql, $placeholders);

        return $data;
    }

    public function allPatternData()
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM `patterns`";
        $placeholders = array();
        $data = $dbc->getDate($sql, $placeholders);

        return $data;

    }

    public function patternFieldsName()
    {
        $dbc = Connect_db::getConnection();
        $sql = "SHOW FIELDS FROM patterns";
        $placeholders = array();
        $d = $dbc->getDate($sql, $placeholders);
        $data = array();

        foreach($d as $k => $v){
            $data[] = $v['Field'];
        }

        return $data;
    }


}