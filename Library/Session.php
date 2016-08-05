<?php


class Session
{
    public static $flash_messages = array();

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function set($key, $val)
    {
        if ($key !== 'flash') {
            return $_SESSION[$key] = $val;
        }
        return null;
    }
    public static function hasUser($user_name)
    {
        if(self::get('user')['user'] == $user_name){
            return$_SESSION['user'];
        }
        return null;
    }

    public static function get($key)
    {
        if (self::has($key)) {
            return $_SESSION[$key];
        }
        return null;
    }

    public static function remove($key)
    {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public static function start()
    {
        session_start();
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function setFlash($message, $warning_class = null)
    {
        $request = new Request();

        if($warning_class == $request->get('warning')){
            $warning_level = 'Warning';
        }elseif($warning_class == $request->get('notice')){
            $warning_level = 'Notice';
        }elseif($warning_class == $request->get('information')){
            $warning_level = 'Information';
        }else{
            $warning_level = $warning_class;
        }
        self::$flash_messages[] = array(
            'message' => $message,
            'warning_class' => $warning_class,
            'warning_level' => $warning_level
        );
        $_SESSION['flash'] = self::$flash_messages;
    }

    public static function getFlash($account_id = null, $switch_id = null, $port_id = null, $switch_port_id = null)
    {
        $message_all = self::get('flash') ? self::get('flash') :array();
//удаление дублирующихся сообщений, если такие появятся

        $m = array();
        foreach($message_all as $k=> $v){

            $m[$k] = $v['message'];
        }
        $m = array_unique($m);
        $message = array();
        foreach($message_all as $k=>$v){
            if($m[$k]){
                $message[$k] = $v;
            }
        }
//завершение удаления дубликатов сообщений
        $errorModel = new errorModel($account_id, $switch_id, $port_id, $switch_port_id);
        $errorModel->writeError($message);

        //удалени сообщений не важных для сопорта Сообщения с пометной "Switch_SNMP_data_problem". Но в историю они записываются

        foreach($message as $k=> $v){
            if(strpos($v['message'],'Switch_SNMP_data_problem') !== false){
                unset($message[$k]);

            }
        }

        self::remove('flash');

        return $message;

    }

}