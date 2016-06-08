<?php


class IndexController extends Controller
{
    public $account_id;
    public $style_class = array();
    public $cable_length;
    public $cable_length_write;
    public $switch_id;
    public $port_id;

    public function __construct()
    {
        $this->style_class = Config::get('style_class');


    }

    //  private $repetition;


    private function findPattern($data_switch)
    {


        $patternModel = new patternModel($this->account_id, $this->switch_id, $this->port_id);


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
            $switch_data_result_model_array = array();

            foreach ($switch as $v) {
                $switch_model[] = $v['model_name'];
            }
            $switch_model = array_unique($switch_model);

            // Debugger::PrintR($data_switch);
            //  Debugger::PrintR($switch_model);

            foreach ($switch_model as $val) {
//echo $data_switch['key'].' test '.$val. '</br>';
                if (stripos($data_switch['key'], $val) !== false) {
                    $switch_data_result_model_array[] = $val;//массив всех названий моделей попадающих под шаблон
                    // $switch_data_result['model_name'] = $val;
                }
            }

//поиск названия свича с максимальным количеством символов - чтоб исключить попадающие в шаблон модели с аналогичными названиями
            $switch_data_result_model_array_count = array();
            foreach ($switch_data_result_model_array as $value_m) {
                $switch_data_result_model_array_count[$value_m] = strlen($value_m);
            }

            $key_m = max($switch_data_result_model_array_count);
            $switch_data_result['model_name'] = array_flip($switch_data_result_model_array_count)[$key_m];


            foreach ($switch as $v) {
                if ($v['model_name'] == $switch_data_result['model_name']) {
                    $switch_soft_version[] = $v['firmware'];
                }
            }

            if (!empty($switch_soft_version)) {
                //  Debugger::PrintR($switch_soft_version);
                foreach ($switch_soft_version as $val) {
//echo $data_switch['key'].'  test '.$val.'</br>  ';
                    if (stripos($data_switch['key'], $val) !== false) {
                        $switch_data_result['soft_version'] = $val;

                    } elseif (strtoupper($data_switch['manufacturer']) == 'ELTEX') {


                        $indexModel = new IndexModel();


                        $soft_ver_array = $indexModel->snmpByKey($this->account_id, '1.3.6.1.4.1.89.2.4', $this->switch_id, $this->port_id);


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
                    Session::setFlash('В полученных со свича данных версия прошивки не совпадает с версией указанной в шаблоне свича.', $this->style_class['information']);
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


                    $pattern_man_mod_firm = 0;
                    $pattern_mod_firm = 0;
                    $pattern_mod = 0;
                    foreach ($switch as $v) {

                        if ($switch_data_result['manufacturer'] == $v['manufacturer'] && $switch_data_result['model_name'] == $v['model_name'] && $switch_data_result['soft_version'] == $v['firmware']) {
                            $pattern_man_mod_firm = $v['pattern_id'];
                        } elseif ($switch_data_result['model_name'] == $v['model_name'] && $switch_data_result['soft_version'] == $v['firmware']) {
                            //Session::setFlash('Данные со свича не содержат информации о производители свича');
                            $pattern_mod_firm = $v['pattern_id'];
                        } elseif ($switch_data_result['model_name'] == $v['model_name']) {
                            $pattern_mod = $v['pattern_id'];
                        }
                    }
                    if ($pattern_man_mod_firm) {
                        return $pattern_man_mod_firm;
                    } elseif ($pattern_mod_firm) {
                        return $pattern_mod_firm;
                    } elseif ($pattern_mod) {
                        Session::setFlash('Данные со свича не содержат информации о производители свича и версии прошивки', $this->style_class['information']);
                        return $pattern_mod;
                    } else {
                        return false;
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
                Session::setFlash("Данные со свича не были получены. Информация о модели свича в базе данных билинга для
                 запрашиваемого user $this->account_id  отсутствуют", $this->style_class['warning']);
            }

        }

        return null;
    }

    /*

    public function insertCableLengthAction()
    {

        //   $request = new Request();

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
    //  $this->redirect("/account_test/" . $this->account_id . "?cable_length=write");
    //throw new Exception("/account_test/{$this->account_id}?cable_length=write", 2);
    /*
    return Router::get_content_by_url("/account_test/{$this->account_id}?cabletest=on");
            //$this->snmpDataAction($this->account_id, 'snmpData');

        }
    */
    private function cableLengthWrite($cable_length, $port_on_off, $account_id, $sw_id, $port)
    {
        $user_id = $this->account_id ? $this->account_id : $account_id;
        $switch_id = $this->switch_id ? $this->switch_id : $sw_id;
        $port_id = $this->port_id ? $this->port_id : $port;

        //if ($cable_length) {
        if ($cable_length == 0) {
            $cable_length = 0.1;
            Session::setFlash('Длинна кабеля меньше метра', $this->style_class['information']);
        }

        $cableLengthModel = new cableLengthModel();

        if (empty($this->cable_length)) {

            $cableLengthModel->insertCableLength($user_id, $cable_length, $port_on_off, $switch_id, $port_id);

            Session::setFlash("Длинна кабеля(порт в статусе $port_on_off) для user " . $this->account_id . " успешно записанна.", $this->style_class['information']);
        } else {
            $cableLengthModel->updataCableLength($user_id, $cable_length, $port_on_off, $switch_id, $port_id);
            Session::setFlash("Длинна кабеля(порт в статусе $port_on_off) для user " . $this->account_id . " успешно перезаписана.", $this->style_class['information']);

        }

        //  } else {

        //      Session::setFlash("Длинна кабеля не известна и не может быть записана. Порт пользователя должен быть в статусе 'open cable'.", $this->style_class['notice']);
        //  }
    }

    private function compareCableLength($new_cable_length, $port_on_off)
    {
        $cableLengthModel = new cableLengthModel();
        /*
                if (empty($cableLengthModel->cableLength($this->account_id))) {
                    Session::setFlash("Для пользователя " . $this->account_id . " не записанна длинна кабеля. Для записи длинны кабеля, пользователь
                    должен отключить кабель после чего необходимо нажать 'Записать длинну кабеля'", $this->style_class['notice']);
                    return $this->style_class['notice'];
                } else {  */
        //Debugger::PrintR($this->cable_length);
        if ($this->cable_length) {
            $saved_cable_length = $this->cable_length[0]["cable_length_port_{$port_on_off}"];//$cableLengthModel->cableLength($this->account_id)[0]["cable_lenght_port_{$port_on_off}"];
            $max_cable_lenght = $saved_cable_length + Config::get('delta_cable_langth');
            $min_cable_lenght = $saved_cable_length - Config::get('delta_cable_langth');

            //  if ($new_cable_length) {
            if ($new_cable_length > $max_cable_lenght || $new_cable_length < $min_cable_lenght) {
                if (!$this->cable_length_write && $this->cable_length_write != 'write') {
                    Session::setFlash("Длинна кабеля не правильная! Записанная ранее длинна " . $saved_cable_length . "м. ,
                полученная со свича длинна " . strval($new_cable_length) . "м.", $this->style_class['warning']);

                    return $this->style_class['warning'];
                }
            }
            //  }
        }
        return '';//'information';

    }

    private function isCableLength($port_status)
    {
        $cable_length_write = null;
        $cableLengthModel = new cableLengthModel();
        $cable_length = $cableLengthModel->cableLength($this->account_id, $this->switch_id, $this->port_id);
        $this->cable_length = $cable_length;
//Debugger::PrintR($port_status);
        if ($port_status[1] && !$cable_length[0]['cable_length_port_on']) {
            $cable_length_write = 1;
        }
        if ($port_status[2] && !$cable_length[0]['cable_length_port_off']) {
            $cable_length_write = 1;

        }

        $data = array(
            'cable_length_port_on' => $cable_length[0]['cable_length_port_on'],
            'cable_length_port_off' => $cable_length[0]['cable_length_port_off'],
            'cable_length_write' => $cable_length_write
        );
        return $data;

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
                    // throw new Exception("/account_test/{$request->post('account_id')}?cabletest=on", 2);
                    //Router::parse("/account_test/{$request->post('account_id')}?cabletest=on");
                    break;
                case 'without_cabletest':
                    $this->redirect("/account_test/" . $request->post('account_id') . "?cabletest=off");
                    //throw new Exception("/account_test/{$request->post('account_id')}?cabletest=off", 2);
                    //Router::parse("/account_test/{$request->post('account_id')}?cabletest=off");
                    break;
                case 'standart_cabletest':
                    $this->redirect("/account_test/" . $request->post('account_id') . "?cabletest=onoff");
                    //throw new Exception("/account_test/{$request->post('account_id')}?cabletest=onoff", 2);
                    //Router::parse("/account_test/{$request->post('account_id')}?cabletest=onoff");
                    break;
                case "history":
                    $this->redirect("/account_test/history/" . $request->post('account_id'));
                    // throw new Exception("/account_test/history/{$request->post('account_id')}", 2);
                    //Router::parse("/account_test/history/{$request->post('account_id')}");
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
        $request = new Request();

        $historyModel = new historyModel();
        $historyModel->cleanHistory();

        $indexModel = new IndexModel();
        $this->account_id = $account_id ? $account_id : Router::getAccountId();


        $d = $indexModel->snmpData($this->account_id, Config::get('oid_switch_model'), $tp_link);

        $pattern_id = $this->findPattern($d);


        $patternModel = new patternModel($this->account_id);

        // $port_coefficient = $patternModel->getPortCoefficient($pattern_id, $d['port'], $d['switch_model'])['port_coefficient_simple_gig'];
        $port_coefficient_array = $indexModel->getPortCoefficient($this->account_id, $d['port'], $this->switch_id);//$patternModel->getPortCoefficient($pattern_id, $d['port'], $d['switch_model']);
        $port_coefficient = $port_coefficient_array['port_coefficient_simple_gig'];


        $mac_port_array = $indexModel->getAllMac($this->account_id, $pattern_id, $port_coefficient_array);
        // Debugger::PrintR($mac_port_array);


        if ($d['mac']) {
            if (array_key_exists($d['mac'], $mac_port_array)) {
                $port_db = $d['port'];// + $port_coefficient;

                $port_switch = $mac_port_array[$d['mac']];


                $p_s = $port_switch;// - $port_coefficient;

                if ($port_switch != $port_db) {
                    if (!Router::getSwitchPortId()) {
                        // echo $port_switch.PHP_EOL;
                        // echo $port_db;

                        Session::setFlash("В базе данных билинга указан не правильный порт! В базе данных порт
                    - " . $d['port'] . " По данным свича - $p_s Для получения данных исползуется порт $p_s", $this->style_class['warning']);

                        $d['port'] = $p_s;
                    } else {
                        $macs_in_port = array_keys($mac_port_array, $d['port']);
                        if (count($macs_in_port)) {
                            if (count($macs_in_port) == 1) {
                                $user_id_in_port = $indexModel->getUserByMac($macs_in_port[0]);
                                If ($user_id_in_port) {
                                    $d['user_id'] = $user_id_in_port;
                                    Session::setFlash("На запрашиваемом порту указанный в базе данный билинка мак адрес {$d['mac']} не обнаружен.
                                    По данному порту обнаружен мак адрес {$macs_in_port[0]}, user id - {$user_id_in_port}", $this->style_class['warning']);
                                } else {
                                    Session::setFlash("На запрашиваемом порту указанный в базе данный билинка мак адрес {$d['mac']} не обнаружен.
                                    По данному порту обнаружен мак адрес {$macs_in_port[0]}, пользователь с таким адресом в базе данных билинга отсутствует.", $this->style_class['warning']);
                                }
                            }
                        } else {
                            Session::setFlash("На запрашиваемом порту мак адресов не обнаружено. По базе данных билинга на запрашиваемом порту должен быть мак адрес {$d['mac']}", $this->style_class['warning']);
                        }

                    }

                }

            } elseif ($d['mac'] == 'Нет данных') { //это значение присваивается в indexModel когда в б.д. id  пользователя = -1, т.е. он не числится на порту

                Session::setFlash("На запрашиваемом прту (свич $this->switch_id, порт $this->port_id) по данным базы данных пользователь не числится", $this->style_class['warning']);
            } else {
                Session::setFlash("Мак адрес для свич $this->switch_id, порт $this->port_id  указанный в базе даных билинга не обнаружен в данных свича", $this->style_class['warning']);
            }

        } else {
            Session::setFlash("Мак адрес в базе даных билинга для user $this->account_id отсутствует", $this->style_class['warning']);
        }

        $pattern_data = $patternModel->PatternData($d['port'], $pattern_id);
        //  Debugger::PrintR($d);


        //  Debugger::PrintR($pattern_data);


        $oid_port_status = Config::get('port_status') . "." . ($d['port'] + $port_coefficient);

        $data_status = $indexModel->snmpByKey($this->account_id, $oid_port_status);

        // Debugger::PrintR($data_status);

        $data_status = array_flip($data_status);

        $first_write_cable_test = $this->isCableLength($data_status);// проверка есть ли запись длинны кабеля для пользователя при включенном и выключенном порте.
        $cabletest_start = '';

        $this->cable_length_write = $request->get('cable_length');
        $cable_test_on_off = $this->cable_length_write == 'write' ? "on" : Config::get('cabletest_on_off');

        switch ($cable_test_on_off) {

            case 'on':
                if ($d['write_community']) {
                    $ct_start = $indexModel->cableTest($this->account_id, $pattern_id, $d['port'], $d['manufacturer'], null, $this->switch_id);

                    if($ct_start){
                        $cabletest_start = "no";
                    }else{
                        $cabletest_start = "yes";
                    }
                } else {
                    $cabletest_start = "no";
                    Session::setFlash('В настройках запрашиваемого свича не прописанна комьюнити для записи. Проведение кабель теста не возможно.', $this->style_class['notice']);
                }
                break;
            case 'off':
                $cabletest_start = "no";
                break;
            case 'onoff':

                if (isset($data_status[2]) || !$first_write_cable_test['cable_length_port_on']) { //если порт выключен или длина кабеля для пользователя при включенном порте не записана
                    //  Debugger::PrintR($data_status);
                    if ($d['write_community']) {
                        $ct_start = $indexModel->cableTest($this->account_id, $pattern_id, $d['port'], $d['manufacturer'], $this->style_class, $this->switch_id);

                        if($ct_start){
                            $cabletest_start = "no";
                        }else{
                            $cabletest_start = "yes";
                        }

                    } else {
                        $cabletest_start = "no";
                        Session::setFlash('В настройках запрашиваемого свича не прописанна комьюнити для записи. Проведение кабель теста не возможно.', $this->style_class['notice']);
                    }
                } else {
                    $cabletest_start = "no";
                }
                break;

        }


        $oids = array();
        foreach ($pattern_data as $k => $v) {
            if ($k != 'id' /* && $k != 'port_coefficient'*/ && $k != 'mac_all' && $k != 'macs_ports'/* && $k != 'gig_port_coefficient' */) {
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
            $data_switch['port_status'] = 'on';
        } else {
            $data_switch['port_status'] = 'off';
        }
        if (isset($data_switch['last_change'])) {

            $data_switch['last_change'] = date(' Y-m-d h:i:m', mktime(0, 0, -($data_switch['last_change'])));
        }

        if (isset($data_switch['cable_status'])) {

            $data_switch['cable_status'] = $cable_test[$d['manufacturer']][$data_switch['cable_status']];
            //  Debugger::PrintR($cable_test[$d['manufacturer']]);

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
        $cable_length_status = '';
        if ($cabletest_start == "yes") {
            $cable_length_status = $this->compareCableLength($data_switch['cable_lenght'], $data_switch['port_status']);
        }


        //Debugger::PrintR($first_write_cable_test);

        if (($request->get('cable_length') == 'write' && $d['write_community'] && $data_switch['cable_test_start']) || ($first_write_cable_test['cable_length_write'] && $d['write_community'] && $data_switch['cable_test_start'])) {
            if ($data_switch['port_status'] == 'on') {

                $this->cableLengthWrite($data_switch['cable_lenght'], "on", $d['user_id'], $d['switch_id'], $d['port']);
            } elseif ($data_switch['port_status'] == 'off') {

                $this->cableLengthWrite($data_switch['cable_lenght'], "off", $d['user_id'], $d['switch_id'], $d['port']);
            }
        }

        if ($first_write_cable_test['cable_length_write']) { // если запись длинны кабеля произодилас, одновить информацию о состоянии длинн кабеля после записи
            $first_write_cable_test = $this->isCableLength($data_status);
        }

        if (!$first_write_cable_test['cable_length_port_on']) {
            Session::setFlash('Длинна кабеля для включенного порта не записана', $this->style_class['notice']);
        }
        if (!$first_write_cable_test['cable_length_port_off']) {
            Session::setFlash('Длинна кабеля для выключенного порта не записана', $this->style_class['notice']);
        }

        $data_switch['speed'] = $data_switch['port_status'] == 'off' ? 0 : $data_switch['speed'];
        $historyModel->insertData($this->account_id, $data_switch, $data, $this->switch_id);

        $link_on_off = $request->get('link');//Параметр необходимый для отключения ссылок в шаблоне
        $switch_data_on_off = $request->get('switch_data'); // Параметр необходимый для отключения вывода данный о свиче (только snmp данные)
        $billing_request = $request->get('bl');// Параметр для определения откуда пришел запрос из биллинга или напрямую из приложения

        $c_l_port_on = $this->cable_length[0]['cable_length_port_on'] ? $this->cable_length[0]['cable_length_port_on'] : 'Нет данных';
        $c_l_port_off = $this->cable_length[0]['cable_length_port_off'] ? $this->cable_length[0]['cable_length_port_off'] : ' Нет данных';


        $args = array(
            'data_switch' => $data_switch,
            'data_db' => $data,
            'cable_length_port_on' => $data_switch['port_status'] == 'on' ? $data_switch['cable_lenght'] : $c_l_port_on,
            'cable_length_port_off' => $data_switch['port_status'] == 'off' ? $data_switch['cable_lenght'] : $c_l_port_off,
            'account_id' => $this->account_id,
            'cabletest_start' => $cabletest_start,
            'cable_length_status_port_on' => $data_switch['port_status'] == 'on' ? $cable_length_status : '',
            'cable_length_status_port_off' => $data_switch['port_status'] == 'off' ? $cable_length_status : '',
            'link_on_off' => isset($link_on_off) ? $request->get('link') : null,
            'switch_data_on_off' => isset($switch_data_on_off) ? $request->get('switch_data') : 1,
            'billing_request' => isset($billing_request) ? $billing_request : null,
            'switch_port_id' => Router::getSwitchPortId() ? Router::getSwitchPortId() : ''
        );


        // Debugger::PrintR($data_switch);
        // Debugger::PrintR($data_switch);
        //  if($this->repetition != 1){
        //      $this->repetition = 1;
        //      $this->snmpDataAction();
        //  }

        return $this->render($args, $tpl);

    }

    public function historyBySwitchAction()
    {
        return $this->historyAction(null, 'history', Router::getSwitchId(), Router::getPortId());
    }

    public function historyAction($account_id = NULL, $tpl = null, $switch_id, $port_id)
    {


        $this->account_id = $account_id ? $account_id : Router::getAccountId();
        $this->switch_id = $switch_id ? $switch_id : Router::getSwitchId();
        $this->port_id = $port_id ? $port_id : Router::getPortId();
        $historyModel = new historyModel();

        $historyModel->cleanHistory();

        $data_history = $historyModel->selectData($this->account_id, $this->switch_id, $this->port_id);

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


            return $this->historyAction($request->post('account_id'), 'history', $request->post('switch_id'), $request->post('port_id'));

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

    public static function write_error($message)
    {
        error_log($message, 3, APPROOT_DIR . 'log.txt');


    }


    public static function errorAction(Exception $e)
    {
        $data = date('Y-m-d H:i:s') . PHP_EOL;
        $data .= '/./ ' . $e->getCode() . PHP_EOL;
        $data .= '/./ ' . $e->getMessage() . PHP_EOL;
        $data .= '/./ ' . $e->getFile() . PHP_EOL;
        $data .= '/./ ' . $e->getLine() . PHP_EOL;
        $data .= '///';

        self::write_error($data);
        // self::rewrite_file(APPROOT_DIR . 'log.txt', 'a', $data);
    }

    /**
     * @return array|null
     */
    public function getStyleClass()
    {
        return $this->style_class;
    }


    public function snmpSwitchDataAction($switch_id = null, $port_id = null)
    {
        require LIB_DIR . 'responseValue.php';
        $request = new Request();

        $historyModel = new historyModel();
        $historyModel->cleanHistory();

        $indexModel = new IndexModel();

        $this->switch_id = $switch_id ? $switch_id : Router::getSwitchId();
        $this->port_id = $port_id ? $port_id : Router::getPortId();


        $d = $indexModel->snmpData(null, Config::get('oid_switch_model'), $tp_link, $this->switch_id, $this->port_id);

        $pattern_id = $this->findPattern($d);



        $patternModel = new patternModel($this->account_id, $this->switch_id, $this->port_id);


        // $port_coefficient = $patternModel->getPortCoefficient($pattern_id, $d['port'], $d['switch_model'])['port_coefficient_simple_gig'];
        $port_coefficient_array = $indexModel->getPortCoefficient($this->account_id, $d['port'], $this->switch_id);//$patternModel->getPortCoefficient($pattern_id, $d['port'], $d['switch_model']);


        $port_coefficient = $port_coefficient_array['port_coefficient_simple_gig'];


        $mac_port_array = $indexModel->getAllMac($this->account_id, $pattern_id, $port_coefficient_array, $this->switch_id, $this->port_id);
        // Debugger::PrintR($mac_port_array);


        if ($d['mac']) {
            if (array_key_exists($d['mac'], $mac_port_array)) {
                $port_db = $d['port'];// + $port_coefficient;

                $port_switch = $mac_port_array[$d['mac']];


                $p_s = $port_switch;// - $port_coefficient;

                if ($port_switch != $port_db) {
                    if (!Router::getSwitchPortId()) {
                        // echo $port_switch.PHP_EOL;
                        // echo $port_db;

                        Session::setFlash("В базе данных билинга указан не правильный порт! В базе данных порт
                    - " . $d['port'] . " По данным свича - $p_s Для получения данных исползуется порт $p_s", $this->style_class['warning']);

                        $d['port'] = $p_s;
                    } else {
                        $macs_in_port = array_keys($mac_port_array, $d['port']);
                        if (count($macs_in_port)) {
                            if (count($macs_in_port) == 1) {
                                $user_id_in_port = $indexModel->getUserByMac($macs_in_port[0]);
                                If ($user_id_in_port) {
                                    $d['user_id'] = $user_id_in_port;

                                    Session::setFlash("На запрашиваемом порту указанный в базе данный билинка мак адрес {$d['mac']} не обнаружен.
                                    По данному порту обнаружен мак адрес {$macs_in_port[0]}, user id - {$user_id_in_port}", $this->style_class['warning']);
                                } else {
                                    Session::setFlash("На запрашиваемом порту указанный в базе данный билинка мак адрес {$d['mac']} не обнаружен.
                                    По данному порту обнаружен мак адрес {$macs_in_port[0]}, пользователь с таким адресом в базе данных билинга отсутствует.", $this->style_class['warning']);
                                }
                            }
                        } else {
                            Session::setFlash("На запрашиваемом порту мак адресов не обнаружено. По базе данных билинга на запрашиваемом порту должен быть мак адрес {$d['mac']}", $this->style_class['warning']);
                        }

                    }
                }

            } elseif ($d['mac'] == 'Нет данных') {

                Session::setFlash("На запрашиваемом прту (свич $this->switch_id, порт $this->port_id) по данным базы данных пользователь не числится", $this->style_class['warning']);
            } else {
                Session::setFlash("Мак адрес для свич $this->switch_id, порт $this->port_id  указанный в базе даных билинга не обнаружен в данных свича", $this->style_class['warning']);
            }

        } else {
            Session::setFlash("Мак адрес в базе даных билинга для свич $this->switch_id, порт $this->port_id отсутствует", $this->style_class['warning']);
        }

        $pattern_data = $patternModel->PatternData($d['port'], $pattern_id);
        //  Debugger::PrintR($d);

        $oid_port_status = Config::get('port_status') . "." . ($d['port'] + $port_coefficient);

        $data_status = $indexModel->snmpByKey($this->account_id, $oid_port_status, $this->switch_id, $this->port_id);


        $data_status = array_flip($data_status);

        $first_write_cable_test = $this->isCableLength($data_status);// проверка есть ли запись длинны кабеля для пользователя при включенном и выключенном порте.
        $cabletest_start = '';

        $this->cable_length_write = $request->get('cable_length');
        $cable_test_on_off = $this->cable_length_write == 'write' ? "on" : Config::get('cabletest_on_off');

        switch ($cable_test_on_off) {

            case 'on':
                if ($d['write_community']) {
                    $ct_start =$indexModel->cableTest($this->account_id, $pattern_id, $d['port'], $d['manufacturer'], null, $this->switch_id);
                    if($ct_start){
                        $cabletest_start = "no";
                    }else{
                        $cabletest_start = "yes";
                    }

                } else {
                    $cabletest_start = "no";
                    Session::setFlash('В настройках запрашиваемого свича не прописанна комьюнити для записи. Проведение кабель теста не возможно.', $this->style_class['notice']);
                }
                break;
            case 'off':
                $cabletest_start = "no";
                break;
            case 'onoff':

                if (isset($data_status[2]) || !$first_write_cable_test['cable_length_port_on']) { //если порт выключен или длина кабеля для пользователя при включенном порте не записана
                    //  Debugger::PrintR($data_status);
                    if ($d['write_community']) {
                        $ct_start =$indexModel->cableTest($this->account_id, $pattern_id, $d['port'], $d['manufacturer'], $this->style_class, $this->switch_id);
                        if($ct_start){
                            $cabletest_start = "no";
                        }else{
                            $cabletest_start = "yes";
                        }

                    } else {
                        $cabletest_start = "no";
                        Session::setFlash('В настройках запрашиваемого свича не прописанна комьюнити для записи. Проведение кабель теста не возможно.', $this->style_class['notice']);
                    }
                } else {
                    $cabletest_start = "no";
                }
                break;

        }

        $oids = array();
        foreach ($pattern_data as $k => $v) {
            if ($k != 'id' /* && $k != 'port_coefficient'*/ && $k != 'mac_all' && $k != 'macs_ports'/* && $k != 'gig_port_coefficient' */) {
                $oids[$k] = $v;
            }
        }
        //Debugger::PrintR($oids);
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

        $data = $indexModel->snmpData($this->account_id, $oids, null, $this->switch_id, $this->port_id);

        $oids = array_flip($oids);
        $data_switch = array_combine($oids, $data['key']);

        // Debugger::PrintR($data);
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


        if ($data_switch['port_status'] == 1) {
            $data_switch['port_status'] = 'on';
        } else {
            $data_switch['port_status'] = 'off';
        }
        if (isset($data_switch['last_change'])) {

            $data_switch['last_change'] = date(' Y-m-d h:i:m', mktime(0, 0, -($data_switch['last_change'])));
        }

        if (isset($data_switch['cable_status'])) {

            $data_switch['cable_status'] = $cable_test[$d['manufacturer']][$data_switch['cable_status']];
            //  Debugger::PrintR($cable_test[$d['manufacturer']]);

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
        $cable_length_status = '';

        if ($cabletest_start == "yes") {
            $cable_length_status = $this->compareCableLength($data_switch['cable_lenght'], $data_switch['port_status']);
        }


        //Debugger::PrintR($first_write_cable_test);\
      //  Debugger::PrintR($data_switch);

        if (($request->get('cable_length') == 'write' && $d['write_community'] && $data_switch['cable_test_start']) || ($first_write_cable_test['cable_length_write'] && $d['write_community'] && $data_switch['cable_test_start'])) {

            if ($data_switch['port_status'] == 'on') {

                $this->cableLengthWrite($data_switch['cable_lenght'], "on", $d['user_id'], $d['switch_id'], $d['port']);
            } elseif ($data_switch['port_status'] == 'off') {

                $this->cableLengthWrite($data_switch['cable_lenght'], "off", $d['user_id'], $d['switch_id'], $d['port']);
            }
        }

        if ($first_write_cable_test['cable_length_write']) { // если запись длинны кабеля произодилас, одновить информацию о состоянии длинн кабеля после записи
            $first_write_cable_test = $this->isCableLength($data_status);
        }

        if (!$first_write_cable_test['cable_length_port_on']) {
            Session::setFlash('Длинна кабеля для включенного порта не записана', $this->style_class['notice']);
        }
        if (!$first_write_cable_test['cable_length_port_off']) {
            Session::setFlash('Длинна кабеля для выключенного порта не записана', $this->style_class['notice']);
        }

        //  die('ups');
        $data_switch['speed'] = $data_switch['port_status'] == 'off' ? 0 : $data_switch['speed'];


        $historyModel->insertData($this->account_id, $data_switch, $data, $this->switch_id);

        $link_on_off = $request->get('link');//Параметр необходимый для отключения ссылок в шаблоне
        $switch_data_on_off = $request->get('switch_data'); // Параметр необходимый для отключения вывода данный о свиче (только snmp данные)
        $billing_request = $request->get('bl');// Параметр для определения откуда пришел запрос из биллинга или напрямую из приложения

        $c_l_port_on = $this->cable_length[0]['cable_length_port_on'] ? $this->cable_length[0]['cable_length_port_on'] : 'Нет данных';
        $c_l_port_off = $this->cable_length[0]['cable_length_port_off'] ? $this->cable_length[0]['cable_length_port_off'] : ' Нет данных';

        $args = array(
            'data_switch' => $data_switch,
            'data_db' => $data,
            'cable_length_port_on' => $data_switch['port_status'] == 'on' ? $data_switch['cable_lenght'] : $c_l_port_on,
            'cable_length_port_off' => $data_switch['port_status'] == 'off' ? $data_switch['cable_lenght'] : $c_l_port_off,
            'account_id' => $this->account_id,
            'switch_id' => $this->switch_id,
            'cabletest_start' => $cabletest_start,
            'cable_length_status_port_on' => $data_switch['port_status'] == 'on' ? $cable_length_status : '',
            'cable_length_status_port_off' => $data_switch['port_status'] == 'off' ? $cable_length_status : '',
            'link_on_off' => isset($link_on_off) ? $request->get('link') : null,
            'switch_data_on_off' => isset($switch_data_on_off) ? $request->get('switch_data') : 1,
            'billing_request' => isset($billing_request) ? $billing_request : null,
            'switch_port_id' => Router::getSwitchPortId() ? Router::getSwitchPortId() : ''
        );


        //Debugger::PrintR($args);


        // Debugger::PrintR($data_switch);
        // Debugger::PrintR($data_switch);
        //  if($this->repetition != 1){
        //      $this->repetition = 1;
        //      $this->snmpDataAction();
        //  }

        return $this->render($args);

    }


}