<?php


class AdminController extends Controller
{
    private function issetSwitch()
    {
        $request = new Request();
        $adminModel = new adminModel($request);
        return empty($adminModel->selectSwitch()) ? false : true;
    }

    private function issetSwitchId($switch_id)
    {
        $request = new Request();
        $adminModel = new adminModel($request);
        return empty($adminModel->selectSwitchByID($switch_id)) ? false : true;

    }


    public function insertSwitchAction()
    {
        $request = new Request();

        if ($request->isPost()) {

            $adminModel = new adminModel($request);
            if ($adminModel->isValid()) {

                if (!$this->issetSwitch()) {

                    $adminModel->insertSwitch();
                    Session::setFlash('Информация о новом свиче успешно добавленна!', 'information');
                    $this->redirect('/account_test/admin/switch_list');

                } else {
                    Session::setFlash('Свич с таким именем уже существует в базе данных', 'warning');
                }
            } else {
                Session::setFlash('Заполните все поля!', 'warning');
            }
        }

        $patternModel = new patternModel(null);
        $patterns_id_array = $patternModel->patternsId();

        $args = array(
            'manufacturer' => Config::get('switch_manufacturer'),
            'patterns_id' => $patterns_id_array
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

    public function editSwitchAction()
    {
        if ($this->issetSwitchId(Router::getSwitchId())) {

            $request = new Request();
            $adminModel = new adminModel($request);
            $switch_data = $adminModel->selectSwitchByID(Router::getSwitchId());

            if ($request->isPost()) {

                $adminModel = new adminModel($request);
                if ($adminModel->isValid()) {


                    $adminModel->editSwitch(Router::getSwitchId());
                    Session::setFlash('Информация о свиче изменена!', 'information');
                    //$this->redirect('/account_test/admin/switch_list');


                } else {
                    Session::setFlash('Заполните все поля!', 'warning');
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
            throw new Exception('Page no found.Switch with id = ' . Router::getSwitchId() . "  is absent in data base", 404);
        }

    }

    public function deleteSwitchAction()
    {
        $request = new Request();
        $adminModel = new adminModel($request);
        if ($this->issetSwitchId(Router::getSwitchId())) {
            $adminModel->deleteSwitch(Router::getSwitchId());
            $this->redirect('/account_test/admin/switch_list');
        } else {
            throw new Exception('Page no found.Switch with id = ' . Router::getSwitchId() . "  is absent in data base", 404);
        }


    }

}