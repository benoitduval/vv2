<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\Model;
use Application\TableGateway;

class Disponibility extends AbstractTableGateway
{

    public function getCounters($eventId)
    {
        $result = [
            Model\Disponibility::RESP_OK => 0,
            Model\Disponibility::RESP_NO => 0,
            Model\Disponibility::RESP_INCERTAIN => 0,
            Model\Disponibility::RESP_NO_ANSWER => 0,
        ];
        $disponibilities = $this->fetchAll([
            'eventId' => $eventId
        ]);

        foreach ($disponibilities as $disponibility) {
            $result[$disponibility->response]++;
        }

        return $result;
    }

    public function getUserResponses($eventId)
    {
        $result = [
            'counters' => [
                Model\Disponibility::RESP_OK => 0,
                Model\Disponibility::RESP_NO => 0,
                Model\Disponibility::RESP_INCERTAIN => 0,
                Model\Disponibility::RESP_NO_ANSWER => 0,
            ],
            'users' => [
                Model\Disponibility::RESP_OK => [],
                Model\Disponibility::RESP_NO => [],
                Model\Disponibility::RESP_INCERTAIN => [],
                Model\Disponibility::RESP_NO_ANSWER => [],
            ]
        ];

        $disponibilities = $this->fetchAll([
            'eventId' => $eventId
        ]);

        $userTable = $this->getContainer()->get(TableGateway\User::class);
        foreach ($disponibilities as $disponibility) {
            $result['counters'][$disponibility->response]++;
             $user = $userTable->find($disponibility->userId);
             $result['users'][$disponibility->response][] = $user->getFullName();
        }

        return $result;
    }
}