<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;
use Admin\Form;

class IndexController extends AbstractController
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
}