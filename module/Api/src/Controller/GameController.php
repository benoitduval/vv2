<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;

class GameController extends AbstractController
{

    public function addAction()
    {
        $data = [        
            'userId'  => $this->params()->fromPost('userId', null),
            'eventId' => $this->params()->fromPost('eventId', null),
            'numero'  => $this->params()->fromPost('numero', null),
            'quality' => $this->params()->fromPost('quality', null),
            'type'    => $this->params()->fromPost('type', null),
        ];

        $this->gameTable->save($data);
        
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