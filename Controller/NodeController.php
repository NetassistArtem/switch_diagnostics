<?php

/**
 * \brief Class NodeController
 *
 * Class NodeController отвечает за загрузку страниц приложения. Включает метод для загрузки стандартных страниц и
 * страниц ошибок. Также writeErrorData записывает на страницах ошибок информацию полученную из выброшенных исключений.
 */
class NodeController extends Controller
{

    private static $error_code = null;
    private static $error_message = null;

    /**
     * \brief Обработка информации выброшенных исключений
     *
     * \param Exception $e объект выброшенного исключения
     *
     * Записывает в свойства класса код ошибки и сообщение полученное из объекта $e выброшенного исключения
     */

    public static function writeErrorData(Exception $e)
    {
        self::$error_code = $e->getCode();
        self::$error_message = $e->getMessage();
    }

    /**
     * \brief Action отвечате за загрузку обычных страниц(включая стандартных страниц ошибок)
     *
     * Action отвечате за загрузку обычных страниц(включая стандартных страниц ошибок). Данные о необходимой странице
     * передает объект класса NodeModel, id нужной страници получает роутер (Router) при парсинге url.
     * $args массив содержит контент страници полученный NodeModel моделью из базы данных а также данные о
     * выброшенном исключении. Передается аргументом в возвращаемую функцию рендеринга $this->render($args)
     * \return $this->render($args) Функция рендеринга страници с параметром $args
     */

    public function indexAction()
    {
        $nodeModel = new NodeModel();
        $node_data = $nodeModel->indexPage(Router::getId());


     //   if(self::$error_code && self::$error_message){
            $error_data = array(
                'error_code' => self::$error_code,
                'error_message' => self::$error_message
            );

        $args = array(
            'node_data' => $node_data[0],
            'error_data' => $error_data
        );

        return $this->render($args);

    }

}