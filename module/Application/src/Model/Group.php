<?php

namespace Application\Model;

class Group extends AbstractModel
{
    const RESPONSE_OK = 1;
    const RESPONSE_NO = 2;

    protected $_id          = null;
    protected $_name        = null;
    protected $_brand       = null;

    public static function initBrand($name)
    {
        $str = strtolower($name);
        $str = preg_replace('/ /', '-', $str);
        $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
        return $str;
    }

    public function getPublicLink()
    {
        return '/welcome-to/' . $this->brand;
    }

    public function getAvatarPath()
    {
        $result = '/img/default-group-avatar.png';
        $avatar = getcwd() . '/public/img/avatars/groups/' . md5($this->brand . $this->id) . '.png';
        if (file_exists(getcwd() . '/public/img/avatars/groups/' . md5($this->brand . $this->id) . '.png')) $result = '/img/avatars/groups/' . md5($this->brand . $this->id) . '.png';
        return $result;
    }
}