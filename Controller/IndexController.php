<?php


class IndexController extends Controller
{
    public $account_id;
    public $style_class = array();

    public function __construct()
    {
        $this->style_class = Config::get('style_class');

    }

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
                    Session::setFlash('В полученных со свича данных отсутствует информация о производители свича.', $this->style_class['information']);
                }
                if (!$switch_data_result['soft_version']) {
                    Session::setFlash('В полученных со свича данных отсутствует информация о версии прошивки свича.', $this->style_class['information']);
                    $patterns_array = array();
                    foreach ($switch as $v) {
                        if ($switch_data_result['model_name'] == $v['model_name']) {
                            $patterns_array[] = $v['pattern_id'];
                        }
                    }
                    $patterns_array = array_unique($patterns_array);
                    if ($patterns_array[1]) {
                        Session::setFlash('Сущствует несколько шаблонов для данного свича! Использован первый из имеющихся
                         шаблонов', $this->style_class['warning']);
                    }

                    return $patterns_array[0];
                }

                if ($switch_data_result['manufacturer'] || $switch_data_result['soft_version'] || $switch_data_result['model_name']) {


                    foreach ($switch as $v) {

                        if ($switch_data_result['manufacturer'] == $v['manufacturer'] && $switch_data_result['model_name'] == $v['model_name'] && $switch_data_result['soft_version'] == $v['firmware']) {
                            return $v['pattern_id'];
                        } elseif ($switch_data_result['model_name'] == $v['model_name'] && $switch_data_result['soft_version'] == $v['firmware']) {

                            //Session::setFlash('Данные со свича не содержат информации о производители свича');
                            return $v['pattern_id'];
                        } elseif ($switch_data_result['model_name'] == $v['model_name']) {

                            Session::setFlash('Данные со свича не содержат информации о производители свича м версии прошивки', $this->style_class['information']);
                            return $v['pattern_id'];
                        }

                    }

                }


            } else {
                Session::setFlash('Данные со свича не содержат информацию о производители, наименовании и версии прошивки свича.
                 Для определения ID шаблона использованны данные о
            модели и прошивки из базы данных биллинга', $this->style_class['notice']);
                $pattern_number = $patternModel->getPatternUserData();
                if ($pattern_number['pattern_id']) {
                    return $pattern_number['pattern_id'];
                } else {
                    Session::setFlash('Данные со свича не содержат информацию о производители, наименовании и версии
                    прошивки свича. Информация о модели свича в базе данных билинга для
                 запрашиваемого пользователя отсутствуют', $this->style_class['warning']);
                }

            }


        } else {

            Session::setFlash('Данные со свича не были полученны. Для определения ID шаблона использованны данные о
            модели и прошивки из базы данных биллинга', $this->style_class['notice']);
            $pattern_number = $patternModel->getPatternUserData();
            if ($pattern_number['pattern_id']) {
                return $pattern_number['pattern_id'];
            } else {
                Session::setFlash('Данные со свича не были получены. Информация о модели свича в базе данных билинга для
                 запрашиваемого пользователя отсутствуют', $this->style_class['warning']);
            }

        }

        return null;
    }

    public function insertCableLengthAction()
    {

        $request = new Request();

        if (!$this->account_id) {
            $this->account_id = Router::getAccountId();
        }
        /*

               $cable_lenght = $request->get('cable_length') ? $request->get('cable_length') : false;


               if($cable_lenght){
                   $cableLengthModel = new cableLengthModel();
                   $cableLengthModel ->insertCableLength($this->account_id, $cable_lenght);
               }else{

                   Session::setFlash("Длинна кабеля не известна и не может быть записана. Порт пользователя должен быть в статусе 'open cable'.");
               }
               */
        $this->redirect("/account_test/" . $this->account_id . "?cable_length=write");
        //$this->snmpDataAction($this->account_id, 'snmpData');

    }

    private function cableLengthWrite($cable_length)
    {
        if ($cable_length) {

            $cableLengthModel = new cableLengthModel();

            if (empty($cableLengthModel->cableLength($this->account_id))) {

                $cableLengthModel->insertCableLength($this->account_id, $cable_length);

                Session::setFlash("Длинна кабеля для пользователя " . $this->account_id . " успешно записанна.", $this->style_class['information']);
            } else {
                $cableLengthModel->updataCableLength($this->account_id, $cable_length);
                Session::setFlash("Длинна кабеля для пользователя " . $this->account_id . " успешно перезаписана.", $this->style_class['information']);

            }

        } else {

            Session::setFlash("Длинна кабеля не известна и не может быть записана. Порт пользователя должен быть в статусе 'open cable'.", $this->style_class['notice']);
        }
    }

    private function compareCableLength($new_cable_length)
    {
        $cableLengthModel = new cableLengthModel();

        if (empty($cableLengthModel->cableLength($this->account_id))) {
            Session::setFlash("Для пользователя " . $this->account_id . " не записанна длинна кабеля. Для записи длинны кабеля, пользователь
            должен отключить кабель после чего необходимо нажать 'Записать длинну кабеля'", $this->style_class['notice']);
            return $this->style_class['notice'];
        } else {
            $saved_cable_length = $cableLengthModel->cableLength($this->account_id)[0]['cable_lenght'];
            $max_cable_lenght = $saved_cable_length + Config::get('delta_cable_langth');
            $min_cable_lenght = $saved_cable_length - Config::get('delta_cable_langth');
            if ($new_cable_length) {
                if ($new_cable_length > $max_cable_lenght || $new_cable_length < $min_cable_lenght) {
                    Session::setFlash("Длинна кабеля не правильная! Записанная ранее длинна " . $saved_cable_length . "м. ,
                полученная со свича длинна " . $new_cable_length . "м.", $this->style_class['warning']);
                    return $this->style_class['warning'];
                }
            }
        }
        return '';//'information';

    }


    public function indexAction()
    {


        $nodeModel = new NodeModel();
        $node_data = $nodeModel->indexPage(4);
        $indexModel = new IndexModel();

        $request = new Request();

        if ($request->isPost()) {

            switch ($request->post('info_type')) {
                case "with_cabletest":
                    $this->redirect("/account_test/" . $request->post('account_id') . "?cabletest=on");
                    break;
                case 'without_cabletest':
                    $this->redirect("/account_test/" . $request->post('account_id') . "?cabletest=off");
                    break;
                case 'standart_cabletest':
                    $this->redirect("/account_test/" . $request->post('account_id') . "?cabletest=onoff");
                    break;
                case "history":
                    $this->redirect("/account_test/history/" . $request->post('account_id'));
                    break;
            }


            //return $this->snmpDataAction($request->post('account_id'), 'snmpData');

            //  добавить другие проверки - есть ли такой пользователь допустим


        }
        $args = array(
            'node_data' => $node_data[0],
            'date' => date('Y_m_d')
        );

        return $this->render($args);
    }


    public function snmpDataAction($account_id = null, $tpl = null)
    {


        // $test = new helperModel();
        // $test->insertMac(501, "88:ae:1d:c9:78:78", "10.4.0.100", 7, "DES-1210-28/ME", "6.02.011", "D-Link");

        //    $indexModel = new IndexModel();
        //  $indexModel->testConnect();

        require LIB_DIR . 'responseValue.php';

        $historyModel = new historyModel();
        $historyModel->cleanHistory();

        $indexModel = new IndexModel();
        $this->account_id = $account_id ? $account_id : Router::getAccountId();


        $d = $indexModel->snmpData($this->account_id, Config::get('oid_switch_model'));


        //Debugger::PrintR($d);
        $pattern_id = $this->findPattern($d);


        $patternModel = new patternModel($this->account_id);

       // $port_coefficient = $patternModel->getPortCoefficient($pattern_id, $d['port'], $d['switch_model'])['port_coefficient_simple_gig'];
        $port_coefficient_array = $patternModel->getPortCoefficient($pattern_id, $d['port'], $d['switch_model']);
        $port_coefficient = $port_coefficient_array['port_coefficient_simple_gig'];


        $mac_port_array = $indexModel->getAllMac($this->account_id, $pattern_id, $port_coefficient_array);
       // Debugger::PrintR($mac_port_array);



        if ($d['mac']) {
            if (array_key_exists($d['mac'], $mac_port_array)) {
                $port_db = $d['port'];// + $port_coefficient;

                $port_switch = $mac_port_array[$d['mac']];


                $p_s = $port_switch;// - $port_coefficient;

                if ($port_switch != $port_db) {
                    // echo $port_switch.PHP_EOL;
                    // echo $port_db;

                    Session::setFlash("В базе данных билинга указан не правильный порт! В базе данных порт
                    - " . $d['port'] . " По данным свича - $p_s Для получения данных исползуется порт $p_s", $this->style_class['warning']);

                    $d['port'] = $p_s;

                }

            } else {
                Session::setFlash("Мак адрес пользователя $this->account_id  указанный в базе даных билинга не обнаружен в данных свича", $this->style_class['warning']);
            }

        } else {
            Session::setFlash("Мак адрес в базе даных билинга для пользователя $this->account_id отсутствует", $this->style_class['warning']);
        }

        $pattern_data = $patternModel->PatternData($d['port'], $pattern_id, $d['switch_model']);
        //  Debugger::PrintR($d);

        //  Debugger::PrintR($pattern_data);


        $oid_port_status = Config::get('port_status') . "." . ($d['port'] + $port_coefficient);



        $data_status = $indexModel->snmpByKey($this->account_id, $oid_port_status);
        $data_status = array_flip($data_status);
        //  Debugger::PrintR($data_status);

        $cabletest_start = '';
        switch (Config::get('cabletest_on_off')) {
            case 'on':
                $indexModel->cableTest($this->account_id, $pattern_id, $d['port'], $d['manufacturer']);
                $cabletest_start = "yes";
                break;
            case 'off':
                $cabletest_start = "no";
                break;
            case 'onoff':
                if (isset($data_status[2])) {
                    $indexModel->cableTest($this->account_id, $pattern_id, $d['port'], $d['manufacturer']);
                    $cabletest_start = "yes";
                } else {
                    $cabletest_start = "no";
                }
                break;

        }

        $oids = array();
        foreach ($pattern_data as $k => $v) {
            if ($k != 'id' && $k != 'port_coefficient' && $k != 'mac_all' && $k != 'macs_ports' && $k != 'gig_port_coefficient') {
                $oids[$k] = $v;
            }
        }
        //  Debugger::PrintR($oids);
        // $manufacturer = strtolower($d['manufacturer']);

        if ($d['manufacturer'] == 'Eltex') {
            $oids['cable_status'] = $oids['cable_status'] . '.2';
            $oids['cable_lenght'] = $oids['cable_lenght'] . '.3';

        }
        // Debugger::PrintR($oids);

        if ($cabletest_start == 'no') {
            unset($oids['cable_test_start']);
            unset($oids['cable_status']);
            unset($oids['cable_lenght']);
        }

        //  die('ups');
       //   Debugger::PrintR($oids);

        $data = $indexModel->snmpData($this->account_id, $oids);

        $oids = array_flip($oids);
        $data_switch = array_combine($oids, $data['key']);


        $mac_arr = array();
      //   Debugger::PrintR($mac_port_array);

        foreach ($mac_port_array as $k => $v) {
            $val = $v;//- $port_coefficient;

            if ($val == $d['port']) {
                $mac_arr[] = $k;
            }
        }
        $data_switch['mac'] = $mac_arr;
        //Debugger::PrintR($data_switch['mac']);

        //   Debugger::PrintR($data_switch);


        if ($data_switch['port_status'] == 1) {
            $data_switch['port_status'] = 'ON';
        } else {
            $data_switch['port_status'] = 'OFF';
        }
        if (isset($data_switch['last_change'])) {

            $data_switch['last_change'] = date(' Y-m-d h:i:m', mktime(0, 0, -($data_switch['last_change'])));
        }

        if (isset($data_switch['cable_status'])) {
            echo $data_switch['cable_status'];

            $data_switch['cable_status'] = $cable_test[$d['manufacturer']][$data_switch['cable_status']];
            //  Debugger::PrintR($cable_test[$d['manufacturer']]);
            echo $d['manufacturer'];

        };
        if ($data_switch['duplex']) {
            $data_switch['duplex'] = $duplex[$data_switch['duplex']];
        }


        // Debugger::PrintR($data_switch);
        unset($data['key']);

        $data['port'] = $d['port'];
        //Debugger::PrintR($data);
        //  if (!$data) {
        //      throw new Exception(" SNMP data is not found", 404);

        // }

        $cable_length_status = $this->compareCableLength($data_switch['cable_lenght']);

        $request = new Request();

        if ($request->get('cable_length') == 'write') {

            $this->cableLengthWrite($data_switch['cable_lenght']);
        }

        $data_switch['speed'] = $data_switch['port_status'] == 'OFF' ? 0 : $data_switch['speed'];
        $historyModel->insertData($this->account_id, $data_switch, $data);

        $link_on_off = $request->get('link');//Параметр необходимый для отключения ссылок в шаблоне
        $switch_data_on_off = $request->get('switch_data'); // Параметр необходимый для отключения вывода данный о свиче (только snmp данные)

        $args = array(
            'data_switch' => $data_switch,
            'data_db' => $data,
            'account_id' => $this->account_id,
            'cabletest_start' => $cabletest_start,
            'cable_length_status' => $cable_length_status,
            'link_on_off' => isset($link_on_off) ? $request->get('link') : null,
            'switch_data_on_off' => isset($switch_data_on_off) ? $request->get('switch_data') : 1
        );


        // Debugger::PrintR($data_switch);
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

    public function errorUserAction()
    {
        $errorModel = new errorModel();
        $errorModel->cleanUserError();
        $request = new Request();
        $data_error = $errorModel->getErrorData($request->get('errors_date'));

        foreach ($data_error as $k => $v) {
            $data_error[$k]['date'] = date('Y-m-d', $v['date']);

        }
        $args = array(
            'data_error' => $data_error
        );
        return $this->render($args);

    }


    public static function rewrite_file($file_path, $mode, $data)
    {
       $f = fopen($file_path, $mode);
       fwrite($f, $data);
        fclose($f);
    }


    public static function errorAction(Exception $e)
    {
        $data = date('Y-m-d H:i:s') . PHP_EOL;
        $data .= '/./ ' . $e->getCode() . PHP_EOL;
        $data .= '/./ ' . $e->getMessage() . PHP_EOL;
        $data .= '/./ ' . $e->getFile() . PHP_EOL;
        $data .= '/./ ' . $e->getLine() . PHP_EOL;
        $data .= '///';


        self::rewrite_file(APPROOT_DIR . 'log.txt', 'a', $data);
    }

}