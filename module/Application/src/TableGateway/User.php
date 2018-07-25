<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\TableGateway;

class User extends AbstractTableGateway
{
    public function getAllByGroupId($groupId)
    {
        $userGroupTable = $this->getContainer()->get(TableGateway\UserGroup::class);
        $objs = $userGroupTable->fetchAll([
            'groupId' => $groupId
        ]);

        $users = [];
        if ($objs->toArray()) {
            $ids = [];
            foreach ($objs as $obj) {
                $ids[] = $obj->userId;
            }

            $players = $this->fetchAll([
                'id' => $ids
            ]);
            foreach ($players as $user) {
                $users[$user->id] = $user;
            }
        }
        return $users;
    }

    public function getAllByEventId($eventId)
    {
        $dispTable = $this->getContainer()->get(TableGateway\Disponibility::class);
        $disponibilities = $dispTable->fetchAll(['eventId' => $eventId, 'response' => \Application\Model\Disponibility::RESP_OK]);
        foreach ($disponibilities as $disponibility) {
            $userIds[] = $disponibility->userId;
        }
        $users = $this->fetchAll(['id' => $userIds]);
        foreach ($users as $user) {
            $result[$user->id] = $user;
        }
        return $result;
    }
}