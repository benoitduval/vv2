<?php

namespace Application\Model;

class Game extends AbstractModel
{

	const SERVICE        = 1;
	const RECEPTION      = 2;
	const DIG            = 3;

	const QUALITY_BAD    = 1;
	const QUALITY_MEDIUM = 2;
	const QUALITY_GOOD   = 3;

    protected $_id      = null;
    protected $_eventId = null;
    protected $_userId  = null;
    protected $_numero  = null;
    protected $_type    = null;
    protected $_quality = null;
}