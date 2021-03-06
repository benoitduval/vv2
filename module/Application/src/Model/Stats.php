<?php

namespace Application\Model;

class Stats extends AbstractModel
{
    const POINT_US       = 1;
    const POINT_THEM     = 2;

    const POINT_SERVE    = 1;
    const POINT_ATTACK   = 2;
    const POINT_BLOCK    = 3;
    const FAULT_SERVE    = 4;
    const FAULT_ATTACK   = 5;
    const FAULT_DEFENCE  = 6;

    const FROM_P1        = 1;
    const FROM_P2        = 2;
    const FROM_P3        = 3;
    const FROM_P4        = 4;
    const FROM_P5        = 5;
    const FROM_P6        = 6;

    const TO_P1          = 1;
    const TO_P2          = 2;
    const TO_P3          = 3;
    const TO_P4          = 4;
    const TO_P5          = 5;
    const TO_P6          = 6;
    const TO_OUT_NET     = 7;
    const TO_OUT_LEFT    = 8;
    const TO_OUT_LONG    = 9;
    const TO_OUT_RIGHT   = 10;

    public static $literalReason = [
        self::POINT_SERVE   => 'Ace Serve',
        self::POINT_ATTACK  => 'Attack Point',
        self::POINT_BLOCK   => 'Block Point',
        self::FAULT_SERVE   => 'Service Fault',
        self::FAULT_ATTACK  => 'Attack Fault',
        self::FAULT_DEFENCE => 'Defensive Fault',
    ];

    protected $_id        = null;
    protected $_eventId   = null;
    protected $_scoreUs   = null;
    protected $_scoreThem = null;
    protected $_pointFor  = null;
    protected $_set       = null;
    protected $_reason    = null;
    protected $_userId    = null;
    protected $_fromZone  = null;
    protected $_toZone    = null;
    protected $_groupId   = null;
    protected $_numero    = null;
    protected $_p1        = null;
    protected $_p2        = null;
    protected $_p3        = null;
    protected $_p4        = null;
    protected $_p5        = null;
    protected $_p6        = null;
    protected $_libero    = null;
    protected $_start     = null;

    public function rotateWhile($setterId, $position) {
        if ($this->$position == $setterId) {
            return $this->mark();
        } else {
            for ($i = 0; $i < 5; $i++) { 
                $result = $this->rotate();
                if ($result[$position] == $setterId) return $result;
                $this->p1 = $result['p1'];
                $this->p2 = $result['p2'];
                $this->p3 = $result['p3'];
                $this->p4 = $result['p4'];
                $this->p5 = $result['p5'];
                $this->p6 = $result['p6'];
                $this->libero = $result['libero'];
            }
        }
        return [];
    }

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

    public function mark() {
        $result['p1'] = $this->p1;
        $result['p2'] = $this->p2;
        $result['p3'] = $this->p3;
        $result['p4'] = $this->p4;
        $result['p5'] = $this->p5;
        $result['p6'] = $this->p6;
        $result['libero'] = $this->libero;

        return $result;
    }

    public function getMatchData()
    {
        $positions = [];
        if (($this->scoreUs >= 25 || $this->scoreThem >= 25) && (abs(
        $this->scoreThem - $this->scoreUs) >= 2)) {
            $set = ($this->set) ? $this->set + 1 : 1;
            $scoreUs   = 0;
            $scoreThem = 0;
            // get First Point
            $key = 'position.' . $eventId . '.numero.' . $data['numero'];
            if (!$startPos = $this->get('memcached')->getItem($key)) {

            }
        } else {
            $set       = $this->set;
            $scoreUs   = $this->scoreUs;
            $scoreThem = $this->scoreThem;
            $start     = $this->start;
            if ($this->pointFor == self::POINT_US) {
                if ($this->start == \Application\Model\Game::RECEPTION) {
                    $positions = $this->rotate();
                } else {
                    $positions = $this->mark();
                }
                $start = \Application\Model\Game::SERVICE;
            } else {
                $positions = $this->mark();
                $start     = \Application\Model\Game::RECEPTION;
            }
        }

        $numero = $this->numero;
        asort($positions);

        return [
            'scoreUs'   => $scoreUs,
            'scoreThem' => $scoreThem,
            'set'       => $set,
            'positions' => $positions,
            'numero'    => $numero,
            'start'     => $start,
        ];
    }
}