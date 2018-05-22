<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;

class GroupController extends AbstractController
{

    public function indexAction()
    {
        // to do : change this to a super admin account
        if ($this->getUser()->id == 1) {
            $groups  = $this->groupTable->fetchAll();

            $this->layout()->setTemplate('admin/layout/layout.phtml');
            return new ViewModel([
                'groups' => $groups,
            ]);
        }
    }

    public function detailAction()
    {
        $id         = $this->params('id', null);
        $groupTable = $this->get(TableGateway\Group::class);
        $group      = $groupTable->find($id);

        $userTable = $this->get(TableGateway\User::class);
        $users     = $userTable->getAllByGroupId($group->id);

        $eventTable = $this->get(TableGateway\Event::class);
        $events = $eventTable->getEventsByGroupId($group->id);

        $recurentTable = $this->get(TableGateway\Recurent::class);
        $recurents = $recurentTable->fetchAll(['groupId' => $group->id]);

        $form = new \Application\Form\Group;
        $form->setData($group->toArray());
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                foreach ($data as $key => $value) {
                    if (!$value) unset($data[$key]);
                }
                $data['id'] = $group->id;
                $groupTable->save($data);
            }
        }

        $this->layout()->setTemplate('admin/layout/layout.phtml');
        return new ViewModel([
            'recurents' => $recurents,
            'events'    => $events,
            'users'     => $users,
            'group'     => $group,
            'form'      => $form,
        ]);
    }
}