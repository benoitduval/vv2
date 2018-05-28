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

    public function paramsOldAction()
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

    public function profileAction()
    {
        $form = new Form\Profile('uploader', ['userId' => $this->getUser()->id]);
        $tempFile = null;

        $prg = $this->fileprg($form);
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response) {
            return $prg; // Return PRG redirect response
        } elseif (is_array($prg)) {
            if ($form->isValid()) {
                $data = $form->getData();
                // Form is valid, save the form!
            } else {
                // Form not valid, but file uploads might be valid...
                // Get the temporary file information to show the user in the view
                $fileErrors = $form->get('image-file')->getMessages();
                if (empty($fileErrors)) {
                    $tempFile = $form->get('image-file')->getValue();
                }
            }
        }

        $this->layout()->setTemplate('layout/no-title.phtml');
        $this->layout()->user = $this->getUser();

        return new ViewModel([
            'user'   => $this->getUser(),
            'form'     => $form,
        ]);
    }
}