<?php

namespace Api;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements ConfigProviderInterface, DependencyIndicatorInterface
{
    /**
     * @var string
     */
    const VERSION = '1.0.0';

    public function getModuleDependencies()
    {
        return ['Application'];
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}