<?php

namespace Application\Service\Factory;

use Application\Service\OneSignalService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class OneSignalFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $config  = $container->get('config');
        $userkey = $config['api']['oneSignal']['userkey'];
        $appid   = $config['api']['oneSignal']['appid'];
        $appkey  = $config['api']['oneSignal']['appkey'];

        return new $requestedName($userkey, $appid, $appkey);
    }
}