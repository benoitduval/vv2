<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\TableGateway;
use Application\Model;
use Application\Service\Date;

class Group extends AbstractTableGateway
{
    public function getAllByUserId($userId)
    {
        // $key = 'user.groups.' . $userId;
        // $memcached = $this->getContainer()->get('memcached');
        // if (!($groups = $memcached->getItem($key))) {
        $userGroupTable = $this->getContainer()->get(TableGateway\UserGroup::class);
        $objs = $userGroupTable->fetchAll([
            'userId' => $userId
        ]);

        $result = [];
        if ($objs->toArray()) {
            $ids = [];
            foreach ($objs as $obj) {
                $ids[] = $obj->groupId;
            }

            $result = $this->fetchAll([
                'id' => $ids
            ]);
        }

        $groups = [];
        foreach ($result as $group) {
            $groups[$group->id] = $group;
        }
            // $memcached->setItem($key, $groups);
        // }
        return $groups;
    }

    public function getAdmins($groupId)
    {
        $userGroupTable = $this->getContainer()->get(TableGateway\UserGroup::class);
        $objs = $userGroupTable->fetchAll(['groupId' => $groupId, 'admin' => Model\UserGroup::ADMIN]);
        $users = [];

        $userTable = $this->getContainer()->get(TableGateway\User::class);
        if ($objs->toArray()) {
            $ids = [];
            foreach ($objs as $obj) {
                $ids[] = $obj->userId;
            }
            $users = $userTable->fetchAll([
                'id' => $ids
            ]);
        }
        return $users;
    }

    public function getDisponibilities($groupId)
    {
        $eventTable = $this->getContainer()->get(TableGateway\Event::class);
        $disponibilityTable = $this->getContainer()->get(TableGateway\Disponibility::class);
        $result['last'] = $result['current'] = [
            '09' => null,
            '10' => null,
            '11' => null,
            '12' => null,
            '01' => null,
            '02' => null,
            '03' => null,
            '04' => null,
            '05' => null,
            '06' => null,
            '07' => null,
            '08' => null,
        ];

        foreach (Date::getSeasonsDates() as $label => $dates) {
            $eventByMonth = [];

            $events = $eventTable->fetchAll([
                'groupId'  => $groupId,
                'date >= ?' => date('Y-m-d H:i:s', $dates['from']),
                'date <= ?' => date('Y-m-d H:i:s', $dates['to']),
            ], 'date ASC');

            foreach ($events as $event) {
                $eventDate = \Datetime::createFromFormat('Y-m-d H:i:s', $event->date);
                $year  = $eventDate->format('Y');
                $month = $eventDate->format('m');
                if (!isset($eventByMonth[$month])) $eventByMonth[$month] = [];
                $eventByMonth[$month][] = $event->id;
            }

            foreach ($eventByMonth as $month => $eventIds) {
                $count = $disponibilityTable->count([
                    'eventId'  => $eventIds,
                    'response' => \Application\Model\Disponibility::RESP_OK,
                ]);
                if ($count) $count = $count / count($eventIds);
                $result[$label][$month] = $count;
            }
        }

        return $result;
    }

    public function getScoresBySeasons($groupId)
    {
        $scores['last'] = $scores['current'] = [
            '3 / 0' => 0,
            '3 / 1' => 0,
            '3 / 2' => 0,
            '0 / 3' => 0,
            '1 / 3' => 0,
            '2 / 3' => 0,
        ];
        $eventTable = $this->getContainer()->get(TableGateway\Event::class);
        $result     = [];
        foreach (Date::getSeasonsDates() as $label => $dates) {
            $events = $eventTable->fetchAll([
                'groupId'  => $groupId,
                'date > ?' => date('Y-m-d H:i:s', $dates['from']),
                'date < ?' => date('Y-m-d H:i:s', $dates['to']),
                'score IS NOT NULL'
            ], 'date DESC');

            foreach ($events as $event) {
                if (isset($scores[$label][$event->score])) $scores[$label][$event->score] ++;
            }
        }

        return [
            [$scores['last']['3 / 0'], $scores['current']['3 / 0']],
            [$scores['last']['3 / 1'], $scores['current']['3 / 1']],
            [$scores['last']['3 / 2'], $scores['current']['3 / 2']],
            [$scores['last']['2 / 3'], $scores['current']['2 / 3']],
            [$scores['last']['1 / 3'], $scores['current']['1 / 3']],
            [$scores['last']['0 / 3'], $scores['current']['0 / 3']],
        ];
    }
}