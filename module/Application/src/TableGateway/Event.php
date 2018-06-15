<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\TableGateway;
use Application\Service;
use Application\Model;

class Event extends AbstractTableGateway
{
    public function getAllByGroupId($groupId, $from = null, $to = null)
    {
        $season = Service\Date::getSeasonsDates();
        if (!$from) $from = $season['from']->format('Y-m-d H:i:s');
        if (!$to) $to = $season['to']->format('Y-m-d H:i:s');
        $events = $this->fetchAll([
            'groupId' => $groupId,
            'date >= ?' => $from,
            'date <= ?' => $to,
        ]);
        return $events;
    }

    public function getGamesByGroupId($groupId)
    {
        $season = Service\Date::getSeasonsDates();
        $games  = $this->fetchAll([
            'groupId' =>  $groupId,
            'date > ?' => $season['from']->format('Y-m-d H:i:s'),
            'date < ?' => $season['to']->format('Y-m-d H:i:s'),
            'victory' => [0, 1],
        ]);

        $disponibilityTable = $this->getContainer()->get(TableGateway\Disponibility::class);
        foreach ($games as $game) {
            $presentUsers = $disponibilityTable->fetchAll(['eventId' => $game->id, 'response' => Model\Disponibility::RESP_OK]);
            $present = [];
            foreach ($presentUsers as $user) {
                $present[] = $user->userId;
            }
            $result[$game->id] = [
                'event'   => $game,
                'userIds' => $present,
            ];
        }
        return $result;
    }

    public function getAllByUserId($userId, $start, $end)
    {
        $objs = $this->getContainer()->get(TableGateway\Disponibility::class)->fetchAll([
            'userId' => $userId
        ]);

        $events = [];
        if ($objs->toArray()) {
            $ids = [];
            foreach ($objs as $obj) {
                $ids[] = $obj->eventId;
            }
            $events = $this->fetchAll([
                'id' => $ids,
                'date >= ?' => $start,
                'date <= ?' => $end,
            ]);
        }
        return $events;
    }

    public function getActiveByUserId($userId)
    {
        $objs = $this->getContainer()->get(TableGateway\Disponibility::class)->fetchAll([
            'userId' => $userId
        ]);

        $events = [];
        $today = new \DateTime('today midnight');
        if ($objs->toArray()) {
            $ids = [];
            foreach ($objs as $obj) {
                $ids[] = $obj->eventId;
            }
            $events = $this->fetchAll([
                'id' => $ids,
                'date >= ?' => $today->format('Y-m-d H:i:s')
            ], 'date ASC');
        }
        return $events;
    }
}