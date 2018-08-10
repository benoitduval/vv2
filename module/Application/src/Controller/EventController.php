<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Service;
use Application\TableGateway;
use Application\Service\MailService;
use Application\Service\OneSignalService;

class EventController extends AbstractController
{
    public function createAction()
    {
        $groupId = $this->params('id', null);

        $isAdmin = $this->userGroupTable->isAdmin($this->getUser()->id, $groupId);
        if (($group = $this->groupTable->find($groupId)) && $isAdmin) {
            $form = new Form\Event();
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post  = $request->getPost();
                $match = isset($post->match);
                if ($match) unset($post->match);

                $form->setData($post);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $date = \DateTime::createFromFormat('d/m/Y H:i', $data['date']);

                    $data['date']    = $date->format('Y-m-d H:i:s');
                    $data['groupId'] = $groupId;
                    $event = $this->eventTable->save($data);

                    if ($match) {
                        $this->matchTable->save([
                            'eventId' => $event->id
                        ]);
                    }

                    // Create disponibility for this new event
                    $emails = [];
                    // $userGroups = $this->userGroupTable->fetchAll(['groupId' => $group->id]);
                    $users = $this->userTable->getAllByGroupId($groupId);
                    foreach ($users as $user) {
                        $absent = $this->holidayTable->fetchOne([
                            '`from` < ?' => $date->format('Y-m-d H:i:s'),
                            '`to` > ?'   => $date->format('Y-m-d H:i:s'),
                            'userId = ?' => $user->id
                        ]);

                        if ($absent) {
                            $response = Model\Disponibility::RESP_NO;
                        } else {
                            $response = Model\Disponibility::RESP_NO_ANSWER;
                            if ($this->notifTable->isAllowed(Model\Notification::EVENT_SIMPLE, $user->id)) {
                                $emails[] = $user->email;
                            }
                        }

                        $this->disponibilityTable->save([
                            'eventId'  => $event->id,
                            'userId'   => $user->id,
                            'response' => $response,
                            'groupId'  => $groupId,
                        ]);
                    }

                    // send emails
                    $config = $this->get('config');
                    $oneSignal = $this->get(OneSignalService::class);
                    $oneSignal->setData([
                        'header'   => 'Nouvel événement !',
                        'content'  => $event->name,
                        'subtitle' => \Application\Service\Date::toFr($date->format('l d F \à H\hi')),
                        'url'      => $config['baseUrl'] . '/event/detail/' . $event->id,
                    ]);
                    foreach ($emails as $email) {
                        $oneSignal->sendTo($email);
                    }

                    if ($config['mail']['allowed']) {

                        $view       = new \Zend\View\Renderer\PhpRenderer();
                        $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
                        $resolver->setMap([
                            'event' => __DIR__ . '/../../view/mail/event.phtml'
                        ]);
                        $view->setResolver($resolver);

                        $viewModel  = new ViewModel();
                        $viewModel->setTemplate('event')->setVariables(array(
                            'event'     => $event,
                            'group'     => $group,
                            'date'      => $date,
                            'baseUrl'   => $config['baseUrl']
                        ));

                        $mail = $this->get(MailService::class);
                        // $mail->addIcalEvent($event);
                        $mail->addBcc($emails);
                        $mail->setSubject('[' . $group->name . '] ' . $event->name . ' - ' . \Application\Service\Date::toFr($date->format('l d F \à H\hi')));
                        $mail->setBody($view->render($viewModel));
                        try {
                            $mail->send();
                        } catch (\Exception $e) {
                        }
                    }

                    $this->flashMessenger()->addSuccessMessage('Votre évènement a bien été créé. Les notifications ont été envoyés aux membres du groupe.');
                    $this->redirect()->toRoute('home');
                }
            }

            $this->layout()->user = $this->getUser();
            return new ViewModel([
                'group'   => $group,
                'form'    => $form,
                'user'    => $this->getUser(),
                'isAdmin' => $isAdmin
            ]);
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
    }

    public function detailAction()
    {
        $eventId = $this->params('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {

            $stats = $this->statsTable->fetchAll([
                'eventId'  => $eventId,
                'pointFor' => Model\Stats::POINT_US,
                'reason'   => Model\Stats::POINT_ATTACK,
            ]);
            $attackScorer = [];
            foreach ($stats as $stat) {
                if (!in_array($stat->userId, $attackScorer) && $stat->userId) $attackScorer[] = $stat->userId;
            }
            $percents = $this->statsTable->getZonePercent([
                'eventId'  => $eventId,
                'pointFor' => Model\Stats::POINT_US,
                'reason'   => Model\Stats::POINT_ATTACK,
            ]);

            foreach (['allFrom', 'allTo'] as $index) {
                foreach ($percents[$index] as $key => $value) {
                    $$key = $value;
                }
            }
            foreach ($percents['allFrom'] as $key => $value) {
                $$key = $value;
            }
            $percents = json_encode($percents);
            $users = $this->userTable->getAllByGroupId($event->groupId);

            $comments  = $this->commentTable->fetchAll(['eventId' => $event->id]);
            $group     = $this->groupTable->find($event->groupId);
            $isMember  = $this->userGroupTable->isMember($this->getUser()->id, $group->id);
            $isAdmin   = false;
            if ($isMember) {
                $isAdmin = $this->userGroupTable->isAdmin($this->getUser()->id, $group->id);
            }

            $counters  = $this->disponibilityTable->getCounters($eventId);
            $disponibilities    = $this->disponibilityTable->fetchAll(['eventId' => $eventId]);
            $myGuest   = $this->disponibilityTable->fetchOne(['eventId' => $eventId, 'userId' => $this->getUser()->id]);
            $eventDate = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);

            $availabilities = [
                Model\Disponibility::RESP_NO_ANSWER => [],
                Model\Disponibility::RESP_OK        => [],
                Model\Disponibility::RESP_NO        => [],
                Model\Disponibility::RESP_UNCERTAIN => [],
            ];

            foreach ($disponibilities as $disponibility) {
                $availabilities[$disponibility->response][] = $users[$disponibility->userId];
            }

            $result = [];
            foreach ($comments as $comment) {
                if (!isset($users[$comment->userId])) {
                    $author = $this->userTable->find($comment->userId);
                } else {
                    $author = $users[$comment->userId];
                }

                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $comment->date);
                $result[$comment->id]['date']    = $date;
                $result[$comment->id]['author']  = $author;
                $result[$comment->id]['comment'] = $comment->comment;
            }

            $counters = $this->disponibilityTable->getCounters($eventId);

            $config  = $this->get('config');
            $baseUrl = $config['baseUrl'];

            // User submit commment
            $form = new Form\Comment();
            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $data = $form->getData();
                    $eventDate   = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);
                    $comment = $this->commentTable->save([
                        'date'    => date('Y-m-d H:i:s'),
                        'eventId' => $eventId,
                        'userId'  => $this->getUser()->id,
                        'comment' => $data['comment'],
                    ]);

                    $config = $this->get('config');
                    if ($config['mail']['allowed']) {
                        $users = $this->userTable->getAllByGroupId($group->id);
                        $bcc   = [];
                        foreach ($users as $user) {
                            $email = true;
                            $disponibility = $this->disponibilityTable->fetchOne(['userId' => $user->id, 'eventId' => $event->id]);
                            if ($disponibility && $disponibility->response = Model\Disponibility::RESP_NO && !$this->notifTable->isAllowed(Model\Notification::COMMENT_ABSENT, $user->id)) {
                                $email = false;
                            } else if ($this->getUser()->id == $user->id && !$this->notifTable->isAllowed(Model\Notification::SELF_COMMENT, $user->id)) {
                                $email = false;
                            } else if (!$this->notifTable->isAllowed(Model\Notification::COMMENTS, $user->id)) {
                                $email = false;
                            }

                            if ($email) $bcc[] = $user->email;
                        }

                        $config = $this->get('config');
                        if ($config['mail']['allowed']) {
                                $commentDate = \DateTime::createFromFormat('U', time());
                                $date        = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);
                                $view       = new \Zend\View\Renderer\PhpRenderer();
                                $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
                                $resolver->setMap([
                                    'event' => __DIR__ . '/../../view/mail/comment.phtml'
                                ]);
                                $view->setResolver($resolver);

                                $viewModel  = new ViewModel();
                                $viewModel->setTemplate('event')->setVariables(array(
                                    'event'     => $event,
                                    'group'     => $group,
                                    'date'      => $date,
                                    'user'      => $this->getUser(),
                                    'comment'   => $comment,
                                    'commentDate' => $commentDate,
                                    'baseUrl'   => $config['baseUrl']
                                ));

                                $oneSignal = $this->get(OneSignalService::class);
                                $oneSignal->setData([
                                    'header'   => 'Nouvel Commentaire !',
                                    'content'  => $event->name,
                                    'subtitle' => \Application\Service\Date::toFr($date->format('l d F \à H\hi')),
                                    'url'      => $config['baseUrl'] . '/event/detail/' . $event->id,
                                ]);
                                foreach ($users as $user) {
                                    $oneSignal->sendTo($user->email);
                                }

                                $mail = $this->get(MailService::class);
                                $mail->addBcc($bcc);
                                $mail->setSubject('[' . $group->name . '] ' . $event->name . ' - ' . \Application\Service\Date::toFr($date->format('l d F \à H\hi')));
                                $mail->setBody($view->render($viewModel));
                            try {
                                $mail->send();
                            } catch (\Exception $e) {
                            }
                        }
                    }

                    $this->flashMessenger()->addSuccessMessage('Commentaire enregistré');
                    $this->redirect()->toUrl('/event/detail/' . $event->id);
                }
            }

            // $this->layout()->setTemplate('layout/titled.phtml');
            return new ViewModel([
                'stats'           => $stats,
                'percents'        => $percents,
                'attackScorer'    => $attackScorer,
                'fromP1'          => $fromP1,
                'fromP2'          => $fromP2,
                'fromP3'          => $fromP3,
                'fromP4'          => $fromP4,
                'fromP5'          => $fromP5,
                'fromP6'          => $fromP6,
                'toP1'            => $toP1,
                'toP2'            => $toP2,
                'toP3'            => $toP3,
                'toP4'            => $toP4,
                'toP5'            => $toP5,
                'toP6'            => $toP6,
                'counters'        => $counters,
                'comments'        => $result,
                'event'           => $event,
                'form'            => $form,
                'group'           => $group,
                'users'           => $users,
                'availabilities'  => $availabilities,
                'user'            => $this->getUser(),
                'date'            => $eventDate,
                'isAdmin'         => $isAdmin,
                'isMember'        => $isMember,
                'myDisponibility' => $myGuest,
                'disponibilities' => json_encode(array_values($counters)),
                'liveScoreUrl'    => $baseUrl . '/live/' . $event->id . '/' . \Application\Service\Strings::toSlug($event->name)
            ]);
        } else {
            $this->redirect()->toRoute('home');
        }
    }

    public function editAction()
    {
        $eventId    = $this->params()->fromRoute('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isAdmin($this->getUser()->id, $event->groupId)) {

            $event = $this->eventTable->find($eventId);
            $group = $this->groupTable->find($event->groupId);

            $form = new Form\Event();
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);
            $formData = $event->toArray();
            $formData['date'] = $date->format('d/m/Y H:i');
            $form->setData($formData);
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post    = $request->getPost()->toArray();

                $form->setData($request->getPost());
                if ($form->isValid()) {

                    $data = $form->getData();
                    $date = \DateTime::createFromFormat('d/m/Y H:i', $data['date']);

                    $data['date']    = $date->format('Y-m-d H:i:s');

                    $event->exchangeArray($data);
                    $this->eventTable->save($event);

                    // send emails
                    $config = $this->get('config');
                    $oneSignal = $this->get(OneSignalService::class);
                    $oneSignal->setData([
                        'header'   => 'Événement modifié',
                        'content'  => $event->name,
                        'subtitle' => \Application\Service\Date::toFr($date->format('l d F \à H\hi')),
                        'url'      => $config['baseUrl'] . '/event/detail/' . $event->id,
                    ]);
                    $users = $this->userTable->getAllByGroupId($group->id);
                    foreach ($users as $user) {
                        $oneSignal->sendTo($user->email);
                    }
                    $this->flashMessenger()->addSuccessMessage('Votre évènement a bien été modifié.');
                    $this->redirect()->toRoute('event', ['action' => 'detail', 'id' => $eventId]);
                }
            }

            $this->layout()->user = $this->getUser();
            return new ViewModel([
                'group'  => $group,
                'form'   => $form,
                'user'   => $this->getUser(),
            ]);
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
    }

    public function deleteAction()
    {
        $eventId = $this->params()->fromRoute('id');

        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isAdmin($this->getUser()->id, $event->groupId)) {
            $this->commentTable->delete(['eventId' => $event->id]);
            $this->disponibilityTable->delete(['eventId' => $event->id]);
            $this->eventTable->delete(['id' => $event->id]);
            $group = $this->groupTable->find($event->groupId);
            $this->redirect()->toUrl('/welcome-to/' . $group->brand);
        }
    }

    public function resultAction()
    {
        $eventId = $this->params('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isAdmin($this->getUser()->id, $event->groupId)) {
            $eventData = [];
            if ($event->sets) {
                foreach ($event->sets as $key => $score) {
                    $i = $key + 1;
                    $set = explode('-', $score);
                    $eventData['set' . $i . 'Team1'] = $set[0];
                    $eventData['set' . $i . 'Team2'] = $set[1];
                }
            } else {
                for ($i = 1; $i <= 5; $i++) {
                    if ($stats = $this->statsTable->fetchOne(['eventId' => $eventId, 'set' => $i], 'id DESC')) {
                        $eventData['set' . $i . 'Team1'] = $stats->scoreUs;
                        $eventData['set' . $i . 'Team2'] = $stats->scoreThem;
                    }
                }
            }
            $eventData['debrief'] = $event->debrief;
            $form = new Form\Result;
            $form->setData($eventData);
            $request = $this->getRequest();
            if ($request->isPost()) {
                $result = [];
                $post = $request->getPost();
                if ($form->isValid()) {
                    $setFor     = 0;
                    $setAgainst = 0;
                    $sets  = [];
                    for ($i = 1; $i <= 5; $i++) {
                        if ($post['set'.$i.'Team1'] && $post['set'.$i.'Team2']) {
                            if ($post['set'.$i.'Team1'] > $post['set'.$i.'Team2']) {
                                $setFor++;
                            } else {
                                $setAgainst++;
                            }
                            $sets[]  = $post['set'.$i.'Team1'] . '-' . $post['set'.$i.'Team2'];
                        }
                    }
                    $result['sets']    = json_encode($sets);
                    $result['victory'] = ($setFor > $setAgainst) ? 1 : 0;
                    $result['score']   = $setFor . ' / ' . $setAgainst;
                    $result['debrief'] = $post['debrief'];
                    $event->exchangeArray($result);
                    $this->eventTable->save($event);
                    $this->flashMessenger()->addSuccessMessage('Résultat enregistré.');
                    $this->redirect()->toRoute('event', ['action' => 'detail', 'id' => $eventId]);
                }
            }
            return new ViewModel([
                'event'  => $event,
                'form'   => $form,
                'user'   => $this->getUser(),
            ]);
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
    }

    public function deleteStatsAction()
    {
        $statsId = $this->params('id', null);
        $stats = $this->statsTable->find($this->params('id'));
        if ($stats && ($event = $this->eventTable->find($stats->eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {
            $eventId = $stats->eventId;
            $this->statsTable->delete(['id' => $statsId]);
            $key = 'position.' . $eventId . '.numero.' . $stats->numero;
            $this->get('memcached')->removeItem($key);

            $this->flashMessenger()->addSuccessMessage('Point supprimé.');
        }
        $this->redirect()->toUrl('/event/live-stats/' . $eventId);
    }

    public function liveStatsAction()
    {
        $eventId = $this->params('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {

            $users      = $this->userTable->getAllByEventId($event->id);
            $config     = $this->get('config');
            $deleteLink = null;
            $data       = [            
                'scoreUs'    => 0,
                'scoreThem'  => 0,
                'set'        => 1,
                'numero'     => 1,
                'start'      => null,
                'positions'  => [],
            ];

            if ($stats = $this->statsTable->fetchOne(['eventId' => $eventId], 'id DESC')) {
                $data = $stats->getMatchData();
                $deleteLink = $config['baseUrl'] . '/event/delete-stats/' . $stats->id;
            } 

            // Check for a new position
            $key = 'position.' . $eventId . '.numero.' . $data['numero'];
            if ($newPos = $this->get('memcached')->getItem($key)) $data['positions'] = $newPos;

            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost()->toArray();

                // Create a new position
                if (isset($post['form-name']) && $post['form-name'] == 'positions') {
                    $positions = $post;
                    $key = 'position.' . $eventId . '.numero.' . $data['numero'];
                    $this->get('memcached')->setItem($key, $positions);

                // Point Validation
                } else {
                    if ($post['pointFor'] == Model\Stats::POINT_US) {
                        $post['scoreUs']++;
                    } else {
                        $post['scoreThem']++;
                    }

                    $post['eventId']   = $eventId;
                    $post['groupId']   = $event->groupId;
                    $post['userId']    = $post['userId'] ? $post['userId'] : null;
                    $post['set']       = $data['set'];
                    $post['numero']    = (!$stats) ? $data['numero'] : $stats->numero + 1;
                    $stats             = $this->statsTable->save($post);
                }

                $this->redirect()->toRoute('event', ['action' => 'live-stats', 'id' => $eventId]);
            }
            unset($data['positions']['start'], $data['positions']['form-name']);

            $setter = null;
            foreach ($data['positions'] as $userId) {
                if ($users[$userId]->position != \Application\Model\User::POSITION_SETTER) continue;
                $setter = $users[$userId];
                break;
            }

            return new ViewModel([
                'deleteLink'     => $deleteLink,
                'data'           => $data,
                'event'          => $event,
                'user'           => $this->getUser(),
                'users'          => $users,
                'server'         => ($data['positions']) ? $users[$data['positions']['p1']] : null,
                'scoreUs'        => $data['scoreUs'],
                'scoreThem'      => $data['scoreThem'],
                'positions'      => $data['positions'],
                'numero'         => $data['numero'],
                'set'            => $data['set'],
                'start'          => $data['start'],
                'setter'         => $setter,
            ]);
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
    }

    public function liveAction()
    {
        $eventId   = $this->params('id');
        $eventName = $this->params('name');
        $event     = $this->eventTable->find($eventId);
        // if ($event && Service\Strings::toSlug($event->name) == $eventName) {
        if ($event) {
            $users      = $this->userTable->getAllByEventId($event->id);

            $config     = $this->get('config');
            $baseUrl    = $config['baseUrl'];

            $stats = $this->statsTable->fetchAll(['eventId' => $eventId], 'id DESC');
            $games = $this->gameTable->fetchAll(['eventId' => $eventId]);

            $result = [];
            foreach ($stats as $stat) {
                $result[$stat->set][$stat->numero]['ending'] = $stat;
                $result[$stat->set][$stat->numero]['during'] = [];
                foreach ($games as $key => $game) {
                    if ($game->numero != $stat->numero) continue;
                    $result[$stat->set][$stat->numero]['during'][] = $game;
                }
            }
            $view = new ViewModel([
                'result' => $result,
                'users' => $users, 
            ]);
            $view->setTerminal(true);
            
            return $view;
        } else {
            $this->redirect()->toRoute('home');
        }
    }
}
