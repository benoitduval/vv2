<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Service;

class TrainingController extends AbstractController
{
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

    public function deleteAction()
    {
        if ($this->_group && $this->_isAdmin) {
            $trainingId = $this->params('trainingId');
            $training = $this->trainingTable->find($trainingId);
            $this->trainingTable->delete($training);
            $this->redirect()->toRoute('group-welcome', ['brand' => $this->_group->brand]);
        } else {
            $this->redirect()->toRoute('home');
        }
    }

    public function createAction()
    {
        if ($this->_group && $this->_isAdmin) {

            $form = new Form\Training;
            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $data = $form->getData();
                    $data['groupId'] = $this->_group->id;
                    $data['status'] = Model\Training::ACTIVE;
                    $this->trainingTable->save($data);
                }

                $this->redirect()->toRoute('group-welcome', ['brand' => $this->_group->brand]);
            }

            return new ViewModel([
                'group'  => $this->_group,
                'form'   => $form,
                'user'   => $this->getUser(),
                'isAdmin' => true,
            ]);

        } else {
            $this->redirect()->toRoute('home');
        }
    }

    public function editAction()
    {
        $trainingId = $this->params('trainingId');
        if ($this->_group && $this->_isAdmin) {
            $training = $this->trainingTable->find($trainingId);

            $form = new Form\Training;
            $form->setData($training->toArray());
            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $data = $form->getData();
                    $data['groupId'] = $this->_group->id;
                    $data['status'] = Model\Training::ACTIVE;

                    $training->exchangeArray($data);
                    $this->trainingTable->save($training);
                }

                $this->redirect()->toRoute('group-welcome', ['brand' => $this->_group->brand]);
            }

            return new ViewModel([
                'group'  => $this->_group,
                'form'   => $form,
                'user'   => $this->getUser(),
                'isAdmin' => true,
            ]);
        } else {
            $this->redirect()->toRoute('home');
        }
    }
}