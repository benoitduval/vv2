<?php

namespace Application\Model;

class Notification extends AbstractModel
{

    const EVENT_SIMPLE       = 1;
    const EVENT_RECURENT     = 2;
    const EVENT_UPDATE       = 3;
    const COMMENTS           = 4;
    const REMINDER           = 5;
    const COMMENT_ABSENT     = 6;
    const SELF_COMMENT       = 7;

    CONST ACTIVE             = 1;
    CONST DISABLED           = 2;

    public static $labels = [
        self::EVENT_SIMPLE   => 'La création d\'évènement',
        self::EVENT_RECURENT => 'La création d\'entrainement',
        self::EVENT_UPDATE   => 'Les modifications d\'évènement',
        self::REMINDER       => 'Les rappels d\'évènement.',
        self::COMMENT_ABSENT => 'Les commentaires étant absent à l\'évènement.',
        self::SELF_COMMENT   => 'Mes propres commentaires.',
        self::COMMENTS       => 'Les commentaires.',
    ];

    protected $_id           = null;
    protected $_userId       = null;
    protected $_notification = null;
    protected $_status       = null;

    public function getLabel()
    {
        return self::$labels[$this->_notification];
    }
}
