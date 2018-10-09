<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;

class StatsController extends AbstractController
{
	public function userStatsByEventAction()
	{
		$eventId   = $this->params('eventId');
		$userId    = $this->params('userId');
		$event     = $this->eventTable->find($eventId);
		$quality   = [];
		$average   = [];
		// if ($event && Service\Strings::toSlug($event->name) == $eventName) {
		if ($event) {

		    $config     = $this->get('config');
		    $baseUrl    = $config['baseUrl'];

		    $games = $this->gameTable->fetchAll(['eventId' => $eventId, 'userId' => $userId]);
		    $stats = $this->statsTable->fetchAll(['eventId' => $eventId, 'userId' => $userId]);
		    $user  = $this->userTable->fetchOne(['id' => $userId]);

		    $counters = [
		    	\Application\Model\Stats::POINT_SERVE  => 0,
		    	\Application\Model\Stats::FAULT_SERVE  => 0,
		    	\Application\Model\Stats::POINT_ATTACK => 0,
		    	\Application\Model\Stats::FAULT_ATTACK => 0,
		    	\Application\Model\Stats::POINT_BLOCK  => 0,
		    ];
		    foreach ($stats as $key => $stat) {
		    	if ($stat->pointFor == \Application\Model\Stats::POINT_US ||
		    		($stat->pointFor == \Application\Model\Stats::POINT_THEM &&
		    		 in_array($stat->reason, [
		    		 	\Application\Model\Stats::FAULT_SERVE ,
		    		 	\Application\Model\Stats::FAULT_ATTACK,
		    	     ]) 
		    		)
		    	) {
		    		$counters[$stat->reason] ++;
		    	}
		    }

	        foreach ($games as $key => $game) {
	            $quality[$game->type][] = (int) $game->quality; 
	        }

	    	foreach ($quality as $type => $values) {
	    		$average[$type] = array_sum($values) / count($values);
	    		$average[$type] = sprintf('%0.1f', $average[$type]);
	    	}

		    $view = new ViewModel([
		    	'quality' => $quality,
		    	'average' => $average,
		    	'user' => $user,
		    	'counters' => $counters,
		    ]);
		    $view->setTerminal(true);
		    
		    return $view;
		} else {
		    $this->redirect()->toRoute('home');
		}
	}
}