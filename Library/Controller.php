<?php

/**
 * \brief Class Controller
 *
 * Отвечает за рендеринг, редиректы
 */

abstract class Controller
{

/*
    public static function test()
{
    $t = "http://test.naic.29632.as/bl/account_test/6048?bl=1";
    return $t;
}
*/
    /**
     * \brief Определяет путь к файлам шаблонов
     * \param $tpl Имя файла шаблона По умолчанию равен null. Используется в случае когда имя файла шаблона отличается от
     * имени Action.
     *
     * На основании данных полученных при парсинге URL объектом класса Router (названии контроллера и действия),а также
     * имени файла шаблона (если имя отличается от имени действия), выстраивается путь к файлу шаблона. Если по сформированному
     * пути файл отсутствует выбрасывается исключение с кодом 404
     *
     * \return $templateFile  возвращает путь к файлу шаблона
     */
    private function file_path($tpl = null)
    {

        $tplDir = Router::getController();
        $tplName = isset($tpl) ? $tpl : Router::getAction();

        $templateFile = VIEW_DIR . $tplDir . DS . $tplName . '.phtml';
        if (!file_exists($templateFile)) {

            throw new Exception("{$templateFile} not found", 404);
        }

        return $templateFile;
    }

    /**
     * \brief Рендеринг обычных страниц
     * \param $arg Массив переменных передаваемые в шаблон  для отображения
     * \param $tpl имя файла шаблона по умолчанию = null если оно отличается от имени дейстия (Action)
     *
     * Переменные из массива $args используются в файлах шаблонов для вывода необходимого контента.В буфер обмена записывается
     * сначала результат рендеринга необходимого шаблона, результат передается переменной $content. После этого из буфера обмена
     * выгружатеся вся страница в виде основного шаблона layout.phtml или layout_billing.phtml в котором встроенна переменная
     * $content, содержащая основной контент. Используются различные лайауты layout.phtml или layout_billing.phtml в
     * зависимости от запроса страници - если запрос произошел из биллинга (еслиь get параметр 'bl') -layout_billing.phtml,
     * если запрос из самого приложения обычный лайаут.
     *
     * \return ob_get_clean() Отрендеренную страницу из буфера обмена
     */

    protected function render(array $args = array(), $tpl = null)
    {
        extract($args);

        ob_start();
        require $this->file_path($tpl); //$templateFile;
        $content = ob_get_clean();


      //  ob_start();
        $request = new Request();
        if($request->get('bl')){//Router::getBilling()){
            require VIEW_DIR . 'layout_billing.phtml';
        }else{
            require VIEW_DIR . 'layout.phtml';
        }

        return ob_get_clean();
    }

    protected function render_admin(array $args = array(), $tpl = null)
    {
        extract($args);

        ob_start();
        require $this->file_path($tpl); //$templateFile;
        $content = ob_get_clean();




        // ob_start();
        require VIEW_DIR . 'layout_admin.phtml';
        return ob_get_clean();
    }


    public static function redirect($url)
    {
        header("Location: $url");
        die;
        // exit;
    }



}