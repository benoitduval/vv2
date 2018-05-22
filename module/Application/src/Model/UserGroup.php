<?php

namespace Application\Model;

class UserGroup extends AbstractModel
{
    protected $_id       = null;
    protected $_userId   = null;
    protected $_groupId  = null;
    protected $_admin    = null;

    const MEMBER = 0;
    const ADMIN  = 1;
}