<?php
/*!
  \file
\brief Основной файл запуска приложения

В данном файле подключается файл init.php который содержит автозагрузчик классов. Запускает сесию. Формирует и выводит
на печать переменную $content которая содержит сформированный шаблон для всей необходимой информации заприашиваемой по
url. URL передается в статический метод роутера  Router::get_content_by_url. Блок иполнения помещен в блок try для
вылавливания исключений. Если в процессе обработки url  выбрасывается иключение оно обрабатыается в блоках catch
 (Их несколько SNMPException, PDOException, Exception  ). В этом случае формирование переменной $content происходит по
адресам соответствующих ошибок ("/error_SNMP","/error_500", "/error_403" и т.д. )
 */


$GLOBALS['start_time'] = microtime(true); ///< Время запуска скрипта. Необходимо для определения времени исполнения скрипта.


require_once '../Applicationroot/init.php';// Подключение файла с автозагрузчиком классов
Session::start();
try {

    $request = new Request();// Объект принимает и обрабатывает все запросы _POST, _GET, _SERVER
    /// Переменная содержит основной контент сформированный на основе запроса $url
    $content = Router::get_content_by_url($url = $request->server('REQUEST_URI'));



} catch (SNMPException $e) {// Обработка исключений выброшенных модулем SNMP
   // IndexController::errorAction($e); //Записывает информацию об ошибке в файл
    NodeController::writeErrorData($e); // Записывает информацию об ошибке в свойства класса NodeController.
    // Записанные свойства используются для вывода информации на странице ошибки.

    $content = Router::get_content_by_url("/error_SNMP"); // $e->getMessage(); //


} catch (PDOException $e) {// Обработка исключений выброшенных модулем SNMP
   // IndexController::errorAction($e);


    $content = Router::get_content_by_url("/error_500");

} catch (Exception $e) {///\details Обработка исключений.  В зависимости от кода ошибки $e->getCode(), разные редиректы и запись ошибок.
    // Присвоение контента страниц ошибок переменной $content.
   // IndexController::errorAction($e);
    NodeController::writeErrorData($e);
    if ($e->getCode() == 403) {

        $content = Router::get_content_by_url("/error_403");
    } elseif ($e->getCode() == 1) {
        NodeController::writeErrorData($e);

        $content = Router::get_content_by_url("/error_mac_switch"); //$e->getMessage(); //

/**
 * альтернативный метод редиректа с помощью Exception. Выбрасывается исключение в сообщении которого передается необходимый
 * url. Полученный url подставляется в функцию роутера для парсинга. Проблема єтого метода - возможность возникновения
 * исключения в исключении что приводит к ошибке. На данный момент метод не используется.
 */
    } elseif ($e->getCode() == 2) {
        $new_url = $e->getMessage();
       // echo $e->getMessage();

        $content = Router::get_content_by_url($new_url);

    } else {

        $content = Router::get_content_by_url("/error_404");
    }
}

echo $content;


