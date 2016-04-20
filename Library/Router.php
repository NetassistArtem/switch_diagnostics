<?php


abstract class Router
{

    private static $controller;
    private static $action;
    private static $account_id = null;
    private static $id;
    private static $switch_pattern_id = null;

    /**
     * @param $url
     * @throws Exception
     */
    public static function parse($url)
    {
        require LIB_DIR . 'routes.php';
        $arr = explode('?', $url);
        $url = rtrim($arr[0], '/');


        if (!$url) {
            self::$controller = 'Index';
            self::$action = 'index';
            return;
        }

        foreach ($routes as $route => $item) {

            $regex = $item['pattern'];

            foreach ($item['params'] as $k => $v) {
                $regex = str_replace('{' . $k . '}', '(' . $v . ')', $regex);
            }

            if (preg_match('@^' . $regex . '$@', $url, $matches)) {

                self::$controller = $item['controller'];
                self::$action = $item['action'];
                self::$id = isset($item['params']['id']) ? $item['params']['id'] : '';

                if ($item['action'] == 'snmpData' || $item['action'] == 'history' ||  $item['action'] == 'insertCableLength') {
                    $url_array = explode('/', $url);

                    self::$account_id = array_pop($url_array);
                }
                if($item['action'] == 'editSwitch'|| $item['action'] == 'deleteSwitch' || $item['action'] == 'editPattern'|| $item['action'] == 'deletePattern'){
                    $url_array = explode('/', $url);
                    self::$switch_pattern_id = array_pop($url_array);
                }
            }
        }

        if (is_null(self::$controller) || is_null(self::$action)) {
            $request = new Request();
            throw new Exception('Page (' . $request->server('REQUEST_URI') . ') not found', 404);
        }
    }

    public static function get_content_by_url($url)
    {

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


}