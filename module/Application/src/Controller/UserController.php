<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Service;
use Application\TableGateway;
use Application\Service\MailService;

class UserController extends AbstractController
{

    public function paramsAction()
    {
        if ($this->getUser()) {
            $form = new Form\SignUp;
            $form->setData($this->getUser()->toArray());
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $post['status']  = $this->getUser()->status;
                $post['display'] = $this->getUser()->display;

                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $data['firstname']= $post['firstname'];
                    $data['lastname'] = $post['lastname'];
                    $data['phone']    = $post['phone'];
                    $data['licence']  = $post['licence'] ? $post['licence'] : null;
                    $this->_user->exchangeArray($data);
                    $user = $this->userTable->save($this->_user);
                    $this->_user = $user;
                    $this->flashMessenger()->addSuccessMessage('Enregistré !');
                    $this->redirect()->toUrl('/user/params');
                }
            }
            $notifs = $this->notifTable->fetchAll(['userId' => $this->getUser()->id]);

            return new ViewModel([
                'form'          => $form,
                'notifications' => $notifs,
                'user'          => $this->getUser(),
            ]);

        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
    }
}