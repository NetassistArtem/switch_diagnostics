<?php


class Session
{

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

    public static function setFlash($message)
    {
        $_SESSION['flash'] = $message;
    }

    public static function getFlash()
    {
        $message = self::get('flash');
        self::remove('flash');
        return (string)$message;

    }

}