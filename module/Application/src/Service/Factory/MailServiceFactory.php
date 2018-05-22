<?php

namespace Application\Service\Factory;

use Application\Service\MailService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class MailServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config   = $container->get('config');

        // Setup SMTP transport
        $transport = new SmtpTransport();
        $options   = new SmtpOptions(array(
            'host'              => 'smtp.gmail.com',
            'connection_class'  => 'login',
            'connection_config' => array(
                'ssl'       => 'tls',
                'username' => $config['mail']['address'],
                'password' => $config['mail']['password']
            ),
            'port' => 587,
        ));

        $transport->setOptions($options);
        return new $requestedName($transport);
    }
}