<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;
use Application\Service\MailService;

class SearchController extends AbstractController
{

    public function dataAction()
    {
        $groupTable = $this->get(TableGateway\Group::class);
        $groups = $groupTable->fetchAll();

        $data['result'] = [];
        foreach ($groups as $group) {
            $data['result']['groups'][] = [
                'label' => $group->name,
                'description'  => $group->getPublicLink(),
                'link'  => $group->getPublicLink(),
            ];
        }

        if ($this->getUser()) {
            $userGroupTable = $this->get(TableGateway\UserGroup::class);
            $userGroups = $userGroupTable->fetchAll(['userId' => $this->getUser()->id]);
            foreach ($userGroups as $userGroup) {
                $groupIds[] = $userGroup->groupId;
            }

            if (isset($groupIds)) {

                $eventTable = $this->get(TableGateway\Event::class);
                $events = $eventTable->fetchAll(['groupId' => $groupIds]);
                $results = [];
                foreach ($events as $event) {
                    $id = md5($event->name);
                    if (!isset($results[$id])) {
                        $eventDate = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);
                        $results[$id] = [
                            'label' => $event->name,
                            'description' => \Application\Service\Date::toFr($eventDate->format('d F Y')),
                            'link' => '/event/detail/' . $event->id,
                            'count' => 1,
                        ];
                    } else {
                        $count = $results[$id]['count'] + 1;
                        $results[$id] = [
                            'label' => $event->name,
                            'description' => $count . ' occurences',
                            'link' => '/search?q=' . $event->name,
                            'count' => $count,
                        ];
                    }
                }

                foreach ($results as $result) {
                    $data['result']['events'][] = $result;
                }
            }
        }

        if (!isset($data['result']['events'])) {
            $data['result']['events'][] = [
                'label' => '',
                'description'  => '',
                'link'  => 'javascritp:void();',
            ];
        }

        $view = new ViewModel($data);

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}