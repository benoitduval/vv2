<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Model\Stats as Statistiques;
use Application\Model\Game as GameStats;

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
		    	Statistiques::POINT_SERVE  => 0,
		    	Statistiques::FAULT_SERVE  => 0,
		    	Statistiques::POINT_ATTACK => 0,
		    	Statistiques::FAULT_ATTACK => 0,
		    	Statistiques::POINT_BLOCK  => 0,
		    ];

	        foreach ($games as $key => $game) {
	            $quality[$game->type][$game->numero] = (int) $game->quality; 
	        }

	        foreach ($stats as $key => $stat) {
	        	if ($stat->pointFor == Statistiques::POINT_US && $stat->reason = Statistiques::POINT_SERVE) {
	        		$quality[GameStats::SERVICE][$stat->numero] = 6;
	        	}

	        	if ($stat->pointFor == Statistiques::POINT_THEM && $stat->reason = Statistiques::FAULT_SERVE) {
	        		$quality[GameStats::SERVICE][$stat->numero] = 0;
	        	}

	        	if ($stat->pointFor == Statistiques::POINT_US ||
	        		($stat->pointFor == Statistiques::POINT_THEM &&
	        		 in_array($stat->reason, [
	        		 	Statistiques::FAULT_SERVE ,
	        		 	Statistiques::FAULT_ATTACK,
	        	     ]) 
	        		)
	        	) {
	        		$counters[$stat->reason] ++;
	        	}
	        }

	    	foreach ($quality as $type => $values) {
	    		$average[$type] = array_sum($values) / count($values);
	    		$average[$type] = sprintf('%0.1f', $average[$type]);
		    	ksort($quality[$type]);
		    	$quality[$type] = array_values($quality[$type]);
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