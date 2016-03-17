<?php


abstract class Debugger {
    public static function PrintR($item)
    {
        switch(gettype($item)){
            case 'array':
                echo '<br/><b> Its Array</b><br/>';
                break;
            case 'object':
                echo '<br/><b> Its Object</b><br/>';
                break;
            default:
                echo '<br/><b> Its NOT array or object</b><br/>';
        }
        if(gettype($item)== 'array'|| gettype($item)== 'object')
        {
        echo '<pre>';
        print_r($item);
        echo '</pre>';
        }
    }

    public static function VarDamp($item)
    {
        echo '<pre>';
        var_dump($item);
        echo '</pre>';

    }

}