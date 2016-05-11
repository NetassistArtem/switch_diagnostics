<?php


$GLOBALS['start_time'] = microtime(true);



require_once '../Applicationroot/init.php';
Session::start();
try {

    $request = new Request();
    $content = Router::get_content_by_url($url = $request->server('REQUEST_URI'));


} catch (SNMPException $e) {
    IndexController::errorAction($e);
    NodeController::writeErrorData($e);



    $content = Router::get_content_by_url("/error_SNMP"); // $e->getMessage(); //

} catch (PDOException $e) {
    IndexController::errorAction($e);


    $content = Router::get_content_by_url("/error_500");

} catch (Exception $e) {
    IndexController::errorAction($e);
    NodeController::writeErrorData($e);
    if ($e->getCode() == 403) {

        $content = Router::get_content_by_url("/error_403");
    }
    if ($e->getCode() == 1) {


        $content =  Router::get_content_by_url("/error_mac_switch"); //$e->getMessage(); //

    } else {
/*
        $message = $e->getMessage();
        $billing = '';
        if(strpos($message,'bl')){
            $billing = 'bl';
        };*/

        $content = Router::get_content_by_url("/error_404");
    }
}


echo $content;


