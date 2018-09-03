<?php

namespace Application\Service;

use Interop\Container\ContainerInterface;
use Application\TableGateway;
use Application\Model;
use Zend\View\Model\ViewModel;


class NotificationService
{
    protected $_container;

    const TYPE_COMMENT = 1;
    const TYPE_EVENT   = 2;

    static private $_template = [
    	self::TYPE_COMMENT => '/../../view/mail/comment.phtml'
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    public function send($type, $senderId, $data)
    {	
    	$config = $this->_container->get('config');
    	if ($config['mail']['allowed']) {
    	    
        	$userTable = $this->_container->get(TableGateway\User::class);
        	$dispTable = $this->_container->get(TableGateway\Disponibility::class);
        	$notifTable = $this->_container->get(TableGateway\Notification::class);
    	    
    	    $users = $userTable->getAllByGroupId($data['group']->id);
    	    unset($users[$senderId]);

    	    $bcc   = [];
    	    foreach ($users as $user) {
    	        $email = true;
    	        $disponibility = $dispTable->fetchOne(['userId' => $user->id, 'eventId' => $data['event']->id]);
    	        if ($disponibility && $disponibility->response = Model\Disponibility::RESP_NO && !$notifTable->isAllowed(Model\Notification::COMMENT_ABSENT, $user->id)) {
    	            $email = false;
    	        } else if (!$notifTable->isAllowed(Model\Notification::COMMENTS, $user->id)) {
    	            $email = false;
    	        }

    	        if ($email) $bcc[] = $user->email;
    	    }

	        $view       = new \Zend\View\Renderer\PhpRenderer();
	        $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
	        $resolver->setMap([
	            'event' => __DIR__ . static::$_template[$type]
	        ]);
	        $view->setResolver($resolver);

	        $viewModel  = new ViewModel();
	        $data['baseUrl'] = $config['baseUrl'];
	        $viewModel->setTemplate('event')->setVariables($data);

	        // $oneSignal = $this->get(OneSignalService::class);
	        // $oneSignal->setData([
	        //     'header'   => 'Nouvel Commentaire !',
	        //     'content'  => $event->name,
	        //     'subtitle' => \Application\Service\Date::toFr($date->format('l d F \à H\hi')),
	        //     'url'      => $config['baseUrl'] . '/event/detail/' . $event->id,
	        // ]);
	        // foreach ($users as $user) {
	        //     $oneSignal->sendTo($user->email);
	        // }

	        $mail = $this->_container->get(MailService::class);
	        // $mail->addBcc($bcc);
            // TODO : Change This !
	        $mail->addBcc('benoit.duval.pro@gmail.com');
	        $mail->setSubject('[' . $data['group']->name . '] ' . $data['event']->name . ' - ' . \Application\Service\Date::toFr($data['date']->format('l d F \à H\hi')));
	        $mail->setBody($view->render($viewModel));
	        try {
	            $mail->send();
	        } catch (\Exception $e) {
	        }
    	}
    	return 'test';
    }
}