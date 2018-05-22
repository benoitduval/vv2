<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Service;
use Application\TableGateway;
use Application\Service\MailService;

class MatchController extends AbstractController
{
    public function createAction()
    {
        $eventId        = $this->params('id');
        $eventTable     = $this->get(TableGateway\Event::class);
        $userGroupTable = $this->get(TableGateway\UserGroup::class);

        if (($event = $eventTable->find($eventId)) && $userGroupTable->isAdmin($this->getUser()->id, $event->groupId)) {

            $matchTable = $this->get(TableGateway\Match::class);
            $match = $matchTable->fetchOne(['eventId' => $event->id]);
            if (!$match) $match = new Model\Match;

            $form = new Form\Match;
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post  = $request->getPost();
                $form->setData($post);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $setFor     = 0;
                    $setAgainst = 0;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($data['set'.$i.'Team1'] && $data['set'.$i.'Team2']) {
                            if ($data['set'.$i.'Team1'] > $data['set'.$i.'Team2']) {
                                $setFor++;
                            } else {
                                $setAgainst++;
                            }
                        }

                        $data['victory'] = ($setFor > $setAgainst) ? 1 : 0;
                        $data['sets']    = $setFor . ' / ' .  $setAgainst;
                        $data['eventId'] = $eventId;
                    }

                    $match->exchangeArray($data);
                    $matchTable->save($match);

                    $this->flashMessenger()->addMessage('Votre match a bien été enregistré.');
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

    public function editAction()
    {
        $eventId    = $this->params('id');
        $matchTable = $this->get(TableGateway\Match::class);
        $userGroupTable = $this->get(TableGateway\UserGroup::class);
        $match      = $matchTable->fetchOne(['eventId' => $eventId]);

        $eventTable = $this->get(TableGateway\Event::class);
        if ($match && ($event = $eventTable->find($eventId)) && $userGroupTable->isAdmin($this->getUser()->id, $event->groupId)) {

            $form = new Form\Match;
            $request = $this->getRequest();
            $form->setData($match->toArray());
            if ($request->isPost()) {
                $post  = $request->getPost();
                $form->setData($post);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $setFor     = 0;
                    $setAgainst = 0;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($data['set'.$i.'Team1'] && $data['set'.$i.'Team2']) {
                            if ($data['set'.$i.'Team1'] > $data['set'.$i.'Team2']) {
                                $setFor++;
                            } else {
                                $setAgainst++;
                            }
                        }

                        $data['victory'] = ($setFor > $setAgainst) ? 1 : 0;
                        $data['sets']    = $setFor . ' / ' .  $setAgainst;
                        $data['eventId'] = $event->id;
                    }
                    $data['id'] = $match->id;
                    $match->exchangeArray($data);
                    $matchTable->save($match);

                    $this->flashMessenger()->addMessage('Votre match a bien été enregistré.');
                    $this->redirect()->toRoute('event', ['action' => detail, 'id' => $event->id]);
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
}