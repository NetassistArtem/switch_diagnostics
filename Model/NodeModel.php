<?php


class NodeModel
{

    public function indexPage($id)
    {
        $dbc = Connect::getConnection();
        $sql = "SELECT * FROM  pages WHERE id= :id";
        $placeholders = array(
            'id' => $id
        );
        $data = $dbc->getDate($sql, $placeholders);

        if (!$data) {
            throw new Exception(" Page is not exist", 404);
        }
        return $data;
    }

}