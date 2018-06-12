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
        $groupId        = $this->params('id', null);

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

            $setsHistory     = $this->statsTable->getSetsHistory($eventId);
            $setsStats       = $this->statsTable->getSetsStats($eventId);
            $overallStats    = $this->statsTable->getOverallStats($eventId);
            $setsLastScore   = $this->statsTable->setsLastScore($eventId);
            $efficiencyStats = $this->statsTable->getEfficiencyStats($eventId);
            $faultStats      = $this->statsTable->getFaultStats($eventId);
            $defenceStats    = $this->statsTable->getDefenceStats($eventId);
            $zoneRepartitionStats = $this->statsTable->getZoneRepartitionStats($eventId);

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

            $availability = [
                Model\Disponibility::RESP_NO_ANSWER => [],
                Model\Disponibility::RESP_OK        => [],
                Model\Disponibility::RESP_NO        => [],
                Model\Disponibility::RESP_UNCERTAIN => [],
            ];

            foreach ($disponibilities as $disponibility) {
                $users[$disponibility->userId] = $this->userTable->find($disponibility->userId);
                $availability[$disponibility->response][] = $users[$disponibility->userId];
            }

            $result = [];
            foreach ($comments as $comment) {
                if (!isset($users[$comment->userId])) {
                    $author = $this->userTable->find($comment->userId);
                } else {
                    $author = $users[$comment->userId];
                }

                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $comment->date);
                $result[$comment->id]['date']    = $date->format('d F Y \à H:i');
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

            return new ViewModel([
                'overallStats'    => $overallStats,
                'setsLastScore'   => $setsLastScore,
                'defenceStats'    => $defenceStats,
                'zoneRepartitionStats' => $zoneRepartitionStats,
                'setsStats'       => $setsStats,
                'faultStats'      => $faultStats,
                'efficiencyStats' => $efficiencyStats,
                'setsHistory'     => $setsHistory,
                'counters'        => $counters,
                'comments'        => $result,
                'event'           => $event,
                'form'            => $form,
                'group'           => $group,
                'users'           => $availability,
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
            $this->flashMessenger()->addSuccessMessage('Point supprimé.');
        }
        $this->redirect()->toUrl('/event/live-stats/' . $eventId);
    }

    public function liveStatsAction()
    {
        $eventId = $this->params('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {

            $config     = $this->get('config');
            $stats      = $this->statsTable->fetchOne(['eventId' => $eventId], 'id DESC');
            $scoreUs    = 0;
            $scoreThem  = 0;
            $set        = 1;
            $deleteLink = null;
            if ($stats) {
                if (($stats->scoreUs >= 25 || $stats->scoreThem >= 25) && (abs(
                $stats->scoreThem - $stats->scoreUs) >= 2)) {
                    $set = $stats->set + 1;
                } else {
                    $set = $stats->set;
                    $scoreUs   = $stats->scoreUs;
                    $scoreThem = $stats->scoreThem;
                }
                $deleteLink = $config['baseUrl'] . '/event/delete-stats/' . $stats->id;
            }

            $setsHistory     = $this->statsTable->getSetsHistory($eventId);
            $setsStats       = $this->statsTable->getSetsStats($eventId);
            $overallStats    = $this->statsTable->getOverallStats($eventId);
            $setsLastScore   = $this->statsTable->setsLastScore($eventId);
            $efficiencyStats = $this->statsTable->getEfficiencyStats($eventId);
            $faultStats      = $this->statsTable->getFaultStats($eventId);
            $defenceStats    = $this->statsTable->getDefenceStats($eventId);
            $zoneRepartitionStats = $this->statsTable->getZoneRepartitionStats($eventId);

            $request = $this->getRequest();
            if ($request->isPost()) {
                $result = [];
                $post = $request->getPost()->toArray();
                if (isset($post['post']) && isset($post['post'])) {
                    $reason = $post['post'] . $post['zone'];
                } else {
                    $reason = $post['reason'];
                }
                if ($post['point-for'] == Model\Stats::POINT_US) {
                    $post['score-us']++;
                } else {
                    $post['score-them']++;
                }

                $data['scoreUs']     = $post['score-us'];
                $data['scoreThem']   = $post['score-them'];
                $data['pointFor']    = $post['point-for'];
                $data['reason']      = $reason;
                $data['eventId']     = $eventId;
                $data['set']         = $set;
                $data['blockUs']     = isset($post['block-us']) ? $post['block-us'] : 0;
                $data['blockThem']   = isset($post['block-them']) ? $post['block-them'] : 0;
                $data['defenceUs']   = isset($post['defence-us']) ? $post['defence-us'] : 0;
                $data['defenceThem'] = isset($post['defence-them']) ? $post['defence-them'] : 0;
                $stats = $this->statsTable->save($data);

                $this->flashMessenger()->addSuccessMessage('Point enregistré.');
                $this->redirect()->toRoute('event', ['action' => 'live-stats', 'id' => $eventId]);
            }

            return new ViewModel([
                'deleteLink'    => $deleteLink,
                'setsLastScore' => $setsLastScore,
                'setsHistory'   => $setsHistory,
                'setsStats'     => $setsStats,
                'event'         => $event,
                'user'          => $this->getUser(),
                'scoreUs'       => $scoreUs,
                'scoreThem'     => $scoreThem,
                'set'           => (int) $set,
                'size'          => 6,
                'setsLastScore'   => $setsLastScore,
                'defenceStats'    => $defenceStats,
                'zoneRepartitionStats' => $zoneRepartitionStats,
                'faultStats'      => $faultStats,
                'efficiencyStats' => $efficiencyStats,
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
        if ($event && Service\Strings::toSlug($event->name) == $eventName) {

            $setsHistory   = $this->statsTable->getSetsHistory($eventId);
            $setsLastScore = $this->statsTable->setsLastScore($eventId);

            $config     = $this->get('config');
            $baseUrl    = $config['baseUrl'];

            return new ViewModel([
                'setsHistory'   => $setsHistory,
                'setsLastScore' => $setsLastScore,
                'event'         => $event,
            ]);
        } else {
            $this->redirect()->toRoute('home');
        }
    }
}
