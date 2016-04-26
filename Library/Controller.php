<?php

/**
 * Class Controller
 *
 * \brief Тестовый коментарий
 *
 * еще один тестовый коментарий
 */

abstract class Controller
{

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

    protected function render(array $args = array(), $tpl = null)
    {
        extract($args);

        ob_start();
        require $this->file_path($tpl); //$templateFile;
        $content = ob_get_clean();




       // ob_start();
        require VIEW_DIR . 'layout.phtml';
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