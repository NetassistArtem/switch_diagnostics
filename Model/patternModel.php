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

    public function PatternData($port_number, $pattern_id)
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM `patterns` WHERE `id`= :pattern_id";
        $placeholders = array(
            'pattern_id' => $pattern_id
        );
        $data = $dbc->getDate($sql, $placeholders);
        $port = $port_number + $data[0]['port_coefficient'];
        if ($data[0]['port_status']) {
            $data[0]['port_status'] = $data[0]['port_status'] . $port;
        }
        if ($data[0]['counter_byte']) {
            $data[0]['counter_byte'] = $data[0]['counter_byte'] . $port;
        }

        return $data[0];

    }


}