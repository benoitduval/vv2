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
    public function profileAction()
    {
        $user = $this->getUser();
        
        $form = new Form\Profile('uploader', ['entity' => $user->getFullname() . $user->id]);

        $holidays = $this->holidayTable->fetchAll([
            'userId' => $user->id,
            '`to` > ?' => date('Y-m-d H:i:s', time())
        ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost()->toArray();
            if (isset($post['submit-profile'])) {
                unset($post['submit-profile']);
                $post['id'] = $user->id;
                $user = $this->userTable->save($post);
            } else if (isset($post['submit-holiday'])) {
                $from = \DateTime::createFromFormat('Y-m-d', $post['start']);
                $to   = \DateTime::createFromFormat('Y-m-d', $post['end']);
                $holiday = $this->holidayTable->save([
                    'from'   => $from->format('Y-m-d 00:00:00'),
                    'to'     => $to->format('Y-m-d 23:59:59'),
                    'userId' => $user->id
                ]);
                $this->_updateEvents($from, $to);
            }
            $this->redirect()->toUrl('/user/profile');
        }

        $count['total'] = $this->disponibilityTable->count(['userId' => $user->id]);
        $eventsOk = $this->disponibilityTable->fetchAll(['userId' => $user->id, 'response' => \Application\Model\Disponibility::RESP_OK]);

        $count['match'] = 0;
        foreach ($eventsOk as $dispo) {
            if ($this->statsTable->count(['eventId' => $dispo->eventId])) $count['match']++;
        }
        $count['ok'] = floor(count($eventsOk) * 100 / $count['total']);
        $count['no'] = floor($this->disponibilityTable->count(['userId' => $user->id, 'response' => \Application\Model\Disponibility::RESP_NO]) * 100 / $count['total']);

        $this->layout()->user = $this->getUser();

        return new ViewModel([
            'user'  => $this->getUser(),
            'form'  => $form,
            'count' => $count,
            'holidays' => $holidays,
        ]);
    }

    protected function _updateEvents($from, $to)
    {
        // update all event between theses dates
        $groupIds   = [];
        foreach ($this->getUserGroups() as $group) {
            $groupIds[] = $group->id;
        }

        if ($groupIds) {
            $events = $this->eventTable->fetchAll([
                'date > ?' => $from->format('Y-m-d 00:00:00'),
                'date < ?' => $to->format('Y-m-d 23:59:59'),
                'groupId'  => $groupIds
            ]);

            foreach ($events as $event) {
                $disponibility = $this->disponibilityTable->fetchOne([
                    'userId'  => $this->getUser()->id,
                    'eventId' => $event->id,
                    'response <> ?' => Model\Disponibility::RESP_NO
                ]);

                if ($disponibility) {
                    $disponibility->response = Model\Disponibility::RESP_NO;
                    $this->disponibilityTable->save($disponibility);
                }
            }
        }
    }
}