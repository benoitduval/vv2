<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\Form;
use Application\TableGateway;

class EventController extends AbstractController
{

    public function indexAction()
    {
        // to do : change this to a super admin account
        if ($this->getUser()->id == 1) {
            $events  = $this->eventTable->fetchAll();

            $this->layout()->setTemplate('admin/layout/layout.phtml');
            return new ViewModel([
                'events' => $events,
            ]);
        }
    }

    public function detailAction()
    {
        $eventId = $this->params('id');
        $start = microtime();
        if (($event = $this->eventTable->find($eventId)) && $this->getUser()->id == 1) {

            $setsHistory   = $this->statsTable->getSetsHistory($eventId);
            $setsStats     = $this->statsTable->getSetsStats($eventId);
            $overallStats  = $this->statsTable->getOverallStats($eventId);
            $setsLastScore = $this->statsTable->setsLastScore($eventId);

            $comments  = $this->commentTable->fetchAll(['eventId' => $event->id]);
            $group     = $this->groupTable->find($event->groupId);

            $counters  = $this->disponibilityTable->getCounters($eventId);
            $disponibilities    = $this->disponibilityTable->fetchAll(['eventId' => $eventId]);
            $myGuest   = $this->disponibilityTable->fetchOne(['eventId' => $eventId, 'userId' => $this->getUser()->id]);
            $eventDate = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);

            $availability    = [
                Model\Disponibility::RESP_NO_ANSWER => [],
                Model\Disponibility::RESP_OK        => [],
                Model\Disponibility::RESP_NO        => [],
                Model\Disponibility::RESP_UNCERTAIN => [],
            ];

            foreach ($disponibilities as $disponibility) {
                $users[$disponibility->userId] = $this->userTable->find($disponibility->userId);
                $availability[$disponibility->response][] = $users[$disponibility->userId];
            }

            $result = [];
            foreach ($comments as $comment) {
                if (!isset($users[$comment->userId])) {
                    $author = $this->userTable->find($comment->userId);
                } else {
                    $author = $users[$comment->userId];
                }

                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $comment->date);
                $result[$comment->id]['date']    = $date->format('d F Y \à H:i');
                $result[$comment->id]['author']  = $author;
                $result[$comment->id]['comment'] = $comment->comment;
            }

            $counters = $this->disponibilityTable->getCounters($eventId);

            $config     = $this->get('config');
            $baseUrl    = $config['baseUrl'];

            $end = microtime();
            error_log($end - $start);
            $this->layout()->setTemplate('admin/layout/layout.phtml');
            return new ViewModel([
                'overallStats'    => $overallStats,
                'setsLastScore'   => $setsLastScore,
                'setsStats'       => $setsStats,
                'setsHistory'     => $setsHistory,
                'counters'        => $counters,
                'comments'        => $result,
                'event'           => $event,
                'group'           => $group,
                'users'           => $availability,
                'user'            => $this->getUser(),
                'date'            => $eventDate,
                'myDisponibility' => $myGuest,
                'disponibilities' => json_encode(array_values($counters))
            ]);
        } else {
            $this->redirect()->toRoute('home');
        }
    }
}