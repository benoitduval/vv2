<?php

namespace Application\Service;

use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;
use Interop\Container\ContainerInterface;
use Application\TableGateway;
use Application\Model;
use Application\Service\Storage\Cookie as CookieStorage;

class AuthenticationService extends \Zend\Authentication\AuthenticationService
{
    protected $_container;
    const NOT_CONFIRMED = -5;

    public function __construct(ContainerInterface $container, Adapter $dbAdapter)
    {
        $this->_container = $container;
        $storage = new CallbackCheckAdapter($dbAdapter, 'user', 'email', 'password', array($this, 'callBack'));
        parent::__construct(new CookieStorage($container), $storage);
    }

    public function getIdentity()
    {
        $identity  = parent::getIdentity();
        $userTable = $this->_container->get(TableGateway\User::class);
        $user      = $userTable->fetchOne(['email' => $identity]);
        return $user;
    }

    public function authenticate($adapter = null)
    {
        $result = $this->getAdapter()->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $user = $this->getAdapter()->getResultRowObject();
            if ($user->status == Model\User::HAS_TO_CONFIRM) {
                $result = new Result(self::NOT_CONFIRMED, $user->email, ['Votre compte n\'as pas encore été confirmé']);
            } else {
                $this->getStorage()->write($user);
            }
        }

        return $result;
    }

    public function callBack($hash, $password)
    {
        $bCrypt = new Bcrypt();
        return $bCrypt->verify(md5($password), $hash);
    }
}