<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Model\Stats as Statistics;
use Application\Model\Game as GameStats;
use Application\Service;
use Application\TableGateway;
use Application\Service\MailService;
use Application\Service\OneSignalService;

class EventController extends AbstractController
{
    public function detailAction()
    {
        $eventId = $this->params('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {
            $users = $this->userTable->getAllByGroupId($event->groupId);

            $comments  = $this->commentTable->fetchAll(['eventId' => $event->id]);
            $group     = $this->groupTable->find($event->groupId);
            $isMember  = $this->userGroupTable->isMember($this->getUser()->id, $group->id);
            $isAdmin   = false;
            if ($isMember) {
                $isAdmin = $this->userGroupTable->isAdmin($this->getUser()->id, $group->id);
            }

            $disponibilities = $this->disponibilityTable->fetchAll(['eventId' => $eventId]);
            $myGuest   = $this->disponibilityTable->fetchOne(['eventId' => $eventId, 'userId' => $this->getUser()->id]);
            $eventDate = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);

            $availabilities = [
                Model\Disponibility::RESP_NO_ANSWER => [],
                Model\Disponibility::RESP_OK        => [],
                Model\Disponibility::RESP_NO        => [],
                Model\Disponibility::RESP_UNCERTAIN => [],
            ];

            foreach ($disponibilities as $disponibility) {
                if (!isset($users[$disponibility->userId])) continue;
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

            $hasStats = $this->statsTable->fetchOne(['eventId' => $eventId]);

            $view = new ViewModel([
                'hasStats'        => $hasStats,
                'counters'        => $counters,
                'comments'        => $result,
                'event'           => $event,
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
            $view->setTerminal(true);
            
            return $view;
        } else {
            $view = new ViewModel([]);
            $view->setTemplate('application/event/not-found.phtml');
            $view->setTerminal(true);
            return $view;
        }
    }

    public function statsAction()
    {
        $eventId = $this->params('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {

            $userId = $this->params()->fromQuery('userId', null);
            $group  = $this->groupTable->find($event->groupId);

            // From Stat
            $attacks     = $this->statsTable->getAttackStats($eventId, $userId);
            $blocks      = $this->statsTable->getBlocks($eventId, $userId);
            $digs        = $this->gameTable->getDigs($eventId, $userId);

            $receptions  = $this->gameTable->getReceptionStats($eventId, $userId);
            $totalDigs   = $this->gameTable->getDigs($eventId);
            $digPercent  = ceil(($digs / $totalDigs) * 100);
            $services    = $this->gameTable->getServiceStats($eventId, $userId);

            $pointCount  = $attacks['kills'] + $blocks + $services['aces'];

            if ($userId) {
                $selected = $this->userTable->find($userId);
                $name = $selected->getFullName();
            } else {
                $selected = $group;
                $name = $group->name;
            }

            $users = $this->userTable->getAllByGroupId($event->groupId);

            $userWithStats = [];
            foreach ($users as $user) {
                if ($this->statsTable->fetchOne(['eventId' => $event->id, 'userId' => $user->id])) {
                    $userWithStats[] = $user->id;
                } else if ($this->gameTable->fetchOne(['eventId' => $event->id, 'userId' => $user->id])) {
                    $userWithStats[] = $user->id;
                }
            }

            $params = [
                'eventId'  => $eventId,
                'pointFor' => Model\Stats::POINT_US,
                'reason'   => Model\Stats::POINT_ATTACK,
            ];
            $stats = $this->statsTable->fetchAll($params);

            $attackScorer = [];
            foreach ($stats as $stat) {
                if (!in_array($stat->userId, $attackScorer) && $stat->userId) $attackScorer[] = $stat->userId;
            }

            $percents = $this->statsTable->getZonePercent([
                'eventId'  => $eventId,
                'reason' => [Model\Stats::POINT_ATTACK, Model\Stats::FAULT_ATTACK]
            ]);
            $compare = $this->statsTable->getCompare($event->id);
            return new ViewModel([
                'receptions'    => $receptions,
                'services'      => $services,
                'selected'      => $selected,
                'digs'          => $digs,
                'totalDigs'     => $totalDigs,
                'digPercent'    => $digPercent,
                'blocks'        => $blocks,
                'attacks'       => $attacks,
                'points'        => $pointCount,
                'userWithStats' => $userWithStats,
                'event'         => $event,
                'users'         => $users,
                'stats'         => $stats,
                'percents'      => $percents,
                'compare'       => $compare,
                'attackScorer'  => $attackScorer,
                'group'         => $group,
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
        $stats   = $this->statsTable->find($statsId);
        if ($stats && ($event = $this->eventTable->find($stats->eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {

            $eventId = $stats->eventId;
            $numero  = $stats->numero;

            $this->statsTable->delete(['id' => $statsId]);
            $key = 'position.' . $eventId . '.numero.' . $stats->numero;
            $this->get('memcached')->removeItem($key);

            $this->gameTable->delete(['eventId' => $eventId, 'numero >= ?' => $numero]);
        }
        $this->redirect()->toUrl('/event/live-stats/' . $eventId);
    }

    public function cancelStatsAction()
    {
        $eventId = $this->params('id', null);
        $numero = $this->params()->fromQuery('numero', null);
        $event = $this->eventTable->find($eventId);
        $gameStats = $this->gameTable->fetchAll(['eventId' => $eventId, 'numero' => $numero]);
        if ($event && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {
            foreach ($gameStats as $gameStat) {
                $this->gameTable->delete(['id' => $gameStat->id]);
            }
        }
        $this->redirect()->toUrl('/event/live-stats/' . $eventId);
    }

    public function liveStatsAction()
    {
        $eventId = $this->params('id');
        if (($event = $this->eventTable->find($eventId)) && $this->userGroupTable->isMember($this->getUser()->id, $event->groupId)) {

            $users      = $this->userTable->getAllByEventId($event->id);
            $group      = $this->groupTable->find($event->groupId);

            $config     = $this->get('config');
            $deleteLink = null;
            $cancelLink = null;
            $data       = [
                'scoreUs'    => 0,
                'scoreThem'  => 0,
                'set'        => 1,
                'numero'     => 1,
                'start'      => null,
                'positions'  => [],
            ];

            if ($stats = $this->statsTable->fetchOne(['eventId' => $eventId], 'id DESC')) {
                $data = $this->statsTable->getMatchData($stats);
                $deleteLink = $config['baseUrl'] . '/event/delete-stats/' . $stats->id;
            }

            // Check for a new position
            $key = 'position.' . $eventId . '.numero.' . $data['numero'];
            if ((!$stats || ($stats && $stats->numero != 1)) && $newPos = $this->get('memcached')->getItem($key)) {
                $data['positions'] = $newPos;
                $data['start'] = (int) $data['positions']['start'];
            }
            $cancelNumero = $data['numero'] + 1;
            $cancelLink = $config['baseUrl'] . '/event/cancel-stats/' . $event->id . '?numero=' . $cancelNumero;

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

                    $post['eventId'] = $eventId;
                    $post['groupId'] = $event->groupId;
                    $post['userId']  = $post['userId'] ? $post['userId'] : null;
                    $post['set']     = $data['set'];
                    $post['numero']  = (!$stats) ? $data['numero'] : $stats->numero + 1;
                    if (!$post['start'] && $data['positions']['start']) $post['start'] = (int) $data['positions']['start'];
                    $stats           = $this->statsTable->save($post);
                }

                $this->redirect()->toRoute('event', ['action' => 'live-stats', 'id' => $eventId]);
            }
            unset($data['positions']['start'], $data['positions']['form-name']);

            $setter = null;
            foreach ($data['positions'] as $userId) {
                if (!isset($users[$userId]) || $users[$userId]->position != \Application\Model\User::POSITION_SETTER) continue;
                $setter = $users[$userId];
                break;
            }

            return new ViewModel([
                'deleteLink'     => $deleteLink,
                'cancelLink'     => $cancelLink,
                'data'           => $data,
                'event'          => $event,
                'group'          => $group,
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
}
