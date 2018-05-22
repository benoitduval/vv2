<?php

namespace Application\Model;

use Application\Service\Date as Date;

class Comment extends AbstractModel
{
    protected $_id      = null;
    protected $_userId  = null;
    protected $_eventId = null;
    protected $_comment = null;
    protected $_date    = null;
}
