<?php


class IndexModel
{
    private $switch_mac;
    private static $port_coeff = array();

    /*
      public function testConnect()
      {
          $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);
        //  $snmp->getByKey(Config::get('oid_switch_model'));
      }

  */
    private function extractMac($mac_data, array $port_coefficient, $mac_oid)
    {
        $mac_port_array = array();
        $mac_oid = trim($mac_oid, '.');
        $mac_oid = substr_replace($mac_oid, 'iso', 0, 1);


        $patternModel = new patternModel(null);
        $data_switch = $patternModel->getSwitchDataByName($this->switch_mac['switch_model']);
        // Debugger::PrintR($port_coefficient);


        foreach ($mac_data as $k => $v) {
            if (strpos($k, $mac_oid) !== false) {

                $mac_p = array_reverse(explode('.', $k));

                $mac_parts_array = array();
                for ($i = 0; $i < 6; $i++) {
                    $mac_parts_array[] = $mac_p[$i];
                }

                $mac_16 = '';
                $mac_parts_array = array_reverse($mac_parts_array);

                foreach ($mac_parts_array as $val) {
                    $number = str_pad(dechex($val), 2, '0', STR_PAD_LEFT);
                    $mac_16 .= $number . ':';
                }
                $mac_16 = trim($mac_16, ':');
                $port = str_replace('INTEGER: ', '', $v);

                // у ELTEX и у hawei для старой версии прошивки, указание портов для макадресов идут с коэфициентом (не путать с именованиями портов для формирования всех остальных оидов)
                if (strtolower($this->switch_mac['manufacturer']) == 'eltex' || strtolower($this->switch_mac['manufacturer']) == 'huawei') {
                    $port_coef = ($port <= $data_switch[0]['simple_port'] || $port == 0) ? $port_coefficient['port_coefficient'] : $port_coefficient['gig_port_coefficient'];

                } else {


                    $port_coef = '';

                }

                //(strtolower($this->switch_mac['manufacturer'])=='eltex') ? $port_coefficient :'';


                $mac_port_array[$mac_16] = $port - $port_coef;
                //  echo $port;
            }
        }

//Debugger::PrintR($mac_port_array);
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

    public function snmpData($account_id, $key, $variable=null)
    {
        if (!$this->switch_mac) {
            $this->switch_mac = $this->getDataByID($account_id);
        }
        $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);


        $switch_data = $snmp->getByKey($key);


        if($variable[$switch_data]){
            $switch_data = $variable[$switch_data];
        }


        $data = array(

            'key' => $switch_data,
            'switch_ip' => $this->switch_mac['switch_ip'],
            'mac' => $this->switch_mac['mac'],
            'port' => $this->switch_mac['port'],
            'switch_model' => $this->switch_mac['switch_model'],
            'firmware' => $this->switch_mac['firmware'],
            'manufacturer' => $this->switch_mac['manufacturer'],
            'snmp' => $this->switch_mac['snmp'],
            'write_community' => $this->switch_mac['write_community']


        );

        return $data;
    }

    private function getDataByID($account_id, $switch_ip = null, $mac = null, $port = null, $switch_model = null, $firmware = null, $manufacturer = null)
    {
        if (Config::get('mode') == 'test') {
            return $this->getDataByIDtest($account_id, $switch_ip, $mac, $port, $switch_model, $firmware, $manufacturer);
        } else {
            return $this->getDataByIDprod($account_id, $switch_ip, $mac, $port, $switch_model, $firmware, $manufacturer);
        }
    }

    private function getDataByIDprod($account_id, $switch_ip = null, $mac = null, $port = null, $switch_model = null, $firmware = null, $manufacturer = null)
    {

        $dbc = Connect_db::getConnection(3);


        $sql = "SELECT pl.sw_id, pl.port_id AS port, sl.sw_ip AS switch_ip,  sl.sw_model  AS switch_mod_manuf, ul.user_mac AS mac, sl.use_snmp AS snmp, sl.snmp_auth_rw AS write_community  FROM port_list pl JOIN sw_list sl
JOIN user_list ul ON pl.ref_user_id = :account_id AND pl.sw_id = sl.sw_id AND pl.ref_user_id = ul.user_id";
        $placeholders = array(
            'account_id' => $account_id
        );

        $d = $dbc->getDate($sql, $placeholders);


        // $d[0]['switch_mod_manuf'] = iconv('latin1','utf8', $d[0]['switch_mod_manuf']);
        //временный костыль
        $d[0]['switch_mod_manuf'] = str_replace(" ", "", $d[0]['switch_mod_manuf']);
        $d[0]['switch_mod_manuf'] = str_replace('å', 'E', $d[0]['switch_mod_manuf']);
        //окончание временного костыля
      //  Debugger::PrintR($d);

        $switch_data_db = $this->switchData();
        $switch_mod_manuf = strtolower($d[0]['switch_mod_manuf']);

        foreach ($switch_data_db as $v) {

            if (strpos($switch_mod_manuf, strtolower($v['model_name'])) !== false) {
                $d[0]['switch_model'] = $v['model_name'];
                $d[0]['manufacturer'] = $v['manufacturer'];
                $d[0]['firmware'] = $v['firmware'];
            }
        }

        if (!$d[0]['switch_model']) {

            throw new Exception('Модель свича отсутсвтует в базе данных. Введите модель свича и соответствующий ей шаблон SNMP запросов', 1);
        }
        if(!$d[0]['snmp']){

            throw new Exception('SNMP отключено в запрашиваемом свиче (информация из базы данных биллинга).',1);
        }


        $data = array(
            'switch_ip' => $switch_ip ? $switch_ip : $d['0']['switch_ip'],
            'mac' => $mac ? $mac : $d['0']['mac'],
            'port' => $port ? $port : $d['0']['port'],
            'switch_model' => $switch_model ? $switch_model : $d['0']['switch_model'],
            'firmware' => $firmware ? $firmware : $d['0']['firmware'],
            'manufacturer' => $manufacturer ? $manufacturer : $d['0']['manufacturer'],
            'write_community' => $d['0']['write_community'],
            'snmp' => $d['0']['snmp']

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

        $data['manufacturer'] = strtolower($data['manufacturer']);
        $data['manufacturer'] = ucfirst($data['manufacturer']);

        return $data;
    }


    public function getAllMac($account_id, $pattern_id, array $port_coefficient)
    {
        $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);
        $patternModel = new patternModel($account_id);



        $mac_port = $patternModel->macData($pattern_id);


        $mac_port['mac_all'] = '.' . trim($mac_port['mac_all'], '.');
        $snmp->getSnmpSession()->valueretrieval = SNMP_VALUE_LIBRARY;


        $mac_data = $snmp->walkByKey($mac_port['mac_all']);


        return $this->extractMac($mac_data, $port_coefficient, $mac_port['mac_all']);

    }

    public function cableTest($account_id, $pattern_id, $port, $switch_manufacturer,$style_class=null)
    {

        if (!$this->switch_mac) {
            $this->switch_mac = $this->getDataByID($account_id);
        }


        $patternModel = new patternModel($account_id);
        $object_id = $patternModel->PatternData($port, $pattern_id)['cable_test_start'];

        if ($object_id) {
            $write_test = 1;
            if (strtolower($switch_manufacturer) == 'eltex') {
                $write_test = 2;
            }

            $snmp = new Connect_SNMP($this->switch_mac['switch_ip'], 'w');



            $snmp->setData($object_id, 'i', $write_test);

            sleep(Config::get('timeout_cabletest')[$switch_manufacturer]);
        } else {
            Session::setFlash('Тест кабеля для свича ' . $this->switch_mac['switch_model'] . ' ' . $this->switch_mac['manufacturer'] . ' ' . $this->switch_mac['firmware'] . ' на данный момент не доступен (отсутствует необходимый OID)', $style_class['notice']);
        }
    }

    public function getCommunity()
    {

        $dbc = Connect_db::getConnection(3);
        $sql = 'SELECT sw.snmp_auth, sw.use_snmp FROM sw_list sw JOIN port_list pl ON sw.sw_id = pl.sw_id AND pl.ref_user_id = :account_id';
        $placeholders = array(
            'account_id' => Router::getAccountId()
        );
        $data = $dbc->getDate($sql, $placeholders);
        return $data[0];
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

    public function userIdByPort($port, $switch_id)
    {
        $dbc = Connect_db::getConnection(3);
        $sql = 'SELECT `ref_user_id` FROM `port_list` WHERE `sw_id`= :switch_id AND `port_id`=:port_id';
        $placeholders = array(
            'port_id' => $port,
            'switch_id' => $switch_id
        );
        $data = $dbc->getDate($sql, $placeholders);


        return $data[0]['ref_user_id'];
    }


    public function getPortCoefficient($account_id, $port_number)
    {

        $patternModel = new patternModel($account_id);

        $data_switch = $patternModel->getSwitchDataByName($this->switch_mac['switch_model']);

        if ($data_switch[0]['model_name'] != 'DGS-3200-10') {

            $snmp = new Connect_SNMP($this->switch_mac['switch_ip']);
            $data_port_index = $snmp->walkByKey('.1.3.6.1.2.1.17.1.4.1.2');
            $port_index = array();
            $i = 0;
            foreach ($data_port_index as $v) {
                $i += 1;
                $port_index[$i] = $v;
            }

            $port_coefficient['port_coefficient'] = $port_index[1] - 1;
            $port_coefficient['gig_port_coefficient'] = $port_index[$data_switch[0]['simple_port'] + 1] - ($data_switch[0]['simple_port'] + 1);
            if ($port_number <= $data_switch[0]['simple_port']) {

                $port_coefficient['port_coefficient_simple_gig'] = $port_index[1] - 1;
            } else {

                $port_coefficient['port_coefficient_simple_gig'] = $port_index[$data_switch[0]['simple_port'] + 1] - ($data_switch[0]['simple_port'] + 1);
            }
        }elseif($data_switch[0]['model_name'] == 'DGS-3200-10'){
            $port_coefficient = array(
                'port_coefficient' =>0,
                'gig_port_coefficient' =>0,
                'port_coefficient_simple_gig' =>0,
            );
        }else{
            $port_coefficient = array();
        }
        self::$port_coeff = $port_coefficient;

        return $port_coefficient;

    }

    /**
     * @return array
     */
    public static function getPortCoeff()
    {
        return self::$port_coeff;
    }


}