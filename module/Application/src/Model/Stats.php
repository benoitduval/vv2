<?php

namespace Application\Model;

class Stats extends AbstractModel
{
    const POINT_US            = 1;
    const POINT_THEM          = 2;

    const SERVICE            = 1;
    const RECEPTION          = 2;

    const POINT_SERVE         = 1;
    const POINT_ATTACK        = 2;
    const POINT_BLOCK         = 3;
    const FAULT_SERVE         = 4;
    const FAULT_ATTACK        = 5;
    const FAULT_DEFENCE       = 6;

    const DURING_BLOCK        = 7;
    const DURING_DEFENCE      = 8;

    const POST_2              = 1;
    const POST_FIX            = 2;
    const POST_3M             = 3;
    const POST_SETTER         = 4;
    const POST_4              = 5;
    const POST_OTHER          = 6;

    const SMALL_DIAG          = 1;
    const LARGE_DIAG          = 2;
    const BLOCK_OUT           = 3;
    const BIDOUILLE           = 4;
    const LINE                = 5;
    const DECA                = 6;
    const FIX                 = 7;
    const BEHIND              = 8;
    const SET_ATTACK          = 9;

    const FROM_P1           = 1;
    const FROM_P2           = 2;
    const FROM_P3           = 3;
    const FROM_P4           = 4;
    const FROM_P5           = 5;
    const FROM_P6           = 6;

    const TO_P1             = 1;
    const TO_P2             = 2;
    const TO_P3             = 3;
    const TO_P4             = 4;
    const TO_P5             = 5;
    const TO_P6             = 6;

    protected $_id          = null;
    protected $_eventId     = null;
    protected $_scoreUs     = null;
    protected $_scoreThem   = null;
    protected $_pointFor    = null;
    protected $_set         = null;
    protected $_reason      = null;
    protected $_userId      = null;
    protected $_fromZone    = null;
    protected $_toZone      = null;
    protected $_groupId     = null;
    protected $_numero      = null;
    protected $_p1          = null;
    protected $_p2          = null;
    protected $_p3          = null;
    protected $_p4          = null;
    protected $_p5          = null;
    protected $_p6          = null;
    protected $_libero      = null;
    protected $_start       = null;

    public static $attackUs = [
        self::POINT_ATTACK,
        self::POINT_ATTACK . self::POST_4 . self::LINE,
        self::POINT_ATTACK . self::POST_4 . self::SMALL_DIAG,
        self::POINT_ATTACK . self::POST_4 . self::LARGE_DIAG,
        self::POINT_ATTACK . self::POST_4 . self::BLOCK_OUT,
        self::POINT_ATTACK . self::POST_4 . self::BIDOUILLE,
        self::POINT_ATTACK . self::POST_2 . self::LINE,
        self::POINT_ATTACK . self::POST_2 . self::SMALL_DIAG,
        self::POINT_ATTACK . self::POST_2 . self::LARGE_DIAG,
        self::POINT_ATTACK . self::POST_2 . self::BLOCK_OUT,
        self::POINT_ATTACK . self::POST_2 . self::BIDOUILLE,
        self::POINT_ATTACK . self::POST_FIX . self::FIX,
        self::POINT_ATTACK . self::POST_FIX . self::DECA,
        self::POINT_ATTACK . self::POST_FIX . self::BEHIND,
        self::POINT_ATTACK . self::POST_SETTER . self::BIDOUILLE,
        self::POINT_ATTACK . self::POST_SETTER . self::SET_ATTACK,
        self::POINT_ATTACK . self::POST_3M . self::LINE,
        self::POINT_ATTACK . self::POST_3M . self::SMALL_DIAG,
        self::POINT_ATTACK . self::POST_3M . self::LARGE_DIAG,
        self::POINT_ATTACK . self::POST_3M . self::BLOCK_OUT,
        self::POINT_ATTACK . self::POST_3M . self::BIDOUILLE,
    ];

    public static $faultUs = [
        self::FAULT_ATTACK,
        self::FAULT_ATTACK . self::POST_4,
        self::FAULT_ATTACK . self::POST_2,
        self::FAULT_ATTACK . self::POST_FIX,
        self::FAULT_ATTACK . self::POST_SETTER,
        self::FAULT_ATTACK . self::POST_3M,
    ];

    public static $reason = [
        self::FAULT_DEFENCE => 'Faute Défensive',
        self::POINT_BLOCK => 'Point au block',
        self::FAULT_ATTACK => 'Faute à l\'attaque',
        self::FAULT_ATTACK . self::POST_4 => 'Faute à l\'attaque (R4)',
        self::FAULT_ATTACK . self::POST_2 => 'Faute à l\'attaque (Pointe)',
        self::FAULT_ATTACK . self::POST_FIX => 'Faute à l\'attaque (Centre)',
        self::FAULT_ATTACK . self::POST_SETTER => 'Faute à l\'attaque (Passe)',
        self::FAULT_ATTACK . self::POST_3M => 'Faute à l\'attaque (3m)',
        self::POINT_ATTACK => 'Point à l\'attaque',
        self::POINT_ATTACK . self::POST_4 . self::LINE => 'Point à l\'attaque (R4 - Ligne)',
        self::POINT_ATTACK . self::POST_4 . self::SMALL_DIAG => 'Point à l\'attaque (R4 - Petite Diag.)',
        self::POINT_ATTACK . self::POST_4 . self::LARGE_DIAG => 'Point à l\'attaque (R4 - Grande Diag.)',
        self::POINT_ATTACK . self::POST_4 . self::BLOCK_OUT => 'Point à l\'attaque (R4 - Block Out)',
        self::POINT_ATTACK . self::POST_4 . self::BIDOUILLE => 'Point à l\'attaque (R4 - Bidouille)',
        self::POINT_ATTACK . self::POST_2 . self::LINE => 'Point à l\'attaque (Pointe - Ligne)',
        self::POINT_ATTACK . self::POST_2 . self::SMALL_DIAG => 'Point à l\'attaque (Pointe - Petite Diag.)',
        self::POINT_ATTACK . self::POST_2 . self::LARGE_DIAG => 'Point à l\'attaque (Pointe - Grande Diag.)',
        self::POINT_ATTACK . self::POST_2 . self::BLOCK_OUT => 'Point à l\'attaque (Pointe - Block Out)',
        self::POINT_ATTACK . self::POST_2 . self::BIDOUILLE => 'Point à l\'attaque (Pointe - Bidouille)',
        self::POINT_ATTACK . self::POST_FIX . self::FIX => 'Point à l\'attaque (Centre - Fix)',
        self::POINT_ATTACK . self::POST_FIX . self::DECA => 'Point à l\'attaque (Centre - Déca.)',
        self::POINT_ATTACK . self::POST_FIX . self::BEHIND => 'Point à l\'attaque (Centre - Arrière)',
        self::POINT_ATTACK . self::POST_SETTER . self::BIDOUILLE => 'Point à l\'attaque (Passeur - Bidouille)',
        self::POINT_ATTACK . self::POST_SETTER . self::SET_ATTACK => 'Point à l\'attaque (Passeur - Attaque)',
        self::POINT_ATTACK . self::POST_3M . self::LINE => 'Point à l\'attaque (3m - Ligne)',
        self::POINT_ATTACK . self::POST_3M . self::SMALL_DIAG => 'Point à l\'attaque (3m - Petite Diag.)',
        self::POINT_ATTACK . self::POST_3M . self::LARGE_DIAG =>'Point à l\'attaque (3m - Grande Diag.)',
        self::POINT_ATTACK . self::POST_3M . self::BLOCK_OUT => 'Point à l\'attaque (3m - Block Out)',
        self::POINT_ATTACK . self::POST_3M . self::BIDOUILLE => 'Point à l\'attaque (3m - Bidouille)',
        self::POINT_SERVE => 'Point au Service',
        self::FAULT_SERVE => 'Faute au Service',
    ];

    public function rotate() {
        $tmp = $this->p1;
        $result['p1'] = $this->p2;
        $result['p2'] = $this->p3;
        $result['p3'] = $this->p4;
        $result['p4'] = $this->p5;
        $result['p5'] = $this->p6;
        $result['libero'] = $this->libero;
        $result['p6'] = $tmp;

        return $result;
    }
}