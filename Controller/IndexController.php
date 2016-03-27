<?php


class IndexController extends Controller
{
    public $account_id;

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
                    }

                }
            }

            if(!empty($switch_data_result)){
                if(!$switch_data_result['manufacturer']){
                    Session::setFlash('В полученных со свича данных отсутствует информация о производители свича.');
                }
                if(!$switch_data_result['soft_version']){
                    Session::setFlash('В полученных со свича данных отсутствует информация о версии прошивки свича.');
                    $patterns_array = array();
                    foreach($switch as $v){
                        if($switch_data_result['model_name'] == $v['model_name']){
                            $patterns_array[] = $v['pattern_id'];
                        }
                    }
                    $patterns_array = array_unique($patterns_array);
                    if($patterns_array[1]){
                        Session::setFlash('Сущствует несколько шаблонов для данного свича! Использован первый из имеющихся
                         шаблонов');
                    }
                    return $patterns_array[0];
                }
                if($switch_data_result['manufacturer'] || $switch_data_result['soft_version'] || $switch_data_result['model_name']){
                    foreach($switch as $v){
                        if($switch_data_result['manufacturer'] == $v['manufacturer'] && $switch_data_result['model_name'] == $v['model_name'] && $switch_data_result['soft_version'] == $v['firmware']){
                            return $v['pattern_id'];
                        }
                    }
                }


            } else{
                Session::setFlash('Данные со свича не содержат информацию о производители, наименовании и версии прошивки свича.
                 Для определения ID шаблона использованны данные о
            модели и прошивки из базы данных биллинга');
                $pattern_number = $patternModel->getPatternUserData();
                if ($pattern_number['pattern_id']) {
                    return $pattern_number['pattern_id'];
                } else {
                    Session::setFlash('ДДанные со свича не содержат информацию о производители, наименовании и версии
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

        //  $test = new helperModel();
        //  $test->insertMac(290, "99:f6:ac:4a:f6:ac", "10.4.0.113", 4, "S2326TP-EI", "Version 5.70");

        $indexModel = new IndexModel();
        $this->account_id = $account_id ? $account_id : Router::getAccountId();


        $d = $indexModel->snmpData($this->account_id, Config::get('oid_switch_model'));
      $pattern_id = $this->findPattern($d);
        $patternModel = new patternModel($this->account_id);

        $pattern_data = $patternModel->PatternData($d['port'], $pattern_id);

        $oids = array();
        foreach($pattern_data as $k => $v){
            if($k != 'id' && $k != 'port_coefficient'){
                $oids[$k] =  $v;
            }
        }


        $data = $indexModel->snmpData($this->account_id, $oids);


        $oids = array_flip($oids);
        $data_switch = array_combine($oids, $data['key']);

        if($data_switch['port_status'] == 1){
            $data_switch['port_status'] = 'ON';
        }else {
            $data_switch['port_status'] = 'OFF';
        }

        $data_switch['last_change'] = date(' Y-m-d h:i:m', mktime(0,0,-($data_switch['last_change'])));
       // Debugger::PrintR($data_switch);
        unset($data['key']);
    //    Debugger::PrintR($data);

        //  if (!$data) {
        //      throw new Exception(" SNMP data is not found", 404);

        // }

        $args = array(
            'data_switch' => $data_switch,
            'data_db' => $data,
            'account_id'=> $this->account_id
        );

        return $this->render($args, $tpl);

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