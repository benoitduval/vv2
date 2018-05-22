<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;

class GuestController extends AbstractController
{

    public function responseAction()
    {
        $eventId  = $this->params('eventId', null);
        $response = $this->params('response', null);

        if ($guest = $this->disponibilityTable->fetchOne(['userId' => $this->getUser()->id, 'eventId' => $eventId])) {
            $guest->response = $response;
            $this->disponibilityTable->save($guest);
        }

        $view = new ViewModel(array(
            'result'   => true
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}