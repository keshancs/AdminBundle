<?php

namespace AdminBundle\Block;

use AdminBundle\Entity\Block;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

interface BlockInterface
{
    /**
     * @param Environment $twig
     * @param Block       $block
     *
     * @return Response
     */
    public function render(Environment $twig, Block $block);
}