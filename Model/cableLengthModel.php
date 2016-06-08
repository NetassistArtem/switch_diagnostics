<?php


class cableLengthModel
{
    public function cableLength($user_id, $switch_id, $port_id)
    {
        $dbc = Connect_db::getConnection();
        if($user_id){
        $sql = "SELECT * FROM  cable_test WHERE user_id= :user_id";
        $placeholders = array(
            'user_id' => $user_id
        );
    }else{
            $sql = "SELECT * FROM  cable_test WHERE switch_id= :switch_id AND port_id= :port_id";
            $placeholders = array(
                'switch_id' => $switch_id,
                'port_id' => $port_id
            );
        }
        $data = $dbc->getDate($sql, $placeholders);

        return $data;
    }

    public function insertCableLength($user_id, $cable_length, $port_on_off, $switch_id, $port_id){

        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'cable_length'=> $cable_length,
            'user_id' => $user_id,
            'switch_id' => $switch_id,
            'port_id' => $port_id
        );
        $sql = "INSERT INTO `cable_test`(`user_id`,`switch_id`,`port_id`, `cable_length_port_{$port_on_off}`) VALUES (:user_id, :switch_id, :port_id, :cable_length)";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);
    }

    public function updataCableLength($user_id, $cable_length, $port_on_off, $switch_id, $port_id)
    {
        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'cable_length'=> $cable_length,
            'user_id' => $user_id,
            'switch_id'=> $switch_id,
            'port_id' => $port_id
        );
        $sql = "UPDATE `cable_test` SET `user_id`= :user_id,`cable_length_port_{$port_on_off}`=:cable_length WHERE `switch_id`= :switch_id AND `port_id`= :port_id";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);
    }
}