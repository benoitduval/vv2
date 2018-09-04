<?php
namespace Application\Model;

use Application\Tablegateway;

class User extends AbstractModel
{

    const DISPLAY_SMALL     = 1;
    const DISPLAY_LARGE     = 2;
    const DISPLAY_TABLE     = 3;

    const POSITION_SETTER   = 1;
    const POSITION_OPPOSITE = 2;
    const POSITION_MIDDLE_BLOCKER = 3;
    const POSITION_OUTSIDE_HITTER = 4;
    const POSITION_LIBERO   = 5;
    const POSITION_OTHER    = 6;

    const HAS_TO_CONFIRM    = 1;
    const CONFIRMED         = 2;

    protected $_id          = null;
    protected $_firstname   = null;
    protected $_lastname    = null;
    protected $_email       = null;
    protected $_password    = null;
    protected $_status      = null;
    protected $_position    = null;
    protected $_numero      = null;

    public static $position = [
        self::POSITION_SETTER         => 'Setter',
        self::POSITION_OPPOSITE       => 'Opposite',
        self::POSITION_MIDDLE_BLOCKER => 'Middle Blocker',
        self::POSITION_OUTSIDE_HITTER => 'Outside Hitter',
        self::POSITION_LIBERO         => 'Libero',
        self::POSITION_OTHER          => 'Coach',
    ];

    public static $colors = [
        self::POSITION_SETTER         => 'info',
        self::POSITION_OPPOSITE       => 'primary',
        self::POSITION_MIDDLE_BLOCKER => 'warning',
        self::POSITION_OUTSIDE_HITTER => 'success',
        self::POSITION_LIBERO         => 'danger',
        self::POSITION_OTHER          => 'default',
    ];

    public function getFullname()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function getFirstname()
    {
        return ucfirst(mb_strtolower($this->_firstname));
    }

    public function getLastname()
    {
        return ucfirst(mb_strtolower($this->_lastname));
    }

    public function getAvatarPath()
    {
        $result = '/img/avatars/default-avatar.png';
        $numero = '/img/avatars/' . $this->numero . '.png';
        if ($this->numero) $result = $numero;
        $avatar = getcwd() . '/public/img/avatars/users/' . md5($this->email . $this->id) . '.png';
        if (file_exists(getcwd() . '/public/img/avatars/users/' . md5($this->email . $this->id) . '.png')) $result = '/img/avatars/users/' . md5($this->email . $this->id) . '.png';
        return $result;
    }
}