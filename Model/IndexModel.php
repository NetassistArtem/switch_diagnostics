<?php


class IndexModel
{


    public function snmpData($account_id)
    {
        $data = array(
            'test_text'=> 'test snmpData',
            'account_id' => $account_id
        );

        return $data;
    }

    public function indexPage($id)
    {
        $dbc = Connect::getConnection();
        $sql = "SELECT * FROM  pages WHERE id= :id";
        $placeholders = array(
            'id' => $id
        );
        $data = $dbc->getDate($sql, $placeholders);
        return $data;
    }





}