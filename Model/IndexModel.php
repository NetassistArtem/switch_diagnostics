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

        );

        return $data;
    }

    private function getDataByID($account_id, $switch_ip = null, $mac = null)
    {
        $dbc = Connect_db::getConnection(2);
        $sql = "SELECT `switch_ip`, `mac`, `port` FROM `users` WHERE `id`= :account_id";
        $placeholders = array(
            'account_id' => $account_id
        );
        $d = $dbc->getDate($sql, $placeholders);


        if (!isset($d['0']['switch_ip']) || !isset($d['0']['mac'])) {
            $message = '';
            if (!isset($d['0']['switch_ip']) && !isset($d['0']['mac'])) {
                $message = 'Not found in data base switch_ip and mac-adress for user with account id = ' . $account_id;
            }
            elseif(!isset($d['0']['switch_ip'])){
                $message = 'Not found in data base switch_ip for user with account id = ' . $account_id;
            }
            elseif(!isset($d['0']['mac'])){
                $message = 'Not found in data base mac-adress for user with account id = ' . $account_id;
            }
            throw new Exception($message, 1);
        }

        $switch_ip_read = long2ip($d['0']['switch_ip']);
        $mac_read = base_convert($d['0']['mac'], 10, 16);

        $data = array(
            'switch_ip' => $switch_ip ? $switch_ip : $switch_ip_read,
            'mac' => $mac ? $mac : $mac_read,
            'port' => $d['0']['port']
        );


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