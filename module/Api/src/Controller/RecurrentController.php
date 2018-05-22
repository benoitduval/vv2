<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;

class RecurrentController extends AbstractController
{

    public function enableAction()
    {
        $id     = $this->params('id', null);
        $status = $this->params('status', null);

        $training = $this->trainingTable->find($id);
        $training->status = $status;
        $this->trainingTable->save($training);

        $view = new ViewModel(array(
            'result'   => [
                'success'  => true
            ]
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}