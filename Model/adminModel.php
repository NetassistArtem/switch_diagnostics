<?php


class adminModel

{
    public $switch_name;
    public $switch_manufacturer;
    public $switch_firmware;
    public $pattern_id;

    public function __construct(Request $request)
    {
        $this->switch_name = $request->post('model_name') ? $request->post('model_name') : null;
        $this->switch_manufacturer = $request->post('switch_manufacturer') ? $request->post('switch_manufacturer') : null;
        $this->switch_firmware = $request->post('firmware') ? $request->post('firmware') : null;
        $this->pattern_id = $request->post('pattern_id') ? $request->post('pattern_id') : null;
    }

    public function isValid()
    {
        if($this->switch_name && $this->switch_manufacturer && $this->switch_firmware && $this->pattern_id){
            return true;
        }
        return false;

    }

    public function insertSwitch()
    {
        $dbc = Connect_db::getConnection();
        $placeholders = array(
            'model_name' => $this->switch_name,
            'manufacturer' => $this->switch_manufacturer,
            'firmware' => $this->switch_firmware,
            'pattern_id' => $this->pattern_id
        );
        $sql = "INSERT INTO `switches`(`model_name`, `manufacturer`, `firmware`, `pattern_id`) VALUES (:model_name,:manufacturer,:firmware,:pattern_id)";

        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }
    public function selectSwitch()
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM `switches` WHERE `model_name`= :model_name";
        $placeholders = array(
            'model_name' => $this->switch_name
        );
        $data = $dbc->getDate($sql, $placeholders);

        return $data[0];
    }
    public function selectSwitchByID($id_switch)
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM `switches` WHERE `id`= :id";
        $placeholders = array(
            'id' => $id_switch
        );
        $data = $dbc->getDate($sql, $placeholders);

        return $data[0];
    }
    public function editSwitch($id_switch)
    {
        $dbc = Connect_db::getConnection();
        $sql = "UPDATE `switches` SET `model_name`=:model_name,`manufacturer`=:manufacturer,`firmware`=:firmware,`pattern_id`= :pattern_id WHERE `id`= :id";
        $placeholders = array(
            'id' => $id_switch,
            'model_name' => $this->switch_name,
            'manufacturer' => $this->switch_manufacturer,
            'firmware' => $this->switch_firmware,
            'pattern_id' => $this->pattern_id
        );
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);
    }

    public function deleteSwitch($id_switch)
    {
        $dbc = Connect_db::getConnection();
        $sql = "DELETE FROM `switches` WHERE `id`= :id";
        $placeholders = array(
            'id' => $id_switch,
        );
        $sth = $dbc->getPDO()->prepare($sql);
        $sth->execute($placeholders);

    }


}