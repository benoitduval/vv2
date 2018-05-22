<?php

namespace Application\TableGateway;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Application\Model;

class Notification extends AbstractTableGateway
{
	public function isAllowed($notification, $userId)
	{
	    $notif = $this->fetchOne([
	        'notification' => $notification,
	        'userId'       => $userId,
	    ]);

	    if (!$notif) return true;
	    return $notif->status == Model\Notification::ACTIVE;
	}
}