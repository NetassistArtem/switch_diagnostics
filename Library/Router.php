<?php


abstract class Router
{

    private static $controller;
    private static $action;
    private static $account_id = null;
    private static $id;
    private static $switch_pattern_id = null;
    private static $billing = null;


    /**
     * @param $url
     * @throws Exception
     */
    public static function parse($url)
    {
        require LIB_DIR . 'routes.php';
        $request = new Request();


        $arr = explode('?', $url);
        $url = rtrim($arr[0], '/');

        $url_a = explode('/', $url);
        if ($url_a[1] == 'bl') {


            self::$billing = 1;
            //  Session::set('billing',1);

            // echo $request->get('billing');
            unset($url_a[1]);
            $switch_id = $request->get('switch');
            $port_id = $request->get('port');
            $user_id = $request->get('user_id');


            if ($switch_id && $port_id && !$user_id) {

                $indexModel = new IndexModel();

                $user_id = (int)$indexModel->userIdByPort($port_id, $switch_id);


                if ($user_id !== -1 && isset($user_id)) {


                    $url_a[] = $user_id;
                    $url = implode("/", $url_a);
                    $cabletest = $request->get('cabletest');
                    $link = $request->get('link');

                    $warning = $request->get('warning');
                    $notice = $request->get('notice');
                    $information = $request->get('information');
                    $cable_length = $request->get('cable_length');
                    $switch_data = $request->get('switch_data');
                    $user_switch = 'switch';//параметр для определения откуда пришел запрос со страници пользователя или страници свича (разная обработка мак адресов)

                    self::parse("{$url}?bl=1&user_switch={$user_switch}&cabletest=$cabletest&link=$link&warning=$warning&notice=$notice&information=$information&cable_length=$cable_length&switch_data=$switch_data");
                   // throw new Exception("{$url}?bl=1&cabletest=$cabletest&link=$link&warning=$warning&notice=$notice&information=$information&cable_length=$cable_length&switch_data=$switch_data",2);
                    //Controller::redirect($url . "?bl=1&cabletest=$cabletest&link=$link&warning=$warning&notice=$notice&information=$information&cable_length=$cable_length&switch_data=$switch_data");
                } else {
                    throw new Exception("В базе данных билинга не обнаружен пользователь с switch_id = $switch_id и port_id = $port_id ", 1);
                }


            }
            if($user_id){

                $url_a[] = $user_id;
                $url = implode("/", $url_a);
                $cabletest = $request->get('cabletest');
                $link = $request->get('link');

                $warning = $request->get('warning');
                $notice = $request->get('notice');
                $information = $request->get('information');
                $cable_length = $request->get('cable_length');
                $switch_data = $request->get('switch_data');
                $user_switch = 'user';//параметр для определения откуда пришел запрос со страници пользователя или страници свича (разная обработка мак адресов)

                self::parse("{$url}?bl=1&user_switch={$user_switch}&cabletest=$cabletest&link=$link&warning=$warning&notice=$notice&information=$information&cable_length=$cable_length&switch_data=$switch_data");
                // throw new Exception("{$url}?bl=1&cabletest=$cabletest&link=$link&warning=$warning&notice=$notice&information=$information&cable_length=$cable_length&switch_data=$switch_data",2);
                // Controller::redirect($url . "?bl=1&cabletest=$cabletest&link=$link&warning=$warning&notice=$notice&information=$information&cable_length=$cable_length&switch_data=$switch_data");

            }

        }

        $url = implode("/", $url_a);


        if (!$url) {
            self::$controller = 'Index';
            self::$action = 'index';
            return;
        }


        foreach ($routes as $route => $item) {

            $regex = $item['pattern'];

            if ($item['params']) {
                foreach ($item['params'] as $k => $v) {
                    $regex = str_replace('{' . $k . '}', '(' . $v . ')', $regex);
                }
            }

            if (preg_match('@^' . $regex . '$@', $url, $matches)) {

                self::$controller = $item['controller'];
                self::$action = $item['action'];
                self::$id = isset($item['params']['id']) ? $item['params']['id'] : '';

                if ($item['action'] == 'snmpData' || $item['action'] == 'history' || $item['action'] == 'insertCableLength') {
                    $url_array = explode('/', $url);

                    self::$account_id = array_pop($url_array);
                }
                if ($item['action'] == 'editSwitch' || $item['action'] == 'deleteSwitch' || $item['action'] == 'editPattern' || $item['action'] == 'deletePattern') {
                    $url_array = explode('/', $url);
                    self::$switch_pattern_id = array_pop($url_array);
                }
            }
        }

        if (is_null(self::$controller) || is_null(self::$action)) {

            throw new Exception('Page (' . $request->server('REQUEST_URI') . ') not found', 404);
        }
    }

    public static function get_content_by_url($url)
    {
        Session::remove('flash');//Удаляет сообщения записанные в массив пр обработке url поступившего из биллинга- решает проблему дубликатов сообщений
        self::parse($url);
        $request = new Request();
        $_controller = self::getController() . 'Controller';
        $_action = self::getAction() . 'Action';
        $_controller_object = new $_controller;

        if (!method_exists($_controller_object, $_action)) {
            throw new Exception("{$_action} not found", 404);
        }

        // echo $_controller.PHP_EOL;
        // echo $_action.PHP_EOL;


        $content = $_controller_object->$_action();

        return $content;
    }

    /**
     *
     *
     * @param $routeName
     * @param array $params
     */
    public static function getUrl($routeName, array $params = array())
    {

    }

    /**
     * @return mixed
     */
    public static function getController()
    {
        return self::$controller;
    }

    /**
     * @return mixed
     */
    public static function getAction()
    {
        return self::$action;
    }

    public static function getAccountId()
    {
        return self::$account_id;
    }

    public static function getId()
    {
        return self::$id;
    }

    /**
     * @return null
     */
    public static function getSwitchPatternId()
    {
        return self::$switch_pattern_id;
    }

    /**
     * @return null
     */
    public static function getBilling()
    {
        return self::$billing;
    }


}