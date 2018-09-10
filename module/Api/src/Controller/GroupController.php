<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;
use Application\Service\MailService;

class GroupController extends AbstractController
{

    public function indexAction()
    {
        $groupId    = $this->params('groupId', null);
        $groupTable = $this->get(TableGateway\Group::class);
        $userGroupTable = $this->get(TableGateway\UserGroup::class);
        $isAdmin    = false;
        $bcc        = [];
        if($group = $groupTable->find($groupId)) {
            $isAdmin = $userGroupTable->isAdmin($this->getUser()->id, $groupId);
        }
        $emails = $this->params()->fromPost('emails');
        if ($this->getUser() && $emails && $isAdmin) {
            $config = $this->get('config');
            if ($config['mail']['allowed']) {
                $emails = explode(',', $emails);
                foreach ($emails as $email) {
                    $email = trim($email);
                    $bcc[] = $email;
                }
                $mail        = $this->get(MailService::class);
                $mail->addBcc($bcc);
                $mail->setSubject('[Volley-ball.eu] Vous avez été invité!');
                $mail->setTemplate(MailService::TEMPLATE_GROUP_SHARE, [
                    'name'    => $group->name,
                    'brand'   => $group->brand,
                    'baseUrl' => $config['baseUrl']

                ]);
                $mail->send();
            }
        } else {
            $data = ['result' => ['success' => false]];
        }
        if (!isset($data)) {
            $data = ['result' => ['success' => true]];
        }

        $view = new ViewModel($data);

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

    public function nameAction()
    {
        $name = $this->params('name', null);
        $name    = \Application\Service\Strings::toSlug($name);
        $groupExist = is_null($this->groupTable->fetchOne(['brand' => $name]));
        $data = ['result' => ['valid' => $groupExist]];
        $view = new ViewModel($data);

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}