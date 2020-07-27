<?php

namespace AdminBundle\Admin;

use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class BlockAdmin extends AbstractAdmin
{
    /**
     * @inheritDoc
     */
    public function configure(Environment $environment, Request $request)
    {
        parent::configure($environment, $request);

        $this->getContext()->setShowPageSidebar(true);
    }
}