<?php

namespace Application\Controller;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Application\Model;
use Application\Model\Disponibility as Response;
use Application\TableGateway;


class AbstractController extends AbstractActionController
{
    protected $_container;
    protected $_user       = null;
    protected $_userGroups = [];

    public function __construct(ContainerInterface $container, $tables, $user = false)
    {
        $this->_container       = $container;
        $this->_user            = $user;
        $this->_userGroups      = $this->getUserGroups();

        foreach ($tables as $name => $obj) {
            $name .= 'Table';
            $this->$name = $obj;
        }
    }

    protected function _getNotifications()
    {
        $notifications = [];
        foreach ($this->_userGroups as $group) {
            $events = $this->eventTable->getAllByGroupId($group->id, date('Y-m-d H:i:s'));
            foreach ($events as $event) {
                if ($this->disponibilityTable->fetchOne(['eventId' => $event->id, 'userId' => $this->_user->id, 'response' => [Response::RESP_NO_ANSWER, Response::RESP_UNCERTAIN]])) {

                    $notifications[] = [
                        'event' => $event,
                        'group' => $group,
                    ];
                }
            }
        }
        return $notifications;
    }

    public function get($name, $options = [])
    {
        return $this->_container->get($name);
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getUserGroups()
    {
        $groups = [];
        if (empty($this->_userGroups) && $this->_user) {
            $groupTable     = $this->get(TableGateway\Group::class);
            if ($result = $groupTable->getAllByUserId($this->_user->id))
            foreach ($result as $group) {
                $groups[$group->id] = $group;
            }
            $this->_userGroups = $groups;
        }
        return $this->_userGroups;
    }

    public function setActiveUser($user)
    {
        $this->_user = $user;
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $controllerName = $this->params('controller');
        $actionName = $this->params('action');

        $config = $this->get('config');
        $this->layout()->assetsBundle = $controllerName . '::' . $actionName;
        $this->layout()->vCss   = $config['version']['css'];
        $this->layout()->vJs    = $config['version']['js'];
        $this->layout()->user   = $this->getUser();
        $this->layout()->groups = $this->getUserGroups();
        $this->layout()->notifications = $this->_getNotifications();

        return parent::onDispatch($e);
    }
}
