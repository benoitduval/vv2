<?php

namespace Application\Model;

class Disponibility extends AbstractModel
{
    const RESP_NO_ANSWER = 0;
    const RESP_OK        = 1;
    const RESP_NO        = 2;
    const RESP_UNCERTAIN = 3;

    const LABEL_RESP_NO_ANSWER = 'noanswer';
    const LABEL_RESP_OK        = 'ok';
    const LABEL_RESP_NO        = 'no';
    const LABEL_RESP_UNCERTAIN = 'uncertain';

    protected $_id       = null;
    protected $_userId   = null;
    protected $_eventId  = null;
    protected $_response = null;
    protected $_groupId  = null;

    public static $literalDisponbility = [
        self::RESP_NO_ANSWER => 'Sans réponse',
        self::RESP_OK        => 'Présent',
        self::RESP_NO        => 'Absent',
        self::RESP_UNCERTAIN => 'Incertain',
    ];
}