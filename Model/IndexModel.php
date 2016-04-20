<?php


class IndexModel
{
    private $switch_mac;

    /*
      public function testConnect()
      {
          $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);
        //  $snmp->getByKey(Config::get('oid_switch_model'));
      }

  */
    private function dlinkMacAll($mac_data, $port_coefficient)
    {
        $mac_port_array = array();
        foreach ($mac_data as $k => $v) {
            if (strpos($k, 'iso.3.6.1.2.1.17.7.1.2.2.1.2.1.') !== false) {
                $mac_10 = str_replace('iso.3.6.1.2.1.17.7.1.2.2.1.2.1.', '', $k);
                $mac_parts_array = explode('.', $mac_10);
                $mac_16 = '';
                foreach ($mac_parts_array as $val) {
                    $mac_16 .= dechex($val) . ':';
                }
                $mac_16 = trim($mac_16, ':');
                $mac_port_array[$mac_16] = str_replace('INTEGER: ', '', $v) + $port_coefficient;;
            }
        }
        //$mac_data = array_flip($mac_data);

        return $mac_port_array;
    }

    private function switchData()
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM  `switches`";
        $placeholders = array();
        $data = $dbc->getDate($sql, $placeholders);
        return $data;

    }

    public function snmpData($account_id, $key)
    {
        if (!$this->switch_mac) {
            $this->switch_mac = $this->getDataByID($account_id);
        }
        $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);


        $switch_data = $snmp->getByKey($key);

        $data = array(

            'key' => $switch_data,
            'switch_ip' => $this->switch_mac['switch_ip'],
            'mac' => $this->switch_mac['mac'],
            'port' => $this->switch_mac['port'],
            'switch_model' => $this->switch_mac['switch_model'],
            'firmware' => $this->switch_mac['firmware'],
            'manufacturer' => $this->switch_mac['manufacturer']


        );

        return $data;
    }

    private function getDataByID($account_id, $switch_ip = null, $mac = null, $port = null, $switch_model = null, $firmware = null, $manufacturer = null)
    {
        if(Config::get('mode') == 'test'){
            return $this->getDataByIDtest($account_id, $switch_ip, $mac, $port, $switch_model, $firmware, $manufacturer);
        }else{
            return $this->getDataByIDprod($account_id, $switch_ip, $mac, $port, $switch_model, $firmware, $manufacturer);
        }
    }





    private function getDataByIDprod($account_id, $switch_ip = null, $mac = null, $port = null, $switch_model = null, $firmware = null, $manufacturer = null)
    {

        $dbc = Connect_db::getConnection(3);


        $sql = "SELECT pl.sw_id, pl.port_id AS port, sl.sw_ip AS switch_ip, sl.sw_model AS switch_mod_manuf, ul.user_mac AS mac FROM port_list pl JOIN sw_list sl
JOIN user_list ul ON pl.ref_user_id = :account_id AND pl.sw_id = sl.sw_id AND pl.ref_user_id = ul.user_id";
        $placeholders = array(
            'account_id' => $account_id
        );

        $d = $dbc->getDate($sql, $placeholders);

        $switch_data_db = $this->switchData();
        $switch_mod_manuf = strtolower($d[0]['switch_mod_manuf']);

        foreach ($switch_data_db as $v){

            if(strpos($switch_mod_manuf, strtolower($v['model_name']) )!== false){
                $d[0]['switch_model'] = $v['model_name'];
                $d[0]['manufacturer']= $v['manufacturer'];
                $d[0]['firmware']= $v['firmware'];
            }
        }
        if(!$d[0]['switch_model']){
            throw new Exception('Модель свича отсутсвтует в базе данных. Введите модель свича и соответствующий ей шаблон SNMP запросов (ссылка на добавление свича)',1);
        }

        $data = array(
            'switch_ip' => $switch_ip ? $switch_ip : $d['0']['switch_ip'],
            'mac' => $mac ? $mac : $d['0']['mac'],
            'port' => $port ? $port : $d['0']['port'],
            'switch_model' => $switch_model ? $switch_model : $d['0']['switch_model'],
            'firmware' => $firmware ? $firmware : $d['0']['firmware'],
            'manufacturer' => $manufacturer ? $manufacturer : $d['0']['manufacturer']
        );


        if (!$data['port'] || !$data['switch_ip']) {
            $message = '';
            if (!$data['port'] || !$data['switch_ip']) {
                $message = 'Not found  switch_ip and switch port for user with account id = ' . $account_id;
            } elseif (!$data['switch_ip']) {
                $message = 'Not found  switch_ip for user with account id = ' . $account_id;
            } elseif (!$data['port']) {
                $message = 'Not found switch port for user with account id = ' . $account_id;
            }
            throw new Exception($message, 1);
        }
        if (!$data['mac']) {
            Session::setFlash('Not found in data base mac-adress for user with account id = ' . $account_id . '.');
        }

        return $data;
    }




    private function getDataByIDtest($account_id, $switch_ip = null, $mac = null, $port = null, $switch_model = null, $firmware = null, $manufacturer = null)
    {
        $dbc = Connect_db::getConnection(2);
          $sql = "SELECT `switch_ip`, `mac`, `port` ,`switch_model`, `firmware`, `manufacturer` FROM `users` WHERE `id`= :account_id";

        $placeholders = array(
            'account_id' => $account_id
        );
        $d = $dbc->getDate($sql, $placeholders);

        $switch_ip_read = long2ip($d['0']['switch_ip']);

        $mac_r = base_convert($d['0']['mac'], 10, 16);

        $mac_read = implode(":", str_split($mac_r, 2));


        $data = array(
            'switch_ip' => $switch_ip ? $switch_ip : $switch_ip_read,
            'mac' => $mac ? $mac : $mac_read,
            'port' => $port ? $port : $d['0']['port'],
            'switch_model' => $switch_model ? $switch_model : $d['0']['switch_model'],
            'firmware' => $firmware ? $firmware : $d['0']['firmware'],
            'manufacturer' => $manufacturer ? $manufacturer : $d['0']['manufacturer']
        );


        if (!$data['port'] || !$data['switch_ip']) {
            $message = '';
            if (!$data['port'] || !$data['switch_ip']) {
                $message = 'Not found  switch_ip and switch port for user with account id = ' . $account_id;
            } elseif (!$data['switch_ip']) {
                $message = 'Not found  switch_ip for user with account id = ' . $account_id;
            } elseif (!$data['port']) {
                $message = 'Not found switch port for user with account id = ' . $account_id;
            }
            throw new Exception($message, 1);
        }
        if (!$data['mac']) {
            Session::setFlash('Not found in data base mac-adress for user with account id = ' . $account_id . '.');
        }

        return $data;
    }










    public function getAllMac($account_id, $pattern_id, $port_coefficient)
    {
        $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);
        $patternModel = new patternModel($account_id);

        $mac_port = $patternModel->macData($pattern_id);

        $mac_port['mac_all'] = '.' . trim($mac_port['mac_all'], '.');
        //$mac_port['macs_ports'] = '.' . trim($mac_port['macs_ports'], '.');
        $snmp->getSnmpSession()->valueretrieval = SNMP_VALUE_LIBRARY;
        //  Debugger::PrintR($mac_port);

        $mac_data = $snmp->walkByKey($mac_port['mac_all']);

        if ($this->switch_mac['manufacturer'] == 'D-Link') {
          return  $this->dlinkMacAll($mac_data, $port_coefficient);

        } else {


            $reg = "/([0-9a-fA-F]{2}([:-\s]|$)){6}$|([0-9a-fA-F]{4}([.]|$)){3}/";

            $mac_array = array();
            $port_array = array();
            foreach ($mac_data as $v) {
                if (preg_match($reg, $v, $matches)) {

                    $mac_array[] = $matches[0];
                } else {

                    $port_array[] = str_replace('INTEGER: ', '', $v) + $port_coefficient;
                }
            }
            $aray_count = count($mac_array);
            //  Debugger::PrintR($mac_array);


            $port_array = array_slice($port_array, 0, $aray_count);

            // Debugger::PrintR($port_array);


            //  $snmp->getSnmpSession()->valueretrieval = SNMP_VALUE_PLAIN;

            //$mac_port_data = $snmp->walkByKey($mac_port['macs_ports']);

            //  Debugger::PrintR($mac_port_data);

            //    $port_array = array_slice($mac_port_data,0, $aray_count);

            // Debugger::PrintR($port_array);

            $mac_port_a = array_combine($mac_array, $port_array);
            $mac_port_array = array();
            foreach ($mac_port_a as $k => $v) {
                $k = strtolower(trim($k));
                if ($v != 0) {
                    if (strpos($k, ' ')) {
                        $mac_port_array[str_replace(' ', ':', $k)] = $v;
                    }
                    if (strpos($k, '-')) {
                        $mac_port_array[str_replace('-', ':', $k)] = $v;
                    }
                }
            }

        }

        return $mac_port_array;

    }

    public function cableTest($account_id, $pattern_id, $port, $switch_manufacturer)
    {
        if (!$this->switch_mac) {
            $this->switch_mac = $this->getDataByID($account_id);
        }



        $patternModel = new patternModel($account_id);
        $object_id = $patternModel->PatternData($port, $pattern_id)['cable_test_start'];
        if ($object_id) {
            $write_test = 1;
            if (strtoupper($switch_manufacturer) == 'ELTEX') {
                $write_test = 2;
            }

            $snmp = new Connect_SNMP($this->switch_mac['switch_ip'], 'w');
            $snmp->setData($object_id, 'i', $write_test);

            sleep(Config::get('timeout_cabletest')[$switch_manufacturer]);
        } else {
            Session::setFlash('Тест кабеля для свича ' . $this->switch_mac['switch_model'] . ' ' . $this->switch_mac['manufacturer'] . ' ' . $this->switch_mac['firmware'] . ' на данный момент не доступен (отсутствует необходимый OID)',"notice");
        }
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

    public function snmpByKey($account_id, $key)
    {
        if (!$this->switch_mac) {
            $this->switch_mac = $this->getDataByID($account_id);
        }

        $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);
        $data = $snmp->walkByKey($key);
        return $data;
    }


}