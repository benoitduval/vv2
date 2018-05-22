<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;
use Application\Service\MailService;

class CommentController extends AbstractController
{

    public function indexAction()
    {
        $eventId  = $this->params('eventId', null);
        $groupId  = $this->params('groupId', null);
        $comment  = $this->params()->fromPost('comment');
        $isMember = false;

        if ($this->getUser() && $comment) {
            $commentTable   = $this->get(TableGateway\Comment::class);
            $groupTable     = $this->get(TableGateway\Group::class);
            $eventTable     = $this->get(TableGateway\Event::class);
            $userGroupTable = $this->get(TableGateway\UserGroup::class);
            $userTable      = $this->get(TableGateway\User::class);
            $notifTable     = $this->get(TableGateway\Notification::class);
            $guestTable     = $this->get(TableGateway\Guest::class);
            if ($group = $groupTable->find($groupId)) {
                $isMember = $userGroupTable->isMember($this->getUser()->id, $groupId);
            }
        } else {
            $data = ['result' => ['success' => false]];
        }

        if ($isMember) {
            $event = $eventTable->find($eventId);
            $eventDate   = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);
            $comment = $commentTable->save([
                'date'    => date('Y-m-d H:i:s'),
                'eventId' => $eventId,
                'userId'  => $this->getUser()->id,
                'comment' => $comment,
            ]);

            $config = $this->get('config');
            if ($config['mail']['allowed']) {
                $users = $userTable->getAllByGroupId($groupId);
                $bcc   = [];
                foreach ($users as $user) {
                    $email = true;
                    $guest = $guestTable->fetchOne(['userId' => $user->id, 'eventId' => $event->id]);
                    if ($guest && $guest->response = Model\Guest::RESP_NO && !$notifTable->isAllowed(Model\Notification::COMMENT_ABSENT, $user->id)) {
                        $email = false;
                    } else if ($this->getUser()->id == $user->id && !$notifTable->isAllowed(Model\Notification::SELF_COMMENT, $user->id)) {
                        $email = false;
                    } else if (!$notifTable->isAllowed(Model\Notification::COMMENTS, $user->id)) {
                        $email = false;
                    }

                    if ($email) $bcc[] = $user->email;
                }

                $commentDate = \DateTime::createFromFormat('U', time());
                $mail        = $this->get(MailService::class);
                $mail->addBcc($bcc);
                $mail->setSubject('[' . $group->name . '] ' . $event->name . ' - ' . $eventDate->format('l d F \à H\hi'));
                $mail->setTemplate(MailService::TEMPLATE_COMMENT, array(
                    'title'     => $event->name . '<br>' . $eventDate->format('l d F \à H\hi'),
                    'subtitle'  => $group->name,
                    'username'  => $this->getUser()->getFullname(),
                    'comment'   => nl2br($comment->comment),
                    'date'      => $commentDate->format('d\/m'),
                    'eventId'   => $eventId,
                    'baseUrl'   => $config['baseUrl']

                ));
                $mail->send();
            }
        } else {
            $data = ['result' => ['success' => false]];
        }
        if (!isset($data)) {
            $data = ['result' => ['success' => true, 'user' => $this->getUser()->getFullname(), 'date' => \Application\Service\Date::toFr($commentDate->format('d F Y \à H:i'))]];
        }
        $view = new ViewModel($data);

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}