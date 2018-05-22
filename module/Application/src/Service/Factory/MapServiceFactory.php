<?php

namespace Application\Service\Factory;

use Application\Service\Map;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MapServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $config   = $container->get('config');
        $apiKey   = $config['api']['googlemaps']['key'];
        $url      = $config['api']['googlemaps']['url'];

        return new $requestedName($apiKey, $url);
    }
}