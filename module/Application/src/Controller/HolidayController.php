<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Service;

class HolidayController extends AbstractController
{
    public function deleteAction()
    {
        if ($this->getUser()) {
            $id = $this->params('id');
            $holiday = $this->holidayTable->find($id);
            $this->holidayTable->delete($holiday);
            $this->redirect()->toRoute('holiday', ['action' => 'index']);
        } else {
            $this->redirect()->toRoute('home');
        }
    }

    public function indexAction()
    {
        if ($user = $this->getUser()) {

            $holidays = $this->holidayTable->fetchAll([
                'userId' => $user->id,
                '`to` > ?' => date('Y-m-d H:i:s', time())
            ]);

            $form = new Form\Holiday;
            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $formData = $form->getData();
                    $from = \DateTime::createFromFormat('d/m/Y', $formData['from']);
                    $to   = \DateTime::createFromFormat('d/m/Y', $formData['to']);
                    $holiday = $this->holidayTable->save([
                        'from'   => $from->format('Y-m-d 00:00:00'),
                        'to'     => $to->format('Y-m-d 23:59:59'),
                        'userId' => $this->getUser()->id
                    ]);
                }

                $this->_updateEvents($from, $to);
                $this->flashMessenger()->addSuccessMessage('Enregistré');
                $this->redirect()->toRoute('holiday', ['action' => 'index']);
            }

            return new ViewModel([
                'form'     => $form,
                'holidays' => $holidays,
                'user'     => $this->getUser(),
            ]);

        } else {
            $this->redirect()->toRoute('home');
        }
    }

    public function editAction()
    {
        $holidayId = $this->params('id');
        if ($this->getUser()) {
            $holiday = $this->holidayTable->find($holidayId);

            $form = new Form\Holiday;
            $from = \DateTime::createFromFormat('Y-m-d H:i:s', $holiday->from);
            $to   = \DateTime::createFromFormat('Y-m-d H:i:s', $holiday->to);
            $data = [
                'from' => $from->format('d/m/Y'),
                'to' => $to->format('d/m/Y'),
                'userId' => $holiday->userId,
            ];

            $form->setData($data);
            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $formData = $form->getData();
                    $from = \DateTime::createFromFormat('d/m/Y', $formData['from']);
                    $to   = \DateTime::createFromFormat('d/m/Y', $formData['to']);

                    $this->holidayTable->save([
                        'id'     => $holidayId,
                        'from'   => $from->format('Y-m-d 00:00:00'),
                        'to'     => $to->format('Y-m-d 23:59:59'),
                        'userId' => $this->getUser()->id
                    ]);
                }

                $this->_updateEvents($from, $to);
                $this->redirect()->toRoute('holiday', ['action' => 'index']);
            }

            return new ViewModel([
                'form'   => $form,
                'user'   => $this->getUser(),
            ]);
        } else {
            $this->flashMessenger()->addErrorMessage('Vous ne pouvez pas accéder à cette page, vous avez été redirigé sur votre page d\'accueil');
            $this->redirect()->toRoute('home');
        }
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