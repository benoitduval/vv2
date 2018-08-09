<?php

namespace Application\Model;

class Game extends AbstractModel
{

	const SERVICE        = 1;
	const RECEPTION      = 2;
    const DIG            = 3;
	const SET            = 4;

    protected $_id      = null;
    protected $_eventId = null;
    protected $_userId  = null;
    protected $_numero  = null;
    protected $_type    = null;
    protected $_quality = null;

    public static $literalReason = [
        self::SERVICE   => 'Service',
        self::RECEPTION => 'Reception',
        self::DIG       => 'Dig',
        self::SET   	=> 'Set',
    ];
}