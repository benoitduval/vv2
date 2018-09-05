<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\TableGateway;
use Application\Service\AuthenticationService;
use Application\Service\StorageCookieService;
use Application\Model;
use Application\Form\SignIn;
use Application\Service\MailService;
use Application\Service\OneSignalService;
use Application\Service\NotificationService;

class IndexController extends AbstractController
{
    public function indexAction()
    {
        if ($this->getUser()) {

            $userId      = $this->getUser()->id;
            $groups      = $this->getUserGroups();
            $adminGroups = [];
            if ($groups) {
                foreach ($groups as $group) {
                    if ($this->userGroupTable->isAdmin($userId, $group->id)) {
                        $adminGroups[] = $group;
                    }
                    $count[$group->id] = $this->eventTable->count(['groupId' => $group->id]);
                }
            }

            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost()->toArray();
                $this->_createEvent($post);
            }

            // $this->layout()->setTemplate('layout/page-aside.phtml');
            $this->layout()->user = $this->getUser();
            $this->layout()->count = $count;

            return new ViewModel([
                'user'   => $this->getUser(),
                'adminGroups'   => $adminGroups,
            ]);
        } else {
            return $this->redirect()->toUrl('/welcome');
        }
    }

    public function welcomeAction()
    {
        $this->layout()->setTemplate('layout/landing.phtml');
    }

    public function _createEvent($data)
    {
        $groupId = $data['group'];
        $isAdmin = $this->userGroupTable->isAdmin($this->getUser()->id, $groupId);
        if (($group = $this->groupTable->find($groupId)) && $isAdmin) {
            $date = \DateTime::createFromFormat('Y-m-d H:s', $data['date'] . ' ' . $data['time']);
            $data['date'] = $data['date'] . ' ' . $data['time'] . ':00';
            unset($data['time']);
            $data['groupId'] = $groupId;
            $event = $this->eventTable->save($data);

            // Create disponibility for this new event
            $emails = [];
            // $userGroups = $this->userGroupTable->fetchAll(['groupId' => $group->id]);
            $users = $this->userTable->getAllByGroupId($groupId);
            foreach ($users as $user) {
                $absent = $this->holidayTable->fetchOne([
                    '`from` < ?' => $date->format('Y-m-d H:i:s'),
                    '`to` > ?'   => $date->format('Y-m-d H:i:s'),
                    'userId = ?' => $user->id
                ]);

                if ($absent) {
                    $response = Model\Disponibility::RESP_NO;
                } else {
                    $response = Model\Disponibility::RESP_NO_ANSWER;
                    if ($this->notifTable->isAllowed(Model\Notification::EVENT_SIMPLE, $user->id)) {
                        $emails[] = $user->email;
                    }
                }

                $this->disponibilityTable->save([
                    'eventId'  => $event->id,
                    'userId'   => $user->id,
                    'response' => $response,
                    'groupId'  => $groupId,
                ]);
            }

            $notifService = $this->get(NotificationService::class);
            $notifService->send(NotificationService::TYPE_EVENT, [
                'event'     => $event,
                'group'     => $group,
                'date'      => $date,
            ]);

            $this->flashMessenger()->addSuccessMessage('Votre évènement a bien été créé. Les notifications ont été envoyés aux membres du groupe.');
            $this->redirect()->toRoute('home');
        }
    }
}