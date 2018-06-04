<?php
namespace Application\Factory\View\Helper;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\View\Helper\AssetsHelper;

class AssetsHelperFactory implements FactoryInterface
{
    /**
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return assetsHelper
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $class = $requestedName ? $requestedName : AssetsHelper::class;
        $config = $container->get('config');
        $assetsHelper = new $class($config['assets']);
        return $assetsHelper;

    }
    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ContainerInterface|ServiceLocatorInterface $container
     * @return assetsHelper
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, AssetsHelper::class);
    }
}