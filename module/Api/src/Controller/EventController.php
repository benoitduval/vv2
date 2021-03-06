<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;
use Application\Model\Stats as Statistics;
use Application\Service\NotificationService;

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
                $group     = $this->groupTable->find($event->groupId);
                $disponibility = $this->disponibilityTable->fetchOne([
                    'userId'  => $this->getUser()->id,
                    'eventId' => $event->id
                ]);

                $className = 'event-default';
                if ($disponibility) {
                    if ($disponibility->response == Model\Disponibility::RESP_OK) {
                        $className = 'event-green';
                    } else if ($disponibility->response == Model\Disponibility::RESP_NO) {
                        $className = 'event-red';
                    } else if ($disponibility->response == Model\Disponibility::RESP_UNCERTAIN) {
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
                ];
            }

            $view = new ViewModel(['result' => $result]);
            $view->setTerminal(true);
            $view->setTemplate('api/default/json.phtml');
            return $view;
        }
    }

    public function getInfoAction()
    {
        $result = [];
        if ($this->getUser()) {
            if ($eventId = $this->params('id', null)) {
                $event = $this->eventTable->find($eventId);

                $counters = $this->disponibilityTable->getCounters($event->id);
                $count[Model\Disponibility::LABEL_RESP_OK] = $counters[Model\Disponibility::RESP_OK];
                $count[Model\Disponibility::LABEL_RESP_NO] = $counters[Model\Disponibility::RESP_NO];
                $count[Model\Disponibility::LABEL_RESP_UNCERTAIN] = $counters[Model\Disponibility::RESP_UNCERTAIN];
                $count[Model\Disponibility::LABEL_RESP_NO_ANSWER] = $counters[Model\Disponibility::RESP_NO_ANSWER];

                $disponibilities    = $this->disponibilityTable->fetchAll(['eventId' => $event->id]);
                $users = [
                    Model\Disponibility::RESP_NO_ANSWER => [],
                    Model\Disponibility::RESP_OK        => [],
                    Model\Disponibility::RESP_NO        => [],
                    Model\Disponibility::RESP_UNCERTAIN => [],
                ];
                foreach ($disponibilities as $disp) {
                    $user = $this->userTable->find($disp->userId);
                    $users[$disp->response][] = [
                        'firstname' => $user->firstname,
                        'lastname'  => $user->lastname,
                        'id'        => $user->id,
                        'avatarUrl' => $user->getAvatarPath(),
                    ];
                }
                $team[Model\Disponibility::LABEL_RESP_NO_ANSWER] = $users[Model\Disponibility::RESP_NO_ANSWER];
                $team[Model\Disponibility::LABEL_RESP_OK] = $users[Model\Disponibility::RESP_OK];
                $team[Model\Disponibility::LABEL_RESP_NO] = $users[Model\Disponibility::RESP_NO];
                $team[Model\Disponibility::LABEL_RESP_UNCERTAIN] = $users[Model\Disponibility::RESP_UNCERTAIN];

                $result  = [
                    'count'        => $count,
                    'team'         => $team
                ];
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

    public function commentAction()
    {
        $eventId    = $this->params()->fromPost('eventId', null);
        $text    = $this->params()->fromPost('comment', null);

        $event = $this->eventTable->find($eventId);
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {

            $comment = $this->commentTable->save([
                'date'    => date('Y-m-d H:i:s'),
                'eventId' => $eventId,
                'userId'  => $this->getUser()->id,
                'comment' => $text,
            ]);

            $group = $this->groupTable->find($event->groupId);

            $commentDate = \DateTime::createFromFormat('U', time());
            $eventDate   = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);

            $notifService = $this->get(NotificationService::class);
            $notifService->send(NotificationService::TYPE_COMMENT, [
                'event'       => $event,
                'group'       => $group,
                'date'        => $eventDate,
                'user'        => $this->getUser(),
                'comment'     => $comment,
                'commentDate' => $commentDate,
            ]);
        }

        $view = new ViewModel(['result' => true]);
        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}