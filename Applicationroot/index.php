<?php

require_once '../Applicationroot/init.php';
Session::start();
try {

    $request = new Request();
    $content = Router::get_content_by_url($request->server('REQUEST_URI'));
} catch (SNMPException $e) {
    IndexController::errorAction($e);
    NodeController::writeErrorData($e);

    $content = Router::get_content_by_url('/error_SNMP');

} catch (PDOException $e) {
    IndexController::errorAction($e);

    $content = Router::get_content_by_url('/error_500');

} catch (Exception $e) {
    IndexController::errorAction($e);
    if ($e->getCode() == 403) {
        $content = Router::get_content_by_url('/error_403');
    } else {
        $content =  Router::get_content_by_url('/error_404');
    }
}
echo $content;

