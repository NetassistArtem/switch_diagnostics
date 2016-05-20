<?php


class AdminController extends Controller
{
    public $style_class = array();

    public function __construct()
    {
        $this->style_class = Config::get('style_class');
    }

    private function issetSwitch()
    {
        $request = new Request();
        $adminModel = new adminModel($request);
       // return empty($adminModel->selectSwitch()) ? false : true;
        return empty($adminModel->selectSwitchByNameFirmware()) ? false : true;
    }

    private function issetSwitchId($switch_id)
    {
        $request = new Request();
        $adminModel = new adminModel($request);
        return empty($adminModel->selectSwitchByID($switch_id)) ? false : true;

    }

    private function issetPatternId($pattern_id)
    {
        $request = new Request();
        $adminModel = new adminModel($request);
        return empty($adminModel->selectPatternByID($pattern_id)) ? false : true;

    }


    public function insertSwitchAction()
    {
        $request = new Request();
        $adminModel = new adminModel($request);
        if ($request->isPost()) {


            if ($adminModel->isValidSwitch()) {

                if (!$this->issetSwitch()) {

                    $adminModel->insertSwitch();
                    Session::setFlash('Информация о новом свиче успешно добавленна!', $this->style_class['information']);
                    $this->redirect('/account_test/admin/switch_list');

                } else {
                    Session::setFlash('Свич с таким именем и такой версии прошивки уже существует в базе данных', $this->style_class['warning']);
                }
            } else {
                Session::setFlash('Заполните все поля!', $this->style_class['warning']);
            }
        }

        $patternModel = new patternModel(null);
        $patterns_id_array = $patternModel->patternsId();
        $switch_data= array(
            'model_name'=> $adminModel->switch_name,
            'firmware' => $adminModel ->switch_firmware,
            'simple_port' =>$adminModel->switch_simple_ports,
            'gig_port' => $adminModel->switch_gig_ports,
            'manufacturer' => $adminModel->switch_manufacturer,
            'pattern_id' => $adminModel->pattern_id
        );

        $args = array(
            'manufacturer' => Config::get('switch_manufacturer'),
            'patterns_id' => $patterns_id_array,
            'switch_data' => $switch_data
        );

        return $this->render_admin($args);
    }


    public function insertPatternAction()
    {
        $request = new Request();

        $patternModel = new patternModel(null);
        $patterns_fields = $patternModel->patternFieldsName();


        if ($request->isPost()) {



            $adminModel = new adminModel($request, $patterns_fields);
            $pattern_post_data = $adminModel->getPatternFieldsValue();
         //   if ($adminModel->isValidPattern()) { //валидация обязатльных полей, раскоментить если такие появятся
                if ($adminModel->isValidFieldPattern()) {
                    if ($adminModel->checkInsertOidData()) {
                      //  if ($adminModel->checkInsertPortCoefficient()) {
                            $adminModel->insertPattern();
                            Session::setFlash('Новый шаблон успешно добавленн!', $this->style_class['information']);
                            $this->redirect('/account_test/admin/pattern_list');

                   //     } else {
                    //        Session::setFlash('Поле port_coefficient и gig_port_coefficient должно содержать только цифры', $this->style_class['warning']);
                    //    }
                    } else {
                        Session::setFlash('Поля для ввода oid должны содержать только цифры и точки и начинатся и заканчиваться точкой', $this->style_class['warning']);
                    }
                } else {
                    Session::setFlash('Заполните поля ввода oid или поставте галочку подтверждающую их отсутствие', $this->style_class['warning']);
                }
          //  } else {
             //   Session::setFlash('Заполните обязательные поля', $this->style_class['warning']);

       //     }

        }


        $args = array(
            'patterns_fields' => $patterns_fields,
            'pattern_post_data' =>isset($pattern_post_data) ? $pattern_post_data : null
        );

        return $this->render_admin($args);
    }


    public function indexAction()
    {
        $args = array();
        return $this->render_admin($args);
    }

    public function switchListAction()
    {
        $patternModel = new patternModel(null);
        $switch_data = $patternModel->switchData();
        $args = array(
            'switch_data' => $switch_data
        );
        return $this->render_admin($args);
    }

    public function patternListAction()
    {
        $patternModel = new patternModel(null);
        $pattern_data = $patternModel->allPatternData();
        $switch_data = $patternModel->switchData();
        $args = array(
            'pattern_data' => $pattern_data,
            'switch_data' => $switch_data
        );
        return $this->render_admin($args);
    }

    public function editSwitchAction()
    {
        $switch_pattern_id = Router::getSwitchPatternId();
        if ($this->issetSwitchId($switch_pattern_id)) {

            $request = new Request();
            $adminModel = new adminModel($request);
            $switch_data = $adminModel->selectSwitchByID($switch_pattern_id);

            if ($request->isPost()) {

                $adminModel = new adminModel($request);
                if ($adminModel->isValidSwitch()) {


                    $adminModel->editSwitch($switch_pattern_id);
                    Session::setFlash('Информация о свиче изменена!', $this->style_class['information']);
                    $this->redirect('/account_test/admin/switch_list');


                } else {
                    Session::setFlash('Заполните все поля!', $this->style_class['warning']);
                }

            }
            $patternModel = new patternModel(null);
            $patterns_id_array = $patternModel->patternsId();


            $args = array(
                'manufacturer' => Config::get('switch_manufacturer'),
                'patterns_id' => $patterns_id_array,
                'switch_data' => $switch_data
            );
            return $this->render_admin($args);

        } else {
            throw new Exception('Page no found.Switch with id = ' . $switch_pattern_id . "  is absent in data base", 404);
        }

    }








    public function editPatternAction()
    {
        $switch_pattern_id = Router::getSwitchPatternId();
        if ($this->issetPatternId($switch_pattern_id)) {

            $request = new Request();

            $patternModel = new patternModel(null);
            $patterns_fields = $patternModel->patternFieldsName();

            $adminModel = new adminModel($request, $patterns_fields);
            $pattern_data = $adminModel->selectPatternByID($switch_pattern_id);



            if ($request->isPost()) {


                $adminModel = new adminModel($request, $patterns_fields);
                $pattern_post_data = $adminModel->getPatternFieldsValue();
              //  if ($adminModel->isValidPattern()) { //валидация обязатльных полей, раскоментить если такие появятся
                    if ($adminModel->isValidFieldPattern()) {
                        if ($adminModel->checkInsertOidData()) {
                           // if ($adminModel->checkInsertPortCoefficient()) {

                                $adminModel->editPattern($switch_pattern_id);
                                Session::setFlash("Шаблон № $switch_pattern_id успешно отредактирован!", $this->style_class['information']);
                                $this->redirect('/account_test/admin/pattern_list');

                        //    } else {
                        //        Session::setFlash('Поле port_coefficient  и gig_port_coefficient должно содержать только цифры', $this->style_class['warning']);
                        //    }
                        } else {
                            Session::setFlash('Поля для ввода oid должны содержать только цифры и точки и начинатся и заканчиваться точкой', $this->style_class['warning']);
                        }
                    } else {
                        Session::setFlash('Заполните поля ввода oid или поставте галочку подтверждающую их отсутствие', $this->style_class['warning']);
                    }
               // } else {
                //    Session::setFlash('Заполните обязательные поля', $this->style_class['warning']);

              //  }
/*
                $adminModel = new adminModel($request);
                if ($adminModel->isValidSwitch()) {


                    $adminModel->editSwitch(Router::getSwitchId());
                    Session::setFlash('Информация о свиче изменена!', 'information');
                    //$this->redirect('/account_test/admin/switch_list');


                } else {
                    Session::setFlash('Заполните все поля!', 'warning');
                }
 */
            }


            $args = array(
                'pattern_post_data' =>isset($pattern_post_data) ? $pattern_post_data : null,
              //  'manufacturer' => Config::get('switch_manufacturer'),
               // 'patterns_id' => $patterns_id_array,
                'patterns_fields' => $patterns_fields,
                'pattern_data' => $pattern_data
            );
            return $this->render_admin($args);



        } else {
            throw new Exception('Page not found.Pattern with id = ' . $switch_pattern_id . "  is absent in data base", 404);
        }

    }


    public function deleteSwitchAction()
    {
        $request = new Request();
        $switch_id = Router::getSwitchPatternId();
        $adminModel = new adminModel($request);
        if ($this->issetSwitchId($switch_id)) {
            $adminModel->deleteSwitch($switch_id);
            Session::setFlash('Информация о свиче с id = "' . $switch_id . '" успешно удалена.', $this->style_class['information']);
            $this->redirect('/account_test/admin/switch_list');
        } else {
            throw new Exception('Page not found.Switch with id = ' .$switch_id. "  is absent in data base", 404);
        }
    }

    public function deletePatternAction()
    {
        $request = new Request();
        $pattern_id = Router::getSwitchPatternId();
        $adminModel = new adminModel($request);
        if ($this->issetPatternId($pattern_id)) {
            $adminModel->deletePattern($pattern_id);
            Session::setFlash('Шаблон с id = "' . $pattern_id . '" успешно удален.', $this->style_class['information']);
            $this->redirect('/account_test/admin/pattern_list');
        } else {
            throw new Exception('Page not found. Pattern with id = ' .$pattern_id. "  is absent in data base", 404);
        }
    }


}