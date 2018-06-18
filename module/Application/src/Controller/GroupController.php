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
use Application\Form;
use Application\Model;
use Application\Service\MailService;
use Application\Service;

class GroupController extends AbstractController
{

    protected $_group    = null;
    protected $_id       = null;
    protected $_isAdmin  = false;
    protected $_isMembre = false;
    protected $_groups   = [];

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        if ($this->_id = $this->params('id')) {
            if ($this->_group = $this->groupTable->find($this->_id)) {
                $this->_isAdmin  = $this->userGroupTable->isAdmin($this->getUser()->id, $this->_id);
                $this->_isMember = $this->userGroupTable->isMember($this->getUser()->id, $this->_id);
            }
        }
        return parent::onDispatch($e);
    }

    public function createAction()
    {
        if ($this->getUser()) {
            $groupForm      = new Form\Group;
            $config         = $this->get('config');

            // bypass validation on fields
            $groupForm->getInputFilter()->get('eventDay[]')->setRequired(false);
            $request = $this->getRequest();
            if ($request->isPost()) {

                $data = $request->getPost();
                $groupForm->setData($data);
                if ($groupForm->isValid()) {
                    $data               = $groupForm->getData();
                    $data['name']       = ucfirst($data['name']);
                    $data['brand']      = Service\Strings::toSlug($data['name']);

                    $group = $this->groupTable->save($data);

                    $userGroup = $this->userGroupTable->save([
                        'userId'  => $this->getUser()->id,
                        'groupId' => $group->id,
                        'admin'   => 1,
                    ]);

                    $data = $request->getPost();
                    foreach ($data['place'] as $key => $value) {
                        if (!($data['address'][$key])
                            || !($data['address'][$key])
                            || !($data['zipCode'][$key])
                            || !($data['city'][$key])
                            || !($data['eventDay'][$key])
                            || !($data['time'][$key])) {
                                continue;
                        }
                        $training = [
                            'groupId' => $group->id,
                            'status'  => Model\Training::ACTIVE,
                            'address' => $data['place'][$key],
                            'zipCode' => $data['zipCode'][$key],
                            'city' => $data['city'][$key],
                            'eventDay' => $data['eventDay'][$key],
                            'emailDay' => $data['eventDay'][$key],
                            'time' => $data['time'][$key],
                            'name' => 'Entrainement ' . Service\Date::toFr($data['eventDay'][$key]),
                        ];
                        $this->trainingTable->save($training);
                    }

                    // send emails
                    if ($config['mail']['allowed']) {
                        if ($data['emails']) {
                            $bcc = [];
                            $emails = explode(',', $data['emails']);
                            foreach ($emails as $email) {
                                $email = trim($email);
                                $validator = new \Zend\Validator\EmailAddress();
                                if ($validator->isValid($email)) {
                                    $bcc[] = $email;
                                }
                            }

                            $view       = new \Zend\View\Renderer\PhpRenderer();
                            $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
                            $resolver->setMap([
                                'invitation' => __DIR__ . '/../../view/mail/invitation.phtml'
                            ]);
                            $view->setResolver($resolver);

                            $viewModel  = new ViewModel();
                            $viewModel->setTemplate('invitation')->setVariables([
                                'group'     => $group,
                                'user'      => $this->getUser(),
                                'shareUrl'  => $config['baseUrl'] . '/group/join/' . $group->brand,
                                'baseUrl'   => $config['baseUrl']
                            ]);

                            $mail = $this->get(MailService::class);
                            $mail->addBcc($emails);
                            $mail->setSubject('[' . $group->name . '] Rejoignez le groupe !');
                            $mail->setBody($view->render($viewModel));
                            try {
                                $mail->send();
                            } catch (\Exception $e) {
                            }
                        }
                    }

                    return $this->redirect()->toRoute('group-welcome', ['brand' => $group->brand]);
                }
            }

            $baseUrl = $config['baseUrl'];

            return new ViewModel(array(
                'form'    => $groupForm,
                'user'    => $this->getUser(),
                'group'   => isset($group) ? $group : '',
                'baseUrl' => $baseUrl,
            ));
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
    }

    public function editAction()
    {
        if ($this->_group && $this->_isAdmin) {
            $form       = new Form\Group;
            $form->setData($this->_group->toArray());
            $request    = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $data       = $form->getData();
                    $data['brand'] = $this->_group->initBrand($data['name']);
                    $this->_group->exchangeArray($data);
                    $this->groupTable->save($this->_group);
                }
                $this->flashMessenger()->addSuccessMessage('Votre groupe a bien été modifié.');
                return $this->redirect()->toRoute('group-welcome', ['brand' => $this->_group->brand]);

            }

            return new ViewModel(array(
                'form'    => $form,
                'user'    => $this->getUser(),
                'group'   => isset($this->_group) ? $this->_group : '',
                'isAdmin' => $this->_isAdmin,
            ));
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            return $this->redirect()->toRoute('home');
        }
    }

    public function welcomeAction()
    {
        $brand = $this->params('brand');
        $group = $this->groupTable->fetchOne(['brand' => $brand]);
        $user  = $this->getUser();
        if (!$this->userGroupTable->isMember($user->id, $group->id)) $this->redirect()->toRoute('home');

        $form = new Form\Profile('uploader', ['entity' => $group->name . $group->id]);
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

        $users       = $this->userTable->getAllByGroupId($group->id);
        $isAdmin     = $this->userGroupTable->isAdmin($user->id, $group->id);
        $isMember    = $this->userGroupTable->isMember($user->id, $group->id);
        $trainings   = $this->trainingTable->fetchAll(['groupId' =>  $group->id]);
        $events      = $this->eventTable->getAllByGroupId($group->id);
        $eventsCount = $this->eventTable->count(['groupId' =>  $group->id]);
        $config      = $this->get('config');

        $present  = [];
        $winCount = $loseCount = 0;
        foreach ($events as $event) {
            if ($event->victory == null) continue;
            $event->victory ? $winCount ++ : $loseCount ++;
            $present[$event->id] = $this->disponibilityTable->getUserIds([
                'eventId'  => $event->id,
                'response' => Model\Disponibility::RESP_OK
            ]);
            $games[] = $event;
            $gameIds[] = $event->id;
            $labels[] = (string) $event->name;
        }

        $stats = $this->statsTable->getRatioEvolution($gameIds);
        $attackRatio = json_encode($stats);
        $labels = json_encode(array_reverse($labels), JSON_HEX_QUOT);

        $matchCount = $winCount + $loseCount;
        $winPercent = $losePercent = 0;
        if ($matchCount) {
            $winPercent  = floor(($winCount * 100) / $matchCount);
            $losePercent = floor(($loseCount * 100) / $matchCount);
        }

        $shareUrl = $config['baseUrl'] . '/group/join/' . $group->brand;
        $this->layout()->group = $group;
        $this->layout()->isAdmin = $isAdmin;

        return new ViewModel([
            'user'          => $user,
            'form'          => $form,
            'group'         => $group,
            'users'         => $users,
            'isMember'      => $isMember,
            'isAdmin'       => $isAdmin,
            'trainings'     => $trainings,
            'shareUrl'      => $shareUrl,
            'eventsCount'   => $eventsCount,
            'winCount'      => $winCount,
            'loseCount'     => $loseCount,
            'matchCount'    => $matchCount,
            'winPercent'    => $winPercent,
            'losePercent'   => $losePercent,
            'games'         => $games,
            'present'       => $present,
            'attackRatio'   => $attackRatio,
            'labels'   => $labels,
        ]);
    }

    public function usersAction()
    {
        if ($this->_group && $this->_isAdmin) {
            $this->userTable = $this->get(TableGateway\User::class);
            $this->joinTable = $this->get(TableGateway\Join::class);

            $joins = $this->joinTable->fetchAll([
                'groupId' => $this->_id
            ]);

            $joinUserIds = [];
            $userJoin    = [];
            foreach ($joins as $join) {
                $joinUserIds[] = $join->userId;
            }
            if (!empty($joinUserIds)) $userJoin = $this->userTable->fetchAll(['id' => $joinUserIds]);

            $this->userGroupTable = $this->get(TableGateway\UserGroup::class);
            $userGroups = $this->userGroupTable->fetchAll([
                'groupId' => $this->_id
            ]);

            $adminIds = [];
            foreach ($userGroups as $userGroup) {
                if ($userGroup->admin) $adminIds[] = $userGroup->userId;
                $userIds[]  = $userGroup->userId;
            }

            $users = $this->userTable->fetchAll(['id' => $userIds]);

            return new ViewModel([
                'adminIds' => $adminIds,
                'isAdmin'  => $this->_isAdmin,
                'users'    => $users,
                'group'    => $this->_group,
                'joins'    => $userJoin,
                'user'     => $this->getUser()
            ]);
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
    }

    public function deleteUserAction()
    {
        $userId    = $this->params('userId');

        if ($userId && $this->_group && $this->_isAdmin) {
            $userGroup = $this->userGroupTable->fetchOne([
                'groupId' => $this->_id,
                'userId'  => $userId
            ]);

            $events = $this->eventTable->fetchAll([
                'date > NOW()',
                'groupId' => $this->_group->id
            ]);
            foreach ($events as $event) {
                $disponibility = $this->disponibilityTable->fetchOne([
                    'userId'  => $userId,
                    'groupId' => $this->_group->id,
                    'eventId' => $event->id,
                ]);
                $this->disponibilityTable->delete($disponibility);
            }
            $this->userGroupTable->delete($userGroup);

            $this->flashMessenger()->addSuccessMessage('Utilisateur supprimé.');
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
        }
        $this->redirect()->toRoute('group', ['action' => 'users', 'id' => $this->_group->id]);
    }

    public function addUserAction()
    {
        $userId    = $this->params('userId');

        if ($userId && $this->_group && $this->_isAdmin) {
            $userGroup = $this->userGroupTable->save([
                'groupId' => $this->_group->id,
                'userId'  => $userId,
                'admin'   => Model\UserGroup::MEMBER,
            ]);

            $events = $this->eventTable->fetchAll([
                'date > NOW()',
                'groupId' => $this->_group->id
            ]);

            foreach ($events as $event) {
                $absent = $this->holidayTable->fetchOne([
                    'userId'     => $userId,
                    '`from` < ?' => $event->date,
                    '`to` > ?'   => $event->date,
                ]);

                $response = $absent ? Model\Disponibility::RESP_NO : Model\Disponibility::RESP_NO_ANSWER;
                $disponibility = $this->disponibilityTable->save([
                    'userId'  => $userId,
                    'groupId' => $this->_group->id,
                    'eventId' => $event->id,
                    'response' => $response,
                ]);
            }

            $join = $this->joinTable->fetchOne([
                'userId'  => $userId,
                'groupId' => $this->_group->id,
            ]);
            $this->joinTable->delete($join);

            $this->flashMessenger()->addSuccessMessage('Utilisateur ajouté.');
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            return $this->redirect()->toRoute('home');
        }
        $this->redirect()->toRoute('group', ['action' => 'users', 'id' => $this->_group->id]);
    }

    public function deleteAction()
    {
        if ($this->_group && $this->_isAdmin) {
            $eventIds = [];

            foreach ($this->eventTable->fetchAll(['groupId' => $this->_id]) as $event) {
                $eventIds[] = $event->id;
            }

            if ($eventIds) {
                $this->commentTable->delete(['eventId' => $eventIds]);
                $this->disponibilityTable->delete(['eventId' => $eventIds]);
            }
            $this->userGroupTable->delete(['groupId' => $this->_id]);
            $this->eventTable->delete(['groupId' => $this->_id]);
            $this->joinTable->delete(['groupId' => $this->_id]);
            $this->groupTable->delete(['id' => $this->_id]);

            $this->flashMessenger()->addSuccessMessage('Le groupe a bien été supprimé.');
            return $this->redirect()->toRoute('home');
        } else {
            return $this->redirect()->toRoute('home');
        }
    }

    public function joinAction()
    {
        $brand = $this->params()->fromRoute('group', null);
        if (!$brand) $this->redirect()->toRoute('home');
        if ($group = $this->groupTable->fetchOne(['brand' => $brand])) {
            if (!$this->getUser()) {
                $this->redirect()->toUrl('/auth/signin?group=' . $brand);
            } else {
                if ($this->userGroupTable->isMember($this->getUser()->id, $group->id)) {
                    $this->redirect()->toRoute('home');
                } else {
                    $user = $this->getUser();
                    $userGroup = $this->userGroupTable->save([
                        'groupId' => $group->id,
                        'userId'  => $user->id,
                        'admin'   => Model\UserGroup::MEMBER,
                    ]);

                    $events = $this->eventTable->fetchAll([
                        'date > NOW()',
                        'groupId' => $group->id
                    ]);

                    foreach ($events as $event) {
                        $holiday = $this->holidayTable->fetchOne([
                            'userId'     => $user->id,
                            '`from` < ?' => $event->date,
                            '`to` > ?'   => $event->date,
                        ]);

                        $response = $holiday ? Model\Disponibility::RESP_NO : Model\Disponibility::RESP_NO_ANSWER;
                        $disponibility = $this->disponibilityTable->save([
                            'userId'   => $user->id,
                            'groupId'  => $group->id,
                            'eventId'  => $event->id,
                            'response' => $response,
                        ]);
                    }
                    $this->redirect()->toRoute('home');
                }
            }
        } else {
            $this->redirect->toRoute('home');
        }
    }
}
