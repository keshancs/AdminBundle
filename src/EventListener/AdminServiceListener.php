<?php

namespace AdminBundle\EventListener;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Admin\Pool;
use AdminBundle\Controller\CoreController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

final class AdminServiceListener
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($adminCode = $request->get('_admin', null)) {
            /** @var AdminInterface $admin */
            $admin = $this->pool->getAdminByAdminCode($adminCode);
            $admin->configure($request);

            /** @var CoreController $controller */
            $controller = $event->getController()[0];
            $controller->setAdmin($admin);
        }
    }
}
