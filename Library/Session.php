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
        self::$flash_messages[] = array(
            'message' => $message,
            'warning_class' => $warning_class
        );
        $_SESSION['flash'] = self::$flash_messages;
    }

    public static function getFlash($account_id = null)
    {
        $message = self::get('flash');

        $errorModel = new errorModel($account_id);
        $errorModel->writeError($message);

        self::remove('flash');
        return $message;

    }

}