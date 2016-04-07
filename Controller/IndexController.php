<?php


class IndexController extends Controller
{
    public $account_id;

    //  private $repetition;

    private function findPattern($data_switch)
    {


        $patternModel = new patternModel($this->account_id);
        if ($data_switch) {
            $switch = $patternModel->switchData();
            //  Debugger::PrintR($switch);
            // Debugger::PrintR($data_switch);

            $switch_manufacturer = array();
            $switch_model = array();
            $switch_soft_version = array();
            $switch_data_result = array();
            foreach ($switch as $v) {
                $switch_manufacturer[] = $v['manufacturer'];
            }
            $switch_manufacturer = array_unique($switch_manufacturer);


            foreach ($switch_manufacturer as $val) {

                if (stripos($data_switch['key'], $val) !== false) {
                    $switch_data_result['manufacturer'] = $val;
                }

            }


            foreach ($switch as $v) {
                $switch_model[] = $v['model_name'];
            }
            $switch_model = array_unique($switch_model);


            foreach ($switch_model as $val) {

                if (stripos($data_switch['key'], $val) !== false) {

                    $switch_data_result['model_name'] = $val;
                }
            }


            foreach ($switch as $v) {
                if ($v['model_name'] == $switch_data_result['model_name']) {
                    $switch_soft_version[] = $v['firmware'];
                }
            }
            if (!empty($switch_soft_version)) {
                foreach ($switch_soft_version as $val) {

                    if (stripos($data_switch['key'], $val) !== false) {
                        $switch_data_result['soft_version'] = $val;
                    } elseif (strtoupper($data_switch['manufacturer']) == 'ELTEX') {


                        $indexModel = new IndexModel();


                        $soft_ver_array = $indexModel->snmpByKey($this->account_id, '1.3.6.1.4.1.89.2.4');


                        foreach ($soft_ver_array as $v) {

                            $switch_data_result['soft_version'] = 'Version ' . $v;
                        }


                    }

                }
            }


            if (!empty($switch_data_result)) {
                if (!$switch_data_result['manufacturer']) {
                    Session::setFlash('В полученных со свича данных отсутствует информация о производители свича.');
                }
                if (!$switch_data_result['soft_version']) {
                    Session::setFlash('В полученных со свича данных отсутствует информация о версии прошивки свича.');
                    $patterns_array = array();
                    foreach ($switch as $v) {
                        if ($switch_data_result['model_name'] == $v['model_name']) {
                            $patterns_array[] = $v['pattern_id'];
                        }
                    }
                    $patterns_array = array_unique($patterns_array);
                    if ($patterns_array[1]) {
                        Session::setFlash('Сущствует несколько шаблонов для данного свича! Использован первый из имеющихся
                         шаблонов');
                    }

                    return $patterns_array[0];
                }

                if ($switch_data_result['manufacturer'] || $switch_data_result['soft_version'] || $switch_data_result['model_name']) {


                    foreach ($switch as $v) {

                        if ($switch_data_result['manufacturer'] == $v['manufacturer'] && $switch_data_result['model_name'] == $v['model_name'] && $switch_data_result['soft_version'] == $v['firmware']) {
                            return $v['pattern_id'];
                        } elseif ($switch_data_result['model_name'] == $v['model_name'] && $switch_data_result['soft_version'] == $v['firmware']) {

                            Session::setFlash('Данные со свича не содержат информации о производители свича');
                            return $v['pattern_id'];
                        } elseif ($switch_data_result['model_name'] == $v['model_name']) {

                            Session::setFlash('Данные со свича не содержат информации о производители свича м версии прошивки');
                            return $v['pattern_id'];
                        }

                    }

                }


            } else {
                Session::setFlash('Данные со свича не содержат информацию о производители, наименовании и версии прошивки свича.
                 Для определения ID шаблона использованны данные о
            модели и прошивки из базы данных биллинга');
                $pattern_number = $patternModel->getPatternUserData();
                if ($pattern_number['pattern_id']) {
                    return $pattern_number['pattern_id'];
                } else {
                    Session::setFlash('Данные со свича не содержат информацию о производители, наименовании и версии
                    прошивки свича. Информация о модели свича в базе данных билинга для
                 запрашиваемого пользователя отсутствуют');
                }

            }


        } else {

            Session::setFlash('Данные со свича не были полученны. Для определения ID шаблона использованны данные о
            модели и прошивки из базы данных биллинга');
            $pattern_number = $patternModel->getPatternUserData();
            if ($pattern_number['pattern_id']) {
                return $pattern_number['pattern_id'];
            } else {
                Session::setFlash('Данные со свича не были получены. Информация о модели свича в базе данных билинга для
                 запрашиваемого пользователя отсутствуют');
            }

        }

        return null;
    }


    public function indexAction()
    {


        $nodeModel = new NodeModel();
        $node_data = $nodeModel->indexPage(4);
        $indexModel = new IndexModel();

        $request = new Request();
        if ($request->isPost()) {


            return $this->snmpDataAction($request->post('account_id'), 'snmpData');

            //  добавить другие проверки - есть ли такой пользователь допустим

        }
        $args = array(
            'node_data' => $node_data[0]
        );

        return $this->render($args);
    }


    public function snmpDataAction($account_id = null, $tpl = null)
    {


      // $test = new helperModel();
       // $test->insertMac(501, "88:ae:1d:c9:78:78", "10.4.0.100", 7, "DES-1210-28/ME", "6.02.011", "D-Link");

        //    $indexModel = new IndexModel();
        //  $indexModel->testConnect();

        require LIB_DIR . 'cableStatus.php';

        $historyModel = new historyModel();
        $historyModel->cleanHistory();

        $indexModel = new IndexModel();
        $this->account_id = $account_id ? $account_id : Router::getAccountId();


        $d = $indexModel->snmpData($this->account_id, Config::get('oid_switch_model'));
        // Debugger::PrintR($d);
        $pattern_id = $this->findPattern($d);


        $patternModel = new patternModel($this->account_id);

        $port_coefficient = $patternModel->getPortCoefficient($pattern_id)['port_coefficient'];


        $mac_port_array = $indexModel->getAllMac($this->account_id, $pattern_id, $port_coefficient);
        //  Debugger::PrintR($mac_port_array);


        if ($d['mac']) {
            if (array_key_exists($d['mac'], $mac_port_array)) {
                $port_db = $d['port'] + $port_coefficient;

                $port_switch = $mac_port_array[$d['mac']];


                $p_s = $port_switch - $port_coefficient;

                if ($port_switch != $port_db) {
                    // echo $port_switch.PHP_EOL;
                    // echo $port_db;

                    Session::setFlash("В базе данных билинга указан не правильный порт! В базе данных порт
                    - " . $d['port'] . " По данным свича - $p_s Для получения данных исползуется порт $p_s");
                    $d['port'] = $p_s;

                }

            } else {
                Session::setFlash("Мак адрес пользователя $this->account_id  указанный в базе даных билинга не обнаружен в данных свича");
            }

        } else {
            Session::setFlash("Мак адрес в базе даных билинга для пользователя $this->account_id отсутствует");
        }

        if (Config::get('cabletest_on_off') == 'on') {

            $indexModel->cableTest($this->account_id, $pattern_id, $d['port'], $d['manufacturer']);
        }

        $pattern_data = $patternModel->PatternData($d['port'], $pattern_id);


        $oids = array();
        foreach ($pattern_data as $k => $v) {
            if ($k != 'id' && $k != 'port_coefficient' && $k != 'mac_all' && $k != 'macs_ports') {
                $oids[$k] = $v;
            }
        }
        //  Debugger::PrintR($oids);

        if ($d['manufacturer'] == 'ELTEX') {
            $oids['cable_status'] = $oids['cable_status'] . '.2';
            $oids['cable_lenght'] = $oids['cable_lenght'] . '.3';

        }

        //  die('ups');
        $data = $indexModel->snmpData($this->account_id, $oids);

        $oids = array_flip($oids);
        $data_switch = array_combine($oids, $data['key']);

        $mac_arr = array();
        foreach ($mac_port_array as $k => $v) {
            $val = $v - $port_coefficient;

            if ($val == $d['port']) {
                $mac_arr[] = $k;
            }
        }
        $data_switch['mac'] = $mac_arr;


        if ($data_switch['port_status'] == 1) {
            $data_switch['port_status'] = 'ON';
        } else {
            $data_switch['port_status'] = 'OFF';
        }
        if (isset($data_switch['last_change'])) {

            $data_switch['last_change'] = date(' Y-m-d h:i:m', mktime(0, 0, -($data_switch['last_change'])));
        }
        if (isset($data_switch['speed'])) {

            $data_switch['speed'] = $data_switch['speed'] / 1000000;
        }
        if (isset($data_switch['cable_status'])) {
            $data_switch['cable_status'] = $cable_status[$d['manufacturer']][$data_switch['cable_status']];

            /*
            switch ($data_switch['cable_status']) {
                case 1:
                    $data_switch['cable_status'] = 'normal';
                    break;
                case 2:
                    $data_switch['cable_status'] = 'обрыв';
                    break;
                case 3:
                    $data_switch['cable_status'] = 'замыкание';
                    break;
                case 4:
                    $data_switch['cable_status'] = 'замыкание или обрыв';
                    break;
                case 5:
                    $data_switch['cable_status'] = 'перепутаны пары';
                    break;
                case 6:
                    $data_switch['cable_status'] = 'неизвестно';
                    break;
                case 7:
                    $data_switch['cable_status'] = 'не поддерживается';
                    break;

            }*/

        };

        // Debugger::PrintR($data_switch);
        unset($data['key']);

        $data['port'] = $d['port'];
        //Debugger::PrintR($data);
        //  if (!$data) {
        //      throw new Exception(" SNMP data is not found", 404);

        // }
        $historyModel->insertData($this->account_id, $data_switch, $data);

        $args = array(
            'data_switch' => $data_switch,
            'data_db' => $data,
            'account_id' => $this->account_id
        );
        // Debugger::PrintR($data_switch);
        //  if($this->repetition != 1){
        //      $this->repetition = 1;
        //      $this->snmpDataAction();
        //  }


        return $this->render($args, $tpl);

    }

    public function historyAction($account_id = NULL, $tpl = null)
    {


        $this->account_id = $account_id ? $account_id : Router::getAccountId();
        $historyModel = new historyModel();

        $historyModel->cleanHistory();


        $data_history = $historyModel->selectData($this->account_id);

        $data_history = array_reverse($data_history);


        foreach ($data_history as $k => $v) {

            $data_history[$k]['switch_ip'] = long2ip($v['switch_ip']);
            $mac = base_convert($v['mac'], 10, 16);
            $data_history[$k]['mac'] = implode(":", str_split($mac, 2));
            $data_history[$k]['date_time'] = date('Y-m-d h:i:s', $v['date_time']);
        }


        if (!$data_history) {
            throw new Exception(" History data for user $this->account_id not found", 404);

        }

        $args = array(
            'data_history' => $data_history,
            'account_id' => $this->account_id
        );
        return $this->render($args, $tpl);

    }

    public function historySelectAction()
    {
        $nodeModel = new NodeModel();
        $node_data = $nodeModel->indexPage(7);


        $request = new Request();
        if ($request->isPost()) {


            return $this->historyAction($request->post('account_id'), 'history');

            //  добавить другие проверки - есть ли такой пользователь допустим

        }
        $args = array(
            'node_data' => $node_data[0]
        );

        return $this->render($args);
    }


    public static function rewrite_file($file_path, $mode, $date)
    {
        $f = fopen($file_path, $mode);
        fwrite($f, $date);
        fclose($f);
    }


    public static function errorAction(Exception $e)
    {
        $date = date('Y-m-d H:i:s') . PHP_EOL;
        $date .= '/./ ' . $e->getCode() . PHP_EOL;
        $date .= '/./ ' . $e->getMessage() . PHP_EOL;
        $date .= '/./ ' . $e->getFile() . PHP_EOL;
        $date .= '/./ ' . $e->getLine() . PHP_EOL;
        $date .= '///';


        self::rewrite_file(APPROOT_DIR . 'log.txt', 'a', $date);
    }

}