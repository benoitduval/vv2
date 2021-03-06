<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model\Stats;
use Application\TableGateway;

class StatsController extends AbstractController
{

    public function saveAction()
    {
        $stats   	  = $this->params('stats', null);
        $eventId 	  = $this->params('eventId', null);
        $scoreFor 	  = $this->params('scoreFor', null);
        $scoreAgainst = $this->params('scoreAgainst', null);
        $setFor 	  = $this->params('setFor', null);
        $setAgainst   = $this->params('setAgainst', null);
        $result		  = [];

        // Point for us 
        if (in_array($stats, [Stats::SERVE_POINT])) {
        	
        }

        // if ($guest = $this->disponibilityTable->fetchOne(['userId' => $this->getUser()->id, 'eventId' => $eventId])) {
        //     $guest->response = $response;
        //     $this->disponibilityTable->save($guest);
        // }

        $view = new ViewModel(array(
            'result'   => true
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

    public function eventAction()
    {
        $eventId = $this->params('eventId', null);
        $userIds = json_decode($this->params()->fromQuery('userIds', null));
        $reason = json_decode($this->params()->fromQuery('type', null));
        $setter = $this->params()->fromQuery('setter', null);

        $settersId = [];
        if ($setter != "all" && $event = $this->eventTable->find($eventId)) {
            $event = $this->eventTable->find($eventId);
            $users = $this->userTable->getAllByGroupId($event->groupId);
            foreach ($users as $userId => $user) {
                if ($user->position == \Application\Model\User::POSITION_SETTER) $settersId[] = $userId;
            }
        }

        $params = [
            'eventId'  => $eventId,
            'userId'   => $userIds,
            'reason'   => $reason,
        ];

        if ($settersId) $params[$setter] = $settersId;

        $result = $this->statsTable->getZonePercent($params);

        $view = new ViewModel(['result' => $result]);
        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}