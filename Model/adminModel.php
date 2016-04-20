<?php


class adminModel

{
    public $switch_name;
    public $switch_manufacturer;
    public $switch_firmware;
    public $pattern_id;
    public $pattern_fields_value = array();
    public $pattern_field_check = array();

    public function __construct(Request $request,array $pattern_fields = null)
    {
        $this->switch_name = $request->post('model_name') ? $request->post('model_name') : null;
        $this->switch_manufacturer = $request->post('switch_manufacturer') ? $request->post('switch_manufacturer') : null;
        $this->switch_firmware = $request->post('firmware') ? $request->post('firmware') : null;
        $this->pattern_id = $request->post('pattern_id') ? $request->post('pattern_id') : null;
        if(isset($pattern_fields)){
            $pattern_fields_value = array();
            $pattern_fields_check_value = array();
            foreach($pattern_fields as $v){
                $pattern_fields_value[$v] = $request->post($v);
                $pattern_fields_check_value[$v] = $request->post("absent_$v");
            }
            unset($pattern_fields_value['id']);
            unset($pattern_fields_check_value['id']);
            $this->pattern_fields_value = $pattern_fields_value;
            $this->pattern_field_check = $pattern_fields_check_value;

        }



    }

    public function isValidSwitch()
    {
        if($this->switch_name && $this->switch_manufacturer && $this->switch_firmware && $this->pattern_id){
            return true;
        }
        return false;
    }
    public function isValidPattern()
    {
        if( $this->pattern_fields_value['port_coefficient']){

            return true;
        }
            return false;
    }
    public function isValidFieldPattern()
    {
        //Debugger::PrintR($this->pattern_field_check);
       // Debugger::PrintR($this->pattern_fields_value);
        foreach($this->pattern_field_check as $k => $v){
            if(!$this->pattern_field_check[$k] && !$this->pattern_fields_value[$k]){

                return false;
            }
        }
        return true;
    }
    public function checkInsertOidData()
    {
        foreach($this->pattern_fields_value as $k => $v){
            if($v && $k != 'port_coefficient'){
                if(!preg_match("/^\.[0-9\.]*\.$/",$v)){
                    return false;
                }
            }
        }
            return true;
    }
    public function checkInsertPortCoefficient()
    {
        if(preg_match("/^[0-9]*$/",$this->pattern_fields_value['port_coefficient'])){
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

    public function insertPattern()
    {
        $dbc = Connect_db::getConnection();
        $placeholders = array();
        Debugger::PrintR($this->pattern_fields_value);
        $fields_k = '';
        $fields_v = '';

        foreach($this->pattern_fields_value as $k=>$v){
            $fields_k .= ', `'.$k.'`';
            $fields_v .= ", '".$v."'";
        }
        $fields_key = trim($fields_k, ',');
        $fields_value = trim($fields_v, ',');
        echo $fields_value;
        $sql = "INSERT INTO `patterns`($fields_key) VALUES ($fields_value)";

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

    public function selectPatternByID($id_pattern)
    {
        $dbc = Connect_db::getConnection();
        $sql = "SELECT * FROM `pattern` WHERE `id`= :id";
        $placeholders = array(
            'id' => $id_pattern
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

    /**
     * @return array
     */
    public function getPatternFieldsValue()
    {
        return $this->pattern_fields_value;
    }




}