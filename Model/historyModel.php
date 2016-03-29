<?php


class historyModel
{
    public function insertData($account_id, array $data_switch, array  $data_db)
    {

        $mac_int = base_convert($data_db['mac'], 16, 10);
        $switch_ip_int = ip2long($data_db['switch_ip']);
        $placeholders = array(
            'date_time' => strtotime(date('Y-m-d h:i:s')),
            'account_id' => $account_id,
            'switch_ip' => $switch_ip_int,
            'mac' => $mac_int,
            'port' => $data_db['port'],
            'switch_model' => $data_db['switch_model'],
            'firmware' => $data_db['firmware'],
            'port_status' => $data_switch['port_status'],
            'counter_byte_in' => $data_switch['counter_byte_in'],
            'counter_byte_out' => $data_switch['counter_byte_out'],
            'counter_pkts_out' => $data_switch['counter_pkts_out'],
            'error' => $data_switch['error'],
            'speed' => $data_switch['speed'],
            'last_change' => $data_switch['last_change']

        );

        $dbc = Connect_db::getConnection();
        $sql = "INSERT INTO `users_history`(`date_time`,`account_id`, `switch_ip`, `mac`, `port`, `switch_model`, `firmware`,
 `port_status`, `counter_byte_in`, `counter_byte_out`, `counter_pkts_out`, `error`, `speed`, `last_change`)
  VALUES (:date_time,:account_id, :switch_ip, :mac, :port, :switch_model, :firmware, :port_status, :counter_byte_in,
  :counter_byte_out, :counter_pkts_out, :error, :speed, :last_change)";
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }

    public function selectData($account_id)
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM `users_history` WHERE `account_id`= :account_id";
        $placeholders = array(
            'account_id' => $account_id
        );
        $data = $dbc->getDate($sql, $placeholders);


        return $data;
    }

    public function cleanHistory()
    {
        $date_now = strtotime(date('Y-m-d h:i:s'));
        $date_oldest = $date_now -Config::get('time_clean_history');

        $dbc = Connect_db::getConnection();
        $sql = "DELETE FROM `users_history` WHERE `date_time`< :date_oldest";
        $placeholders = array(
            'date_oldest' => $date_oldest
        );

        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }

}