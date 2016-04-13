<?php


class cableLengthModel
{
    public function cableLength($user_id)
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM  cable_test WHERE user_id= :user_id";
        $placeholders = array(
            'user_id' => $user_id
        );
        $data = $dbc->getDate($sql, $placeholders);

        return $data;
    }

    public function insertCableLength($user_id, $cable_lenght){

        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'cable_lenght'=> $cable_lenght,
            'user_id' => $user_id
        );
        $sql = "INSERT INTO `cable_test`(`user_id`, `cable_lenght`) VALUES (:user_id, :cable_lenght)";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);
    }

    public function updataCableLength($user_id, $cable_lenght)
    {
        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'cable_lenght'=> $cable_lenght,
            'user_id' => $user_id
        );
        $sql = "UPDATE `cable_test` SET `user_id`=user_id,`cable_lenght`=:cable_lenght WHERE `user_id`= :user_id";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);
    }
}