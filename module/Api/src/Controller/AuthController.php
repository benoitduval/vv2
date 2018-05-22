<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;

class AuthController extends AbstractController
{
    public function signinAction()
    {
        $user = $this->getUser();

        $result = new ViewModel([
            'result' => ['ok']
        ]);
        $result->setTerminal(true);
        $result->setTemplate('api/default/json.phtml');

        return $result;
    }
}