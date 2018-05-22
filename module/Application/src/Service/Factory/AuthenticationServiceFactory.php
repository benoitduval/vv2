<?php

namespace Application\Service\Factory;

use Application\Service\AuthenticationService;
use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthenticationServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dbAdapter = $container->get(AdapterInterface::class);
        $authenticationService = new AuthenticationService($container, $dbAdapter);

        return $authenticationService;
    }
}