<?php

namespace Application\Model;

class Join extends AbstractModel
{
    protected $_id          = null;
    protected $_userId      = null;
    protected $_groupId     = null;
    protected $_response    = null;

    const RESPONSE_WAITING = 1;
    const RESPONSE_REFUSED = 2;

}
