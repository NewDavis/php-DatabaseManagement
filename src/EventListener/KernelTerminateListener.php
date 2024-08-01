<?php

namespace DatabaseManagement\EventListener;

use DatabaseManagement\Core\Driver\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelTerminateListener implements EventSubscriberInterface
{

    public function __construct(private Connection $connection)
    {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate'
        ];
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        $this->connection->disconnect();
    }

}