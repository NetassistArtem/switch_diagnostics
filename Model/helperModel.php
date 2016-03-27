<?php


class helperModel
{
    public function insertMac($id,$mac, $switch_ip, $port,$switch_model, $firmware)
    {
        $mac_int = base_convert($mac, 16, 10);
        $switch_ip_int = ip2long($switch_ip);
        $dbc = Connect_db::getConnection(2);
        $placeholders = array(
            'mac_int' => $mac_int,
            'id' => $id,
            'switch_ip_int' => $switch_ip_int,
            'port' => $port,
            'firmware' => $firmware,
            'switch_model' => $switch_model
        );
        $sql = "INSERT INTO `users`(`id`, `switch_ip`, `mac`, `port`, `switch_model`, `firmware`) VALUES (:id,:switch_ip_int,:mac_int, :port, :switch_model, :firmware)";

        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }

}