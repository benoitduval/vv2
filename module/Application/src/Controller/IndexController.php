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

class IndexController extends AbstractController
{
    public function indexAction()
    {
        if ($this->getUser()) {

            $userId     = $this->getUser()->id;
            $groups     = $this->getUserGroups();
            if ($groups) {
                foreach ($groups as $group) {
                    $count[$group->id] = $this->eventTable->count(['groupId' => $group->id]);
                }
            }

            $this->layout()->setTemplate('layout/page-aside.phtml');
            $this->layout()->user = $this->getUser();
            $this->layout()->count = $count;

            return new ViewModel([
                'user'   => $this->getUser(),
            ]);
        } else {
            return $this->redirect()->toUrl('/welcome');
        }
    }

    public function welcomeAction()
    {
        $this->layout()->setTemplate('layout/landing.phtml');
    }
}
