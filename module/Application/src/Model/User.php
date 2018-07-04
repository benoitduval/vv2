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
    protected $_display     = null;
    protected $_phone       = null;
    protected $_licence     = null;
    protected $_position    = null;

    public static $position = [
        self::POSITION_SETTER         => 'setter',
        self::POSITION_OPPOSITE       => 'opposite',
        self::POSITION_MIDDLE_BLOCKER => 'middle-blocker',
        self::POSITION_OUTSIDE_HITTER => 'outside-hitter',
        self::POSITION_LIBERO         => 'libero',
        self::POSITION_OTHER          => 'other',
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
        return '/img/avatars/' . md5($this->getFullname() . $this->id) . '.png';
    }
}