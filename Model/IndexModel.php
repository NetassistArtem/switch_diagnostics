<?php


class IndexModel
{


    public function snmpData($account_id, $key)
    {
        $switch_mac = $this->getDataByID($account_id);

        $snmp = new Connect_SNMP($switch_mac['switch_ip']);
        $switch_data = $snmp->getByKey($key);

        $data = array(

            'key' => $switch_data,
            'switch_ip' => $switch_mac['switch_ip'],
            'mac' => $switch_mac['mac'],
            'port' => $switch_mac['port']


        );

        return $data;
    }

    private function getDataByID($account_id, $switch_ip = null, $mac = null, $port = null)
    {
        $dbc = Connect_db::getConnection(2);
        $sql = "SELECT `switch_ip`, `mac`, `port` FROM `users` WHERE `id`= :account_id";
        $placeholders = array(
            'account_id' => $account_id
        );
        $d = $dbc->getDate($sql, $placeholders);

        $switch_ip_read = long2ip($d['0']['switch_ip']);
        $mac_read = base_convert($d['0']['mac'], 10, 16);

        $data = array(
            'switch_ip' => $switch_ip ? $switch_ip : $switch_ip_read,
            'mac' => $mac ? $mac : $mac_read,
            'port' => $port ? $port : $d['0']['port']
        );


        if (!$data['port'] || !$data['switch_ip']) {
            $message = '';
            if (!$data['port'] || !$data['switch_ip']) {
                $message = 'Not found  switch_ip and switch port for user with account id = ' . $account_id;
            }
            elseif(!$data['switch_ip']){
                $message = 'Not found  switch_ip for user with account id = ' . $account_id;
            }
            elseif(!$data['port']){
                $message = 'Not found switch port for user with account id = ' . $account_id;
            }
            throw new Exception($message, 1);
        }
        if(!$data['mac']){
            Session::setFlash('Not found in data base mac-adress for user with account id = '.$account_id.'.');
        }

            return $data;


    }

    public function indexPage($id)
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM  pages WHERE id= :id";
        $placeholders = array(
            'id' => $id
        );
        $data = $dbc->getDate($sql, $placeholders);

        return $data;
    }


}