<?php

namespace Application\Model;

class Disponibility extends AbstractModel
{
    const RESP_NO_ANSWER = 0;
    const RESP_OK        = 1;
    const RESP_NO        = 2;
    const RESP_INCERTAIN = 3;

    protected $_id       = null;
    protected $_userId   = null;
    protected $_eventId  = null;
    protected $_response = null;
    protected $_groupId  = null;

    public static $literalDisponbility = [
        self::RESP_NO_ANSWER => 'Sans réponse',
        self::RESP_OK        => 'Présent',
        self::RESP_NO        => 'Absent',
        self::RESP_INCERTAIN => 'Incertain',
    ];
}