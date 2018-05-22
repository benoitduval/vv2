<?php
namespace Application\Model;

class Training extends AbstractModel
{
    protected $_id       = null;
    protected $_name     = null;
    protected $_groupId  = null;
    protected $_eventDay = null;
    protected $_time     = null;
    protected $_emailDay = null;
    protected $_status   = null;
    protected $_place    = null;
    protected $_address  = null;
    protected $_city     = null;
    protected $_zipCode  = null;

    const ACTIVE   = 1;
    const INACTIVE = 2;
}
