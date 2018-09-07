<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;

class DisponibilityController extends AbstractController
{

    protected $_event    = null;
    protected $_id       = null;
    protected $_isAdmin  = false;
    protected $_isMembre = false;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        if ($this->_id = $this->params('id')) {
            if($this->_event = $this->eventTable->find($this->_id)) {
                $this->_isAdmin  = $this->userGroupTable->isAdmin($this->getUser()->id, $this->_event->groupId);
                $this->_isMember = $this->userGroupTable->isMember($this->getUser()->id, $this->_event->groupId);
            }
        }
        return parent::onDispatch($e);
    }

    public function responseAction()
    {
        $responseId = $this->params('response');
        if ($this->_event && $this->_isMember) {
            $disponibility = $this->disponibilityTable->fetchOne([
                'eventId' => $this->_id,
                'userId'  => $this->getUser()->id,
            ]);
            $group = $this->groupTable->find($this->_event->groupId);
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->_event->date);

            if ($date->format('Ymd') >= date('Ymd')) {
                if ($disponibility->response != $responseId) {
                    $disponibility->response = $responseId;
                    $this->disponibilityTable->save($disponibility);
                }
            }
        }
        $this->redirect()->toUrl('/?eventId=' . $this->_id);
    }
}