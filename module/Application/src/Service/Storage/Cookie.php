<?php

namespace Application\Service\Storage;

use Zend\Authentication\Storage\StorageInterface;
use Zend\Crypt\Password\Bcrypt;
use Application\TableGateway;

class Cookie implements StorageInterface
{
    protected $_container;

    public function __construct($container)
    {
        $this->_container = $container;
    }

    /**
     * Returns true if and only if storage is empty.
     *
     * @return boolean
     * @throws \Zend\Authentication\Exception\ExceptionInterface If it is
     *     impossible to determine whether storage is empty.
     */
    public function isEmpty()
    {
        return ($this->read() === null);
    }

    /**
     * Returns the contents of storage.
     *
     * Behavior is undefined when storage is empty.
     *
     * @return mixed
     * @throws \Zend\Authentication\Exception\ExceptionInterface If reading
     *     contents from storage is impossible
     */

    public function read()
    {
        if (empty($_COOKIE['userId']) || empty($_COOKIE['cs'])) return null;
        $userTable = $this->_container->get(TableGateway\User::class);

        if ($user = $userTable->find($_COOKIE['userId'])) {
            $bcrypt = new Bcrypt;
            if ($bcrypt->verify($user->email . $user->password, $_COOKIE['cs'])) {
                return $user->toArray();
            }
        }

        $this->clear();
        return null;
    }

    /**
     * Writes $contents to storage.
     *
     * @param  mixed $email
     * @return void
     * @throws \Zend\Authentication\Exception\ExceptionInterface If writing
     *     $contents to storage is impossible
     */

    public function write($obj)
    {
        $userTable   = $this->_container->get(TableGateway\User::class);
        $user = $userTable->fetchOne(['email' => $obj->email]);

        $bcrypt = new Bcrypt;
        $salt = $bcrypt->create($user->email . $user->password);

        // set authentication cookies (both to browser and current PHP process)
        setcookie('cs', $salt, strtotime('+1 year'), '/');
        $_COOKIE['cs'] = $salt;
        setcookie('userId', $user->id, strtotime('+1 year'), '/');
        $_COOKIE['userId'] = $user->id;
    }

    /**
     * Clears contents from storage.
     *
     * @return void
     * @throws \Zend\Authentication\Exception\ExceptionInterface If clearing
     *     contents from storage is impossible.
     */

    public function clear()
    {
        $cookies = array_keys($_COOKIE);

        // remove all the given cookies
        $pastTime = time() - 42000;
        foreach ((array)$cookies as $cookie) {
            setcookie($cookie, '', $pastTime, '/');
            if (isset($_COOKIE[$cookie])) {
                unset($_COOKIE[$cookie]);
            }
        }
    }
}