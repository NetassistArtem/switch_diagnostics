<?php


class NodeController extends Controller {

    public function indexAction()
    {
        $nodeModel = new NodeModel();
        $node_data = $nodeModel->indexPage(Router::getId());

        $args = array(
            'node_data' => $node_data[0]
        );

        return $this->render($args);

    }

}