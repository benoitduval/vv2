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
		    $user  = $this->userTable->fetchOne(['id' => $userId]);

	        foreach ($games as $key => $game) {
	            $quality[$game->type][] = $game->quality; 
	        }

	    	foreach ($quality as $type => $values) {
	    		$average[$type] = array_sum($values) / count($values);
	    		$average[$type] = sprintf('%0.1f', $average[$type]);
	    	}

		    $view = new ViewModel([
		    	'average' => $average,
		    	'user' => $user,
		    ]);
		    $view->setTerminal(true);
		    
		    return $view;
		} else {
		    $this->redirect()->toRoute('home');
		}
	}
}