<?php


class IndexController extends Controller
{


    public function indexAction()
    {

        $nodeModel = new NodeModel();
        $node_data = $nodeModel->indexPage(4);

        $request =new Request();
        if($request->isPost()){
          //  добавить другие проверки - есть ли такой пользователь допустим
        return  $this->snmpDataAction($request->post('account_id'), 'snmpData');
        }
        $args = array(
            'node_data' => $node_data[0]
        );

        return $this->render($args);
    }


    public function snmpDataAction($account_id = null, $tpl = null)
    {

      //  $test = new helperModel();
      //  $test->insertMac(489, "99:f6:ac:6a:ac:99", "10.4.0.113");

        $indexModel = new IndexModel();
        $id = $account_id ? $account_id : Router::getAccountId();




        $data = $indexModel->snmpData($id, "iso.3.6.1.2.1.1.4.0");

        if (!$data) {
            throw new Exception(" SNMP data is not found", 404);

        }

        return $this->render($data, $tpl);

    }

    public static function rewrite_file($file_path, $mode, $date)
    {
        $f = fopen($file_path, $mode);
        fwrite($f, $date);
        fclose($f);
    }


    public static function errorAction(Exception $e)
    {
        $date = date('Y-m-d H:i:s') . PHP_EOL;
        $date .= '/./ ' . $e->getCode() . PHP_EOL;
        $date .= '/./ ' . $e->getMessage() . PHP_EOL;
        $date .= '/./ ' . $e->getFile() . PHP_EOL;
        $date .= '/./ ' . $e->getLine() . PHP_EOL;
        $date .= '///';


        self::rewrite_file(APPROOT_DIR . 'log.txt', 'a', $date);
    }

}