<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\TableGateway;
use Application\Model\Game as GameStats;

class Game extends AbstractTableGateway
{
	public function getReceptionStats($eventId, $userId = null)
	{
		$key = 'stats.reception.eventId.' . $eventId;
		if ($userId) $key .= '.userId.' . $userId;
		$memcached = $this->getContainer()->get('memcached');
		if ($result = $memcached->getItem($key)) return $result;

		$evolution = $this->getReceptionEvolution($eventId, $userId);
		$result['evolution'] = array_values($evolution);
		$result['quality'] 	 = $this->getRecepByQuality($eventId, $userId);
		$result['average']   = $this->getReceptionAvg($eventId, $userId);
		$result['count']     = count($result['evolution']);

		$result['totalReception'] = count($this->getReceptionEvolution($eventId));
		$result['percent'] = ceil(($result['count'] / $result['totalReception']) * 100);
	    if ($result) $memcached->setItem($key, $result);

		return $result;
	}

	public function getServiceStats($eventId, $userId = null)
	{
		$key = 'stats.service.eventId.' . $eventId;
		if ($userId) $key .= '.userId.' . $userId;
		$memcached = $this->getContainer()->get('memcached');
		if ($result = $memcached->getItem($key)) return $result;

		$statsTable = $this->getContainer()->get(TableGateway\Stats::class);

		$servicePoints = $statsTable->getServiceEvolution($eventId, $userId);
		$serviceList   = $this->getServiceEvolution($eventId, $userId);
		$evolution     = $serviceList + $servicePoints;
		ksort($evolution);
		
		$result['evolution']    = array_values($evolution);
		$result['quality']      = $this->getServiceByQuality($evolution);
		$result['aces'] 	    = $statsTable->getAces($eventId, $userId);
		$result['faults'] 	    = $statsTable->getServiceFault($eventId, $userId);
		$result['average']      = $this->getServiceAvg($eventId, $userId);
		$result['count']        = count($result['evolution']);
		$result['acePercent']   = ceil(($result['aces'] / $result['count']) * 100);
		$result['faultPercent'] = ceil(($result['faults'] / $result['count']) * 100);

	    if ($result) $memcached->setItem($key, $result);

		return $result;
	}

	private function _getHistory($params)
	{
        $result = [];
	    $gameStats = $this->fetchAll($params);
	    foreach ($gameStats as $stat) {
	    	$result[$stat->numero] = (int) $stat->quality;
	    }
	    return $result;
	}

	private function _getCounter($params)
	{
	    return $this->count($params);
	}

	public function getReceptionEvolution($eventId, $userId = null)
	{   
	    $params = [
	        'eventId' => $eventId,
	        'type' 	  => GameStats::RECEPTION,
	    ];
	    if ($userId) $params['userId'] = $userId;
	    $result = $this->_getHistory($params);
	    ksort($result);
	    return $result;
	}

	public function getRecepByQuality($eventId, $userId = null)
	{
		for ($i = 0; $i < 6; $i++) $result[$i] = 0;
		$receptions = $this->getReceptionEvolution($eventId, $userId);
		foreach ($receptions as $reception) {
		    $result[(string) $reception] ++;
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

	public function getServiceByQuality($services)
	{
		for ($i = 0; $i < 7; $i++) $result[$i] = 0;
		foreach ($services as $service) {
		    $result[$service] ++;
		}
		return $result;
	}

	public function getServiceAvg($eventId, $userId = null)
	{
		$services = $this->getServiceEvolution($eventId, $userId);
		if(count($services)) {
		    $services = array_filter($services);
		    $average = array_sum($services) / count($services);
		    return sprintf('%0.1f', $average);
		}
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