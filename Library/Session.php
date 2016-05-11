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

    public static function getFlash($account_id = null)
    {
        $message = self::get('flash') ? self::get('flash') :array();


        $errorModel = new errorModel($account_id);
        $errorModel->writeError($message);

        self::remove('flash');
        return $message;

    }

}