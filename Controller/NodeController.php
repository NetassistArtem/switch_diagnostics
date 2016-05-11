<?php


class NodeController extends Controller
{

    private static $error_code = null;
    private static $error_message = null;

    public static function writeErrorData(Exception $e)
    {
        self::$error_code = $e->getCode();
        self::$error_message = $e->getMessage();
    }

    public function indexAction()
    {
        $nodeModel = new NodeModel();
        $node_data = $nodeModel->indexPage(Router::getId());


     //   if(self::$error_code && self::$error_message){
            $error_data = array(
                'error_code' => self::$error_code,
                'error_message' => self::$error_message
            );
     //   }else{
      //      $error_data = array(
       //         'error_code' => '',
        //        'error_message' => ''
        //    );
       // }


        $args = array(
            'node_data' => $node_data[0],
            'error_data' => $error_data
        );

        return $this->render($args);

    }

}