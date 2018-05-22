<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;

class UserController extends AbstractController
{

    public function grantAction()
    {
        $userId  = $this->params('userId', null);
        $groupId = $this->params('groupId', null);
        $status  = $this->params('status', null);

        $userGroupTable = $this->get(TableGateway\UserGroup::class);
        $userGroup = $userGroupTable->fetchOne(['userId' => $userId, 'groupId' => $groupId]);

        $userGroup->admin = $status;
        $userGroupTable->save($userGroup);

        $view = new ViewModel(array(
            'result'   => [
                'success'  => true
            ]
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

    public function paramsAction()
    {
        $notifId = $this->params('id', null);
        $status = $this->params('status', null);

        $notifTable = $this->get(TableGateway\Notification::class);
        $notif = $notifTable->find($notifId);
        $notif->status = $status;
        $notifTable->save($notif);

        $view = new ViewModel(array(
            'result'   => [
                'success'  => true
            ]
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

    public function displayAction()
    {
        $userTable = $this->get(TableGateway\User::class);

        $display  = $this->params('display', null);
        $this->getUser()->display = $display;
        $userTable->save($this->getUser());

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