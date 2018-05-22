<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\TableGateway;
use Application\Model;
use Application\Form\SignIn;
use Application\Form\SignUp;
use Application\Form\Reset;
use Application\Form\Password;
use Application\Service\AuthenticationService;
use Application\Service\StorageCookieService;
use Application\Service\MailService;
use Zend\Crypt\Password\Bcrypt;
use Application\Service\Storage\Cookie as CookieStorage;

class AuthController extends AbstractController
{
    public function signinAction()
    {
        if ($this->getUser()) $this->redirect()->toRoute('home');

        $group = $this->params()->fromQuery('group', null);
        $signInForm = new SignIn();
        $request    = $this->getRequest();

        if ($request->isPost()) {
            $signInForm->setData($request->getPost());
            if ($signInForm->isValid()) {
                $data = $signInForm->getData();
                $authService = $this->get(AuthenticationService::class);
                if (!$authService->hasIdentity()) {
                    $adapter  = $authService->getAdapter();
                    $adapter->setIdentity($data['email']);
                    $adapter->setCredential($data['password']);
                    $result = $authService->authenticate();
                    if ($result->isValid()) {
                        $this->setActiveUser($authService->getIdentity());
                        if ($group) {
                            $this->redirect()->toRoute('group-join', ['group' => $group]);
                        } else {
                            $this->redirect()->toRoute('home');
                        }
                    } else {
                        foreach ($result->getMessages() as $message) {
                            $this->flashMessenger()->addErrorMessage($message);
                        }
                        $this->redirect()->toRoute('auth', ['action' => 'signin']);
                    }
                }
            }
        }

        $this->layout()->setTemplate('layout/auth.phtml');
        $this->layout()->group = $group;
        return new ViewModel([
            'group'      => $group,
            'signInForm' => $signInForm
        ]);
    }

    public function signoutAction()
    {
        $authService = $this->get(AuthenticationService::class);
        $authService->clearIdentity();
        $this->setActiveUser(null);

        $this->redirect()->toRoute('home');
    }

    public function signupAction()
    {
        if ($this->getUser()) $this->redirect()->toRoute('home');

        $group        = $this->params()->fromQuery('group', null);
        $signUpForm = new SignUp();
        $request    = $this->getRequest();

        if ($request->isPost()) {
            $signUpForm->setData($request->getPost());
            if ($signUpForm->isValid()) {
                $data = $signUpForm->getData();

                if ($this->userTable->fetchOne(['email' => $data['email']])) {
                    $this->flashMessenger()->addErrorMessage('Cette adresse Email existe déjà.');
                    $this->redirect()->toRoute('auth', ['action' => 'signup']);
                } elseif ($data['password'] != $data['repassword']) {
                    $this->flashMessenger()->addErrorMessage('Les mots de passe ne correspondent pas.');
                    $this->redirect()->toRoute('auth', ['action' => 'signup']);
                } else {
                    $bCrypt = new Bcrypt();
                    $data['status']   = Model\User::CONFIRMED;
                    $data['password'] = $bCrypt->create(md5($data['password']));

                    $user = $this->userTable->save($data);

                    // create account notifications
                    $notifs = Model\Notification::$labels;
                    foreach ($notifs as $id => $label) {
                        $this->notifTable->save([
                            'userId' => $user->id,
                            'notification' => $id,
                            'status' => Model\Notification::ACTIVE
                        ]);
                    }

                    $authService = $this->get(AuthenticationService::class);
                    $authService->getStorage()->write($user);
                    $this->setActiveUser($user);

                    if ($group) {
                        $this->redirect()->toRoute('group-join', ['group' => $group]);
                    } else {
                        $this->redirect()->toRoute('home');
                    }
                }
            }
        }

        $this->layout()->setTemplate('layout/auth.phtml');
        $this->layout()->group = $group;
        return new ViewModel([
            'group'      => $group,
            'signUpForm' => $signUpForm
        ]);
    }

    public function verifyAction()
    {
        $config = $this->get('config');
        $email      = $this->params()->fromQuery('email');
        $paramToken = $this->params()->fromQuery('token');
        if ($user = $this->userTable->fetchOne(['email' => $email])) {
            $token = md5($user->email . $config['salt']);
            if ($paramToken == $token) {
                $user->status = Model\User::CONFIRMED;
                $this->userTable->save($user);

                $authService = $this->get(AuthenticationService::class);
                if (!$authService->hasIdentity()) {
                    $authService->getStorage()->write($user);
                    $this->setActiveUser($user);
                    $this->flashMessenger()->addSuccessMessage('Votre compte est maintenant actif. Merci d\'utiliser http://volley-ball.eu.');
                }
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Désolé, nous n\'avons pas pu confirmer votre compte, un erreur est survenue lors de cette vérification. Merci de me contacter afin de régler ce soucis.');
        }
        $this->redirect()->toRoute('home');
    }

    public function emailAction()
    {
        $form = new Reset();
        if ($this->getRequest()->isPost()) {
            if ($email = $this->params()->fromPost('email')) {
                if ($user = $this->userTable->fetchOne(['email' => $email])) {
                    $config = $this->get('config');

                    $view       = new \Zend\View\Renderer\PhpRenderer();
                    $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
                    $resolver->setMap([
                        'event' => __DIR__ . '/../../view/mail/password.phtml'
                    ]);
                    $view->setResolver($resolver);

                    $viewModel  = new ViewModel();
                    $token = md5($user->email . $config['salt']);
                    $viewModel->setTemplate('event')->setVariables(array(
                        'url'   => $config['baseUrl'] . '/auth/reset?email=' . urlencode($user->email) . '&token=' . $token,
                        'baseUrl'   => $config['baseUrl']
                    ));

                    $mail = $this->get(MailService::class);
                    $mail->addTo($user->email);
                    $mail->setSubject('[Volley-ball.eu] Mot de passe oublié');
                    $mail->setBody($view->render($viewModel));
                    try {
                        $mail->send();
                    } catch (\Exception $e) {
                    }

                    return $this->redirect()->toUrl('/auth/signin');
                }
            }
        }
        $this->layout()->setTemplate('layout/auth.phtml');
        return new ViewModel([
            'form'   => $form,
        ]);
    }

    public function resetAction()
    {
        $email = $this->params()->fromQuery('email');
        $token = $this->params()->fromQuery('token');
        if ($user = $this->userTable->fetchOne(['email' => $email])) {
            $config = $this->get('config');
            $salt   = $config['salt'];
            $verify = md5($user->email . $config['salt']);
            if ($verify == $token) {
                $form = new Password;
                $request    = $this->getRequest();

                if ($request->isPost()) {
                    $form->setData($request->getPost());
                    if ($form->isValid()) {
                        $data = $form->getData();
                        if ($data['password'] == $data['repassword']) {
                            $bCrypt = new Bcrypt();
                            $user->password = $bCrypt->create(md5($data['password']));
                            $this->userTable->save($user->toArray());
                            $authService = $this->get(AuthenticationService::class);
                            $authService->getStorage()->write($user);
                            $this->setActiveUser($user);
                            $this->flashMessenger()->addSuccessMessage('Votre mot de passe est modifié.');
                            return $this->redirect()->toRoute('home');
                        }
                    }
                }

                $this->layout()->setTemplate('layout/auth.phtml');
                return new ViewModel([
                    'form'   => $form,
                ]);
            }

        }
    }
}