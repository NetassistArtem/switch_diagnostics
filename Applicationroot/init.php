<?php
/*!
  \file
\brief Файл содержит автозагрузчик классов и подключает конфиги

Файл подключает конфигурационный файл, а также содержит функцию автозагрузки файлов классов.
 */

require_once '../Config/conf.php';

/*!
Принимает имена классов, определяет путь файлов содержащих эти класы и произодитиз загрузку. Необходимым условием является
совпадение названия классов и файлов в которых они определены. Если путь файла не найдет выбрасывает исключение с кодом
ошибки 404.

\param $className Имя класса. Любое имя класса встречающееся по ходу выполнения скрипта.

\return загружает файл нужного класса или выбрасывает исключение
*/

function __autoload($className)
{
    $file = "{$className}.php";
    if(file_exists(CONF_DIR . $file)){
        require_once CONF_DIR . $file;
    } elseif (file_exists(CONTROLLER_DIR . $file)){
        require_once CONTROLLER_DIR . $file;
    } elseif (file_exists(LIB_DIR . $file)){
        require_once LIB_DIR .$file;
    } elseif (file_exists(MODEL_DIR . $file)){
        require_once MODEL_DIR .$file;
    } elseif (file_exists(VIEW_DIR . $file)){
        require_once VIEW_DIR . $file;
    } elseif (file_exists(APPROOT_DIR . $file)){
        require_once APPROOT_DIR . $file;
    } else {
        throw new Exception ("{$file} not found", 404);
    }
}
