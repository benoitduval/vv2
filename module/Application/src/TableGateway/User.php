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
}