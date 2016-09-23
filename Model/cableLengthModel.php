<?php


class cableLengthModel
{
    public function cableLength($user_id, $switch_id, $port_id)
    {
        $dbc = Connect_db::getConnection();
        if ($user_id) {
            $sql = "SELECT * FROM  cable_test WHERE user_id= :user_id";
            $placeholders = array(
                'user_id' => $user_id
            );
        } else {
            $sql = "SELECT * FROM  cable_test WHERE switch_id= :switch_id AND port_id= :port_id";
            $placeholders = array(
                'switch_id' => $switch_id,
                'port_id' => $port_id
            );
        }

        $data = $dbc->getDate($sql, $placeholders);

        return $data;
    }

    public function insertCableLength($user_id, $cable_length, $port_on_off, $switch_id, $port_id, array $cable_length_pairs)
    {

        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'cable_length' => isset($cable_length) ? $cable_length : 0,
            'user_id' => $user_id,
            'switch_id' => $switch_id,
            'port_id' => $port_id,
            'cable_length_pair_1' => isset($cable_length_pairs[0]) ? $cable_length_pairs[0] : 0,
            'cable_length_pair_2' => isset($cable_length_pairs[1]) ? $cable_length_pairs[1] : 0,
            'cable_length_pair_3' => isset($cable_length_pairs[2]) ? $cable_length_pairs[2] : 0,
            'cable_length_pair_4' => isset($cable_length_pairs[3]) ? $cable_length_pairs[3] : 0,
        );
        $sql = "INSERT INTO `cable_test`(`user_id`,`switch_id`,`port_id`, `cable_length_port_{$port_on_off}`, `cable_length_port_{$port_on_off}_p1`
, `cable_length_port_{$port_on_off}_p2`, `cable_length_port_{$port_on_off}_p3`, `cable_length_port_{$port_on_off}_p4`)
 VALUES (:user_id, :switch_id, :port_id, :cable_length, :cable_length_pair_1, :cable_length_pair_2, :cable_length_pair_3, :cable_length_pair_4)";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);
    }

    public function updataCableLength($user_id, $cable_length, $port_on_off, $switch_id, $port_id, array $cable_length_pairs)
    {
        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'cable_length' => isset($cable_length) ? $cable_length : 0,
            'user_id' => $user_id,
            'switch_id' => $switch_id,
            'port_id' => $port_id,
            'cable_length_pair_1' => isset($cable_length_pairs[0]) ? $cable_length_pairs[0] : 0,
            'cable_length_pair_2' => isset($cable_length_pairs[1]) ? $cable_length_pairs[1] : 0,
            'cable_length_pair_3' => isset($cable_length_pairs[2]) ? $cable_length_pairs[2] : 0,
            'cable_length_pair_4' => isset($cable_length_pairs[3]) ? $cable_length_pairs[3] : 0,
        );
        $sql = "UPDATE `cable_test` SET `user_id`= :user_id,`cable_length_port_{$port_on_off}`=:cable_length,`cable_length_port_{$port_on_off}_p1`=:cable_length_pair_1,`cable_length_port_{$port_on_off}_p2`=:cable_length_pair_2
 ,`cable_length_port_{$port_on_off}_p3`=:cable_length_pair_3,`cable_length_port_{$port_on_off}_p4`=:cable_length_pair_4 WHERE `switch_id`= :switch_id AND `port_id`= :port_id";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);
    }
}