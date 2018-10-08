<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\TableGateway;
use Application\Model\Stats as Statistics;

class Stats extends AbstractTableGateway
{

    public function getAllByEventId($eventId)
    {
        $key = 'event.stats.' . $eventId;
        $memcached = $this->getContainer()->get('memcached');
        $result = $memcached->getItem($key);
        if ($result === null) {
            $result = [];
            if (($this->fetchOne(['eventId' => $eventId]))) {
                $result = [
                    'ratio'      => $this->getRatio($eventId),
                    'compare'    => $this->getCompare($eventId),
                    'efficiency' => $this->getEfficiency($eventId),
                    'zone'       => $this->getZoneRepartition($eventId),
                    'defence'    => $this->getDefence($eventId),
                    'fault'      => $this->getFault($eventId),
                    'history'    => $this->getSetsHistory($eventId),
                ];
            }
            $memcached->setItem($key, $result);
        }
        return $result;
    }

    public function getMatchData($stat)
    {
        $positions = [];
        if (($stat->scoreUs >= 25 || $stat->scoreThem >= 25) && (abs(
        $stat->scoreThem - $stat->scoreUs) >= 2)) {
            $set = ($stat->set) ? $stat->set + 1 : 1;
            $scoreUs   = 0;
            $scoreThem = 0;
            // get First Point
            if ($set != 5) {            
                $memcached = $this->getContainer()->get('memcached');
                $key = 'position.' . $stat->eventId . '.numero.1';
                if ($startPos = $memcached->getItem($key)) {
                    $startWith = $startPos['start'];
                } else {
                    $data = $this->fetchOne(['eventId' => $stat->eventId, 'numero' => 1], 'id DESC');
                    $startWith = $data->start;
                }

                if ($set % 2) {
                    $start = $startWith;
                } else {
                    $start = ($startWith == \Application\Model\Game::RECEPTION) ? \Application\Model\Game::SERVICE : \Application\Model\Game::RECEPTION;
                }
                $startPos  = ($start == \Application\Model\Game::SERVICE) ? 'p1' : 'p2';
                $userIds = [
                    $stat->p1,
                    $stat->p2,
                    $stat->p3,
                    $stat->p4,
                    $stat->p5,
                    $stat->p6,
                ];
                $userTable = $this->getContainer()->get(TableGateway\User::class);
                $users  = $userTable->fetchAll(['id' => $userIds]);
                $setter = null;
                foreach ($users as $user) {
                    if ($user->position != \Application\Model\User::POSITION_SETTER) continue;
                    $setter = $user->id;
                    break;
                }

                if ($setter) $positions = $stat->rotateWhile($setter, $startPos);
            }
        } else {
            $set       = $stat->set;
            $scoreUs   = $stat->scoreUs;
            $scoreThem = $stat->scoreThem;
            $start     = $stat->start;
            if ($stat->pointFor == Statistics::POINT_US) {
                if ($stat->start == \Application\Model\Game::RECEPTION) {
                    $positions = $stat->rotate();
                } else {
                    $positions = $stat->mark();
                }
                $start = \Application\Model\Game::SERVICE;
            } else {
                $positions = $stat->mark();
                $start     = \Application\Model\Game::RECEPTION;
            }
        }

        $numero = $stat->numero;
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

    public function getZonePercent($options)
    {
        $stats = $this->fetchAll($options);

        $result = [
            'allFrom' => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'allTo'   => [ 'toP1' => 0, 'toP2' => 0, 'toP3' => 0, 'toP4' => 0, 'toP5' => 0, 'toP6' => 0, 'toOutNet' => 0, 'toOutLeft' => 0, 'toOutRight' => 0, 'toOutLong' => 0],
            'fromP1'  => ['toP1' => 0,'toP2' => 0,'toP3' => 0,'toP4' => 0,'toP5' => 0,'toP6' => 0, 'toOutNet' => 0, 'toOutLeft' => 0, 'toOutRight' => 0, 'toOutLong' => 0],
            'fromP2'  => ['toP1' => 0,'toP2' => 0,'toP3' => 0,'toP4' => 0,'toP5' => 0,'toP6' => 0, 'toOutNet' => 0, 'toOutLeft' => 0, 'toOutRight' => 0, 'toOutLong' => 0],
            'fromP3'  => ['toP1' => 0,'toP2' => 0,'toP3' => 0,'toP4' => 0,'toP5' => 0,'toP6' => 0, 'toOutNet' => 0, 'toOutLeft' => 0, 'toOutRight' => 0, 'toOutLong' => 0],
            'fromP4'  => ['toP1' => 0,'toP2' => 0,'toP3' => 0,'toP4' => 0,'toP5' => 0,'toP6' => 0, 'toOutNet' => 0, 'toOutLeft' => 0, 'toOutRight' => 0, 'toOutLong' => 0],
            'fromP5'  => ['toP1' => 0,'toP2' => 0,'toP3' => 0,'toP4' => 0,'toP5' => 0,'toP6' => 0, 'toOutNet' => 0, 'toOutLeft' => 0, 'toOutRight' => 0, 'toOutLong' => 0],
            'fromP6'  => ['toP1' => 0,'toP2' => 0,'toP3' => 0,'toP4' => 0,'toP5' => 0,'toP6' => 0, 'toOutNet' => 0, 'toOutLeft' => 0, 'toOutRight' => 0, 'toOutLong' => 0],
            'toP1'    => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toP2'    => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toP3'    => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toP4'    => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toP5'    => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toP6'    => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toOutNet' => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toOutLeft' => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toOutRight' => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
            'toOutLong' => ['fromP1' => 0,'fromP2' => 0,'fromP3' => 0,'fromP4' => 0,'fromP5' => 0,'fromP6' => 0],
        ];

        foreach ($stats as $stat) {
            if (($stat->pointFor == Statistics::POINT_US && $stat->reason == Statistics::FAULT_ATTACK) || ($stat->pointFor == Statistics::POINT_THEM && $stat->reason == Statistics::POINT_ATTACK)) continue;
            switch ($stat->fromZone) {
                case Statistics::FROM_P1:
                    $labelFrom = 'fromP1';
                    break;
                case Statistics::FROM_P2:
                    $labelFrom = 'fromP2';
                    break;
                case Statistics::FROM_P3:
                    $labelFrom = 'fromP3';
                    break;
                case Statistics::FROM_P4:
                    $labelFrom = 'fromP4';
                    break;
                case Statistics::FROM_P5:
                    $labelFrom = 'fromP5';
                    break;
                case Statistics::FROM_P6:
                    $labelFrom = 'fromP6';
                    break;
            }

            switch ($stat->toZone) {
                case Statistics::TO_P1:
                    $labelTo = 'toP1';
                    break;
                case Statistics::TO_P2:
                    $labelTo = 'toP2';
                    break;
                case Statistics::TO_P3:
                    $labelTo = 'toP3';
                    break;
                case Statistics::TO_P4:
                    $labelTo = 'toP4';
                    break;
                case Statistics::TO_P5:
                    $labelTo = 'toP5';
                    break;
                case Statistics::TO_P6:
                    $labelTo = 'toP6';
                    break;
                case Statistics::TO_OUT_NET:
                    $labelTo = 'toOutNet';
                    break;
                case Statistics::TO_OUT_LEFT:
                    $labelTo = 'toOutLeft';
                    break;
                case Statistics::TO_OUT_RIGHT:
                    $labelTo = 'toOutRight';
                    break;
                case Statistics::TO_OUT_LONG:
                    $labelTo = 'toOutLong';
                    break;

            }

            $result['allFrom'][$labelFrom] ++;
            $result['allTo'][$labelTo] ++;

            $result[$labelFrom][$labelTo] ++;
            $result[$labelTo][$labelFrom] ++;
        }

        foreach ($result as $label => $data) {
            $total = array_sum($data);

            $data = array_map(function($hits) use ($total) {
                if (!$total) return $total;
               return (int) floor($hits / $total * 100);
            }, $data);
            $result[$label] = $data;
        }

        return $result;
    }

    public function getRatio($eventId)
    {
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $this->_getRatio($eventId, $i);
        }
        $result = array_filter($result);
        $result['all'] = array_sum($result) / count($result);
        return $result;
    }

    public function getCompare($eventId, $set = null)
    {
        $result = [];
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $this->_getCompare($eventId, $i);
        }
        $result = array_filter($result);

        $result['all']['us'] = [];
        $result['all']['them'] = [];
        foreach ($result as $set => $data) {
            foreach (['us', 'them'] as $target) {
                $result['all'][$target] = array_map(function ($test) {
                    return array_sum(func_get_args());
                }, $result['all'][$target], $data[$target]);
            }
        }
        return $result;
    }

    public function getEfficiency($eventId, $set = null)
    {
        $result = [];
        if ($set) {
            $result[$set] = $this->_getEfficiency($eventId, $set);
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $result[$i] = $this->_getEfficiency($eventId, $i);
                if ($i == 1) {
                    $result['all'] = $result[$i];
                } else {
                    foreach ($result[$i] as $key => $value) {
                        $result['all'][$key] += $value;
                    }
                }
            }
        }
        return $result;
    }

    public function getZoneRepartition($eventId, $set = null)
    {
        $result = [];
        if ($set) {
            $result[$set] = $this->_getZoneRepartition($eventId, $set);
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $result[$i] = $this->_getZoneRepartition($eventId, $i);
                if ($i == 1) {
                    $result['all'] = $result[$i];
                } else {
                    foreach ($result[$i] as $key => $value) {
                        $result['all'][$key] += $value;
                    }
                }
            }
        }
        return $result;
    }

    public function getDefence($eventId, $set = null)
    {
        $result = [];
        if ($set) {
            $result[$set] = $this->_getDefence($eventId, $set);
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $result[$i] = $this->_getDefence($eventId, $i);
                if ($i == 1) {
                    $result['all'] = $result[$i];
                } else {
                    foreach ($result[$i] as $key => $value) {
                        $result['all'][$key] += $value;
                    }
                }
            }
        }
        return $result;
    }

    public function getFault($eventId, $set = null)
    {
        $result = [];
        if ($set) {
            $result[$set] = $this->_getFault($eventId, $set);
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $result[$i] = $this->_getFault($eventId, $i);
                if ($i == 1) {
                    $result['all'] = $result[$i];
                } else {
                    foreach ($result[$i] as $key => $value) {
                        $result['all'][$key] += $value;
                    }
                }
            }
        }
        return $result;
    }

    public function setsLastScore($eventId, $set = null)
    {
        $result = [];
        if ($set) {
            $result[$set] = $this->_setsLastScore($eventId, $set);
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $result[$i] = $this->_setsLastScore($eventId, $i);
                if ($i == 1) {
                    $result['all'] = $result[$i];
                } else {
                    foreach ($result[$i] as $key => $value) {
                        $result['all'][$key] += $value;
                    }
                }
            }
        }
        return $result;
    }


    public function getSetsHistory($eventId, $set = null)
    {
        $result = [];
        if ($set) {
            $result[$set] = $this->_getSetsHistory($eventId, $set);
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $result[$i] = $this->_getSetsHistory($eventId, $i);
            }
        }
        return array_reverse($result, true);
    }

    private function _setsLastScore($eventId, $set)
    {
        $score = [];
        if (!($stat = $this->fetchOne(['eventId' => $eventId, 'set' => $set]))) return $score;
        return [$stat->scoreUs, $stat->scoreThem];
    }

    private function _getSetsHistory($eventId, $set)
    {
        $result = [];
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return $result;
        $stats = $this->fetchAll(['eventId' => $eventId, 'set' => $set], 'id DESC');
        $data  = [];
        foreach ($stats as $stat) {
            $data[] = [
                'id' => $stat->id,
                'us' => $stat->scoreUs,
                'them' => $stat->scoreThem,
                'blockUs' => $stat->blockUs ? $stat->blockUs : '-',
                'defenceUs' => $stat->defenceUs ? $stat->defenceUs : '-',
                'blockThem' => $stat->blockThem ? $stat->blockThem : '-',
                'defenceThem' => $stat->defenceThem ? $stat->defenceThem : '-',
                'reason' => Statistics::$reason[$stat->reason],
                'pointFor' => $stat->pointFor,
            ];
        }
        return $data;
    }

    private function _getQuality($eventId, $set)
    {
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return null;

        $fault = $this->count([
            'eventId'  => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::$faultUs
        ]);

        if (!$fault) return null;

        $attack = $this->count([
            'eventId'  => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::$attackUs,
        ]);
        return (float) sprintf('%0.2f', $attack / $fault);
    }

    private function _getRatio($eventId, $set)
    {
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return null;

        $fault = $this->count([
            'eventId'  => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::$faultUs
        ]);

        if (!$fault) return null;

        $attack = $this->count([
            'eventId'  => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::$attackUs,
        ]);
        return (float) sprintf('%0.2f', $attack / $fault);
    }

    private function _getCompare($eventId, $set)
    {
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return null;

        $defenceFault = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason' => Statistics::FAULT_DEFENCE,
        ]);

        $blockPoint = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason' => Statistics::POINT_BLOCK,
        ]);

        $attackFault = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason' => Statistics::$faultUs
        ]);

        $attackPoint = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason' => Statistics::$attackUs,
        ]);

        $serveFault = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason' => Statistics::FAULT_SERVE,
        ]);

        $servePoint = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason' => Statistics::POINT_SERVE,
        ]);

        $blockUs = $this->sum('blockUs', [
            'eventId'  => $eventId,
            'set'      => $set,
            'blockUs > ?' => 0,
        ]);

        $defenceUs = $this->sum('defenceUs', [
            'eventId'  => $eventId,
            'set'      => $set,
            'defenceUs > ?' => 0,
        ]);

        $totalFaults = $defenceFault + $attackFault + $serveFault;

        $result['us'] = [
            $servePoint,
            $attackPoint,
            $blockPoint,
            $serveFault,
            $attackFault,
            $defenceFault,
            $blockUs,
            $defenceUs,
            $totalFaults,
        ];

        $defenceFault = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason' => Statistics::FAULT_DEFENCE,
        ]);

        $blockPoint = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason' => Statistics::POINT_BLOCK,
        ]);

        $attackFault = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason' => Statistics::FAULT_ATTACK,
        ]);

        $attackPoint = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason' => Statistics::POINT_ATTACK,
        ]);

        $serveFault = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_US,
            'reason' => Statistics::FAULT_SERVE,
        ]);

        $servePoint = $this->count([
            'eventId' => $eventId,
            'set' => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason' => Statistics::POINT_SERVE,
        ]);

        $blockThem = $this->sum('blockThem', [
            'eventId'  => $eventId,
            'set'      => $set,
            'blockThem > ?' => 0,
        ]);

        $defenceThem = $this->sum('defenceThem', [
            'eventId'  => $eventId,
            'set'      => $set,
            'defenceThem > ?' => 0,
        ]);

        $totalFaults = $defenceFault + $attackFault + $serveFault;

        $result['them'] = [
            $servePoint,
            $attackPoint,
            $blockPoint,
            $serveFault,
            $attackFault,
            $defenceFault,
            $blockThem,
            $defenceThem,
            $totalFaults,
        ];

        return $result;
    }

    private function _getEfficiency($eventId, $set)
    {
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return [];

        $attackFault = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::$faultUs
        ]);

        $attackPoint = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::$attackUs,
        ]);

        $blockThem = $this->sum('blockThem', [
            'eventId'  => $eventId,
            'set'      => $set,
            'blockThem > ?' => 0,
        ]);

        $defenceThem = $this->sum('defenceThem', [
            'eventId'  => $eventId,
            'set'      => $set,
            'defenceThem > ?' => 0,
        ]);

        $result = [
            'fault' => $attackFault,
            'point' => $attackPoint,
            'block' => $blockThem,
            'defence' => $defenceThem,
        ];

        return $result;
    }

    private function _getFault($eventId, $set)
    {
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return [];

        $post4Fault = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::FAULT_ATTACK . Statistics::POST_4,
        ]);

        $post2Fault = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::FAULT_ATTACK . Statistics::POST_2,
        ]);

        $postCenterFault = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::FAULT_ATTACK . Statistics::POST_FIX,
        ]);

        $postSetFault = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::FAULT_ATTACK . Statistics::POST_SETTER,
        ]);

        $post3mFault = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_THEM,
            'reason'   => Statistics::FAULT_ATTACK . Statistics::POST_3M,
        ]);

        $result = [
            '4' => $post4Fault,
            '2' => $post2Fault,
            'center' => $postCenterFault,
            '3m' => $post3mFault,
            'setter' => $postSetFault,
        ];

        return $result;
    }

    private function _getZoneRepartition($eventId, $set)
    {
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return [];

        $line4 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_4 . Statistics::LINE,
        ]);

        $line2 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_2 . Statistics::LINE,
        ]);

        $line3m = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_3M . Statistics::LINE,
        ]);

        $bidouille4 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_4 . Statistics::BIDOUILLE,
        ]);

        $bidouille2 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_2 . Statistics::BIDOUILLE,
        ]);

        $bidouille3m = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_3M . Statistics::BIDOUILLE,
        ]);

        $blockOut4 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_4 . Statistics::BLOCK_OUT,
        ]);

        $blockOut2 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_2 . Statistics::BLOCK_OUT,
        ]);

        $blockOut3m = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_3M . Statistics::BLOCK_OUT,
        ]);

        $largeDiag4 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_4 . Statistics::LARGE_DIAG,
        ]);

        $largeDiag2 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_2 . Statistics::LARGE_DIAG,
        ]);

        $largeDiag3m = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_3M . Statistics::LARGE_DIAG,
        ]);

        $smallDiag4 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_4 . Statistics::SMALL_DIAG,
        ]);

        $smallDiag2 = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_2 . Statistics::SMALL_DIAG,
        ]);

        $smallDiag3m = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_3M . Statistics::SMALL_DIAG,
        ]);

        $fixCenter = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_FIX . Statistics::FIX,
        ]);

        $decaCenter = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_FIX . Statistics::DECA,
        ]);

        $behindCenter = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_FIX . Statistics::BEHIND,
        ]);

        $setAttack = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_SETTER . Statistics::SET_ATTACK,
        ]);

        $setBidouille = $this->count([
            'eventId'  => $eventId,
            'set'      => $set,
            'pointFor' => Statistics::POINT_US,
            'reason'   => Statistics::POINT_ATTACK . Statistics::POST_SETTER . Statistics::BIDOUILLE,
        ]);

        $line = $line2 + $line4 + $line3m;
        $largeDiag = $largeDiag3m + $largeDiag2 + $largeDiag4;
        $smallDiag = $smallDiag3m + $smallDiag2 + $smallDiag4;
        $blockOut = $blockOut3m + $blockOut2 + $blockOut4;
        $bidouille = $bidouille3m + $bidouille2 + $bidouille4 + $setBidouille;

        $set = $setAttack + $setBidouille;
        $center = $decaCenter + $fixCenter + $behindCenter;
        $post4 = $largeDiag4 + $smallDiag4 + $line4 + $bidouille4 + $blockOut4;
        $post2 = $largeDiag2 + $smallDiag2 + $line2 + $bidouille2 + $blockOut2;
        $post3m = $largeDiag3m + $smallDiag3m + $line3m + $bidouille3m + $blockOut3m;

        $result = [
            'line'         => $line,
            'line4'        => $line4,
            'line3m'       => $line3m,
            'line2'        => $line2,
            'largeDiag'    => $largeDiag,
            'largeDiag2'   => $largeDiag2,
            'largeDiag4'   => $largeDiag4,
            'largeDiag3m'  => $largeDiag3m,
            'smallDiag'    => $smallDiag,
            'smallDiag2'   => $smallDiag2,
            'smallDiag4'   => $smallDiag4,
            'smallDiag3m'  => $smallDiag3m,
            'bidouille'    => $bidouille,
            'bidouille2'   => $bidouille2,
            'bidouille4'   => $bidouille4,
            'bidouille3m'  => $bidouille3m,
            'blockOut'     => $blockOut,
            'blockOut2'    => $blockOut2,
            'blockOut4'    => $blockOut4,
            'blockOut3m'   => $blockOut3m,
            'center'       => $center,
            'decaCenter'   => $decaCenter,
            'fixCenter'    => $fixCenter,
            'behindCenter' => $behindCenter,
            'set'          => $set,
            'setAttack'    => $setAttack,
            'setBidouille' => $setBidouille,
            'post4'        => $post4,
            'post2'        => $post2,
            'post3m'       => $post3m,
        ];

        return $result;
    }

    private function _getDefence($eventId, $set)
    {
        if (!$this->fetchOne(['eventId' => $eventId, 'set' => $set])) return [];

        $blockThem = $this->sum('blockThem', [
            'eventId'  => $eventId,
            'set'      => $set,
            'blockThem > ?' => 0,
        ]);

        $blockUs = $this->sum('blockUs', [
            'eventId'  => $eventId,
            'set'      => $set,
            'blockUs > ?' => 0,
        ]);

        $defenceThem = $this->sum('defenceThem', [
            'eventId'  => $eventId,
            'set'      => $set,
            'defenceThem > ?' => 0,
        ]);

        $defenceUs = $this->sum('defenceUs', [
            'eventId'  => $eventId,
            'set'      => $set,
            'defenceUs > ?' => 0,
        ]);

        $result = [
            'blockUs'   => $blockUs,
            'blockThem' => $blockThem,
            'defenceUs' => $defenceUs,
            'defenceThem' => $defenceThem,
        ];

        return $result;
    }
}