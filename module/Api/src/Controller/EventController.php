<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;
use Application\Model\Stats as Statistics;

class EventController extends AbstractController
{
    public function getAction()
    {
        if ($this->getUser()) {
            $start = $this->params()->fromQuery('start', null);
            $start .= ' 00:00:00';
            $end = $this->params()->fromQuery('end', null);
            $end .= ' 23:59:59';

            if ($groupId = $this->params()->fromQuery('groupId', null)) {
                $events = $this->eventTable->getEventsByGroupId($groupId, $start, $end);
            } else {
                $events = $this->eventTable->getAllByUserId($this->getUser()->id, $start, $end);
            }

            $result = [];
            $config = $this->get('config');
            foreach ($events as $event) {
                $disponibility = $this->disponibilityTable->fetchOne([
                    'userId'  => $this->getUser()->id,
                    'eventId' => $event->id
                ]);

                $count = $this->disponibilityTable->count([
                    'eventId' => $event->id,
                    'response' => Model\Disponibility::RESP_OK
                ]);

                $className = 'event-default';
                if ($disponibility) {
                    if ($disponibility->response == Model\Disponibility::RESP_OK) {
                        $className = 'event-green';
                    } else if ($disponibility->response == Model\Disponibility::RESP_NO) {
                        $className = 'event-red';
                    } else if ($disponibility->response == Model\Disponibility::RESP_INCERTAIN) {
                        $className = 'event-azure';
                    }
                }

                $eventDate = \Datetime::createFromFormat('Y-m-d H:i:s', $event->date);
                $result[]  = [
                    'id'           => $event->id,
                    'title'        => $event->name,
                    'start'        => $eventDate->format('Y-m-d H:i'),
                    'url'          => $config['baseUrl'] . '/event/detail/' . $event->id,
                    'className'    => $className,
                    'count'        => $count,
                    'place'        => $event->place,
                    'address'      => $event->address,
                    'zipcode'      => $event->zipCode,
                    'city'         => $event->city,
                    'month'        => \Application\Service\Date::toFr($eventDate->format('F')),
                    'date'         => $eventDate->format('d'),
                    'day'          => \Application\Service\Date::toFr($eventDate->format('D')) . ' ' . $eventDate->format('H:i'),
                ];
            }

            if ($groupId) {
                $users = $this->userTable->getAllByGroupId($groupId);
                foreach ($users as $user) {
                    $ids[] = $user->id;
                    $data[$user->id] = $user;
                }
                $holidays = $this->holidayTable->fetchAll(['userId' => $ids]);
                foreach ($holidays as $holiday) {
                    $from =  \Datetime::createFromFormat('Y-m-d H:i:s', $holiday->from);
                    $to   =  \Datetime::createFromFormat('Y-m-d H:i:s', $holiday->to);
                    $result[] = [
                        'title' => $data[$holiday->userId]->getFullname(),
                        'start' => $from->format('Y-m-d'),
                        'className' => 'event-absent',
                        'end'   => $to->modify('+ 1day')->format('Y-m-d'),
                    ];
                }
            }

            $view = new ViewModel(['result' => $result]);
            $view->setTerminal(true);
            $view->setTemplate('api/default/json.phtml');
            return $view;
        }
    }

    public function liveAction()
    {
        $id     = $this->params('id', null);
        $set    = $this->params('set', null);
        $last   = $this->params('last', 1);
        $points = $this->statsTable->fetchAll(['eventId' => $id, 'id > ?' => $last], 'id DESC');
        $result = [];
        foreach ($points as $stat) {
            if ($stat->set != $set) {
                $view = new ViewModel(['result' => 'reload']);
                $view->setTerminal(true);
                $view->setTemplate('api/default/json.phtml');
                return $view;
                break;
            } else {
                $result[] = [
                    'id' => $stat->id,
                    'us' => $stat->scoreUs,
                    'them' => $stat->scoreThem,
                    'blockUs' => $stat->blockUs ? $stat->blockUs : '-',
                    'defenceUs' => $stat->defenceUs ? $stat->defenceUs : '-',
                    'blockThem' => $stat->blockThem ? $stat->blockThem : '-',
                    'defenceThem' => $stat->defenceThem ? $stat->defenceThem : '-',
                    'reason' => Statistics::$reason[$stat->reason],
                    'pointFor' => $stat->pointFor,
                ];
            }
            $result = array_reverse($result);
        }

        $view = new ViewModel(['result' => $result]);
        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

}