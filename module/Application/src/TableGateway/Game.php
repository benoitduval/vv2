<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\TableGateway;
use Application\Model\Game as GameStats;

class Game extends AbstractTableGateway
{
	private function _getHistory($params)
	{
	    $key = 'stats.' . md5(implode('.', $params));
	    $memcached = $this->getContainer()->get('memcached');
	    if ($result = $memcached->getItem($key)) return $result;
	    $gameStats = $this->fetchAll($params);
	    foreach ($gameStats as $stat) {
	    	$result[] = (int) $stat->quality;
	    }

	    if ($result) $memcached->setItem($key, $result);

	    return $result;
	}

	private function _getCounter($params)
	{
	    $key = 'stats.' . md5(implode('.', $params));
	    $memcached = $this->getContainer()->get('memcached');
	    if ($result = $memcached->getItem($key)) return $result;
	    $result = $this->count($params);

	    if ($result) $memcached->setItem($key, $result);

	    return $result;
	}

	public function getReceptionEvolution($eventId, $userId = null)
	{   
	    $params = [
	        'eventId' => $eventId,
	        'type' 	  => GameStats::RECEPTION,
	    ];
	    if ($userId) $params['userId'] = $userId;
	    $result = $this->_getHistory($params);
	    return $result;
	}

	public function getReceptionCount($eventId, $userId = null)
	{
		$result = [];
		$receptions = $this->getReceptionEvolution($eventId, $userId);
		foreach ($receptions as $reception) {
		    if (isset($result[$reception])) {
		        $result[(string) $reception] ++;
		    } else {
		        $result[(string) $reception] = 1;
		    }
		}
		ksort($result);
		return $result;
	}

	public function getReceptionAvg($eventId, $userId = null)
	{
		$receptions = $this->getReceptionEvolution($eventId, $userId);
		if(count($receptions)) {
		    $receptions = array_filter($receptions);
		    $average = array_sum($receptions) / count($receptions);
		    return sprintf('%0.1f', $average);
		}
	}

	public function getServiceEvolution($eventId, $userId = null)
	{   
	    $params = [
	        'eventId' => $eventId,
	        'type' 	  => GameStats::SERVICE,
	    ];
	    if ($userId) $params['userId'] = $userId;
	    return $this->_getHistory($params);
	}

	public function getServiceCount($eventId, $userId = null)
	{
		$result = [];
		$services = $this->getServiceEvolution($eventId, $userId);
		foreach ($services as $service) {
		    if (isset($result[$service])) {
		        $result[(string) $service] ++;
		    } else {
		        $result[(string) $service] = 1;
		    }
		}
		ksort($result);
		return $result;
	}

	public function getDigs($eventId, $userId = null)
	{   
	    $params = [
	        'eventId' => $eventId,
	        'type' 	  => GameStats::DIG,
	    ];
	    if ($userId) $params['userId'] = $userId;
	    return $this->_getCounter($params);
	}

	public function getAttempts($eventId, $userId = null)
	{   
	    $params = [
	        'eventId' => $eventId,
	        'type' 	  => GameStats::ATTEMPT,
	    ];
	    if ($userId) $params['userId'] = $userId;
	    return $this->_getCounter($params);
	}
}