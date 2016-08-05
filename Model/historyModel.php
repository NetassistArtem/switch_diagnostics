<?php


class historyModel
{
    public function insertData($account_id, array $data_switch, array  $data_db, $switch_id)
    {
        $cable_status_total = isset($data_switch['cable_status'])? $data_switch['cable_status'] : '-';
        $cable_status_p1 = isset($data_switch['cable_status_p1'])? $data_switch['cable_status_p1'] : '-';
        $cable_status_p2 = isset($data_switch['cable_status_p2'])? $data_switch['cable_status_p2'] : '-';
        $cable_status_p3 = isset($data_switch['cable_status_p3'])? $data_switch['cable_status_p3'] : '-';
        $cable_status_p4 = isset($data_switch['cable_status_p4'])? $data_switch['cable_status_p4'] : '-';
        $cable_status = $cable_status_total.'<br> Пара 1: '.$cable_status_p1.'<br> Пара 2: '.$cable_status_p2.'<br> Пара 3: '.$cable_status_p3.'<br> Пара 4: '.$cable_status_p4;
        $cable_lenght_total = isset($data_switch['cable_lenght'])? $data_switch['cable_lenght'] : '-';
        $cable_lenght_p1 = isset($data_switch['cable_lenght_p1'])? $data_switch['cable_lenght_p1'] : '-';
        $cable_lenght_p2 = isset($data_switch['cable_lenght_p2'])? $data_switch['cable_lenght_p2'] : '-';
        $cable_lenght_p3 = isset($data_switch['cable_lenght_p3'])? $data_switch['cable_lenght_p3'] : '-';
        $cable_lenght_p4 = isset($data_switch['cable_lenght_p4'])? $data_switch['cable_lenght_p4'] : '-';
        $cable_lenght = $cable_lenght_total.' / ('.$cable_lenght_p1.' / '.$cable_lenght_p2.' / '.$cable_lenght_p3.' / '.$cable_lenght_p4.')';
        $mac_int = '';
        if(!empty($data_switch['mac'])){

            foreach($data_switch['mac'] as $v){
                $mac_int .= base_convert($v, 16, 10).',';
            }
            $mac_int = trim($mac_int,',');
        }else{
            $mac_int = 'Нет данных';
        }

        $switch_ip_int = ip2long($data_db['switch_ip']);
        $placeholders = array(
            'date_time' => strtotime(date('Y-m-d h:i:s')),
            'account_id' => $account_id ? $account_id : $data_db['user_id'],
            'switch_ip' => $switch_ip_int,
            'mac' => $mac_int,
            'port' => $data_db['port'],
            'switch_model' => $data_db['switch_model'],
            'firmware' => $data_db['firmware'],
            'port_status' => $data_switch['port_status'],
            'counter_byte_in' => $data_switch['counter_byte_in'],
            'counter_byte_out' => $data_switch['counter_byte_out'],
            'counter_pkts_unicast_in' => $data_switch['counter_pkts_unicast_in'],
            'counter_pkts_unicast_out' => $data_switch['counter_pkts_unicast_out'],
            'error_in' => $data_switch['error_in'],
            'error_out' => $data_switch['error_out'],
            'duplex' => $data_switch['duplex'],
            'speed' => $data_switch['speed'],
            'last_change' => $data_switch['last_change'],
            'switch_id' => $data_db['switch_id'] ? $data_db['switch_id'] : $switch_id,
            'temperature' => $data_switch['temperature'],
            'ref_sw_id' => $data_db['ref_sw_id'] == -1 ? 'Нет данных' : $data_db['ref_sw_id'],
            'cable_status' => $cable_status,
            'cable_lenght' => $cable_lenght,




        );

        $dbc = Connect_db::getConnection();
        $sql = "INSERT INTO `users_history`(`date_time`,`account_id`, `switch_ip`, `mac`, `port`, `switch_model`, `firmware`,
 `port_status`, `counter_byte_in`, `counter_byte_out`,`counter_pkts_unicast_in`, `counter_pkts_unicast_out`, `error_in`, `error_out`, `duplex`,`speed`, `last_change`, `switch_id`, `temperature`, `ref_sw_id`, `cable_status`, `cable_lenght`)
  VALUES (:date_time,:account_id, :switch_ip, :mac, :port, :switch_model, :firmware, :port_status, :counter_byte_in,
  :counter_byte_out, :counter_pkts_unicast_in, :counter_pkts_unicast_out, :error_in, :error_out, :duplex, :speed, :last_change, :switch_id, :temperature, :ref_sw_id, :cable_status, :cable_lenght)";

        $sth = $dbc->getPDO()->prepare($sql);

        $sth->execute($placeholders);


    }

    public function selectData($account_id,$switch_id, $port_id)
    {
        $dbc = Connect_db::getConnection();
        if($account_id){
        $sql = "SELECT * FROM `users_history` WHERE `account_id`= :account_id";
        $placeholders = array(
            'account_id' => $account_id
        );
        }elseif($switch_id && $port_id){

            $sql = "SELECT * FROM `users_history` WHERE `switch_id`= :switch_id AND `port`= :port_id";
            $placeholders = array(
                'switch_id' => $switch_id,
                'port_id' => $port_id
            );

        }else{
            throw new Exception('Нет данных account_id, switch_id и port_id',1);
        }
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