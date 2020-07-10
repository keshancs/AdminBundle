<?php

namespace AdminBundle\EventListener;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Admin\Pool;
use AdminBundle\Controller\CoreController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

final class AdminServiceListener
{
    /**
     * @var Environment $environment
     */
    private $environment;

    /**
     * @var Pool $pool
     */
    private $pool;

    /**
     * @param Environment $environment
     * @param Pool        $pool
     */
    public function __construct(Environment $environment, Pool $pool)
    {
        $this->environment = $environment;
        $this->pool        = $pool;
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
            $admin->configure($this->environment, $request);

            /** @var CoreController $controller */
            $controller = $event->getController()[0];
            $controller->setAdmin($admin);
        }
    }
}
