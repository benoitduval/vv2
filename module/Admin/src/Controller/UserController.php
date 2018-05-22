<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\Form;
use Application\TableGateway;

class UserController extends AbstractController
{

    public function indexAction()
    {
        // to do : change this to a super admin account
        if ($this->getUser()->id == 1) {
            $users  = $this->userTable->fetchAll();

            $this->layout()->setTemplate('admin/layout/layout.phtml');
            return new ViewModel([
                'users' => $users,
            ]);
        }
    }

    public function detailAction()
    {
        $id         = $this->params('id', null);
        $userTable  = $this->get(TableGateway\User::class);
        $user       = $userTable->find($id);

        $groupTable = $this->get(TableGateway\Group::class);
        $groups     = $groupTable->getUserGroups($user->id);

        $eventTable = $this->get(TableGateway\Event::class);
        $events = $eventTable->getUserEvents($user->id);

        $absentTable = $this->get(TableGateway\Absent::class);
        $absents = $absentTable->fetchAll(['userId' => $user->id]);

        $notifTable = $this->get(TableGateway\Notification::class);
        $notifs = $notifTable->fetchAll(['userId' => $user->id]);

        $form = new Form\SignUp;
        $form->setData($user->toArray());
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $data['email'] = $user->email;
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                foreach ($data as $key => $value) {
                    if (!$value) unset($data[$key]);
                }
                $data['id'] = $user->id;
                $userTable->save($data);
            }
        }

        $this->layout()->setTemplate('admin/layout/layout.phtml');
        return new ViewModel([
            'absents' => $absents,
            'notifs' => $notifs,
            'events' => $events,
            'groups' => $groups,
            'user'   => $user,
            'form'   => $form,
        ]);
    }
}