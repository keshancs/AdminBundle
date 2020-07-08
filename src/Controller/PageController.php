<?php

namespace AdminBundle\Controller;

use AdminBundle\Entity\Page;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PageController extends CRUDController
{
    /**
     * @param Request  $request
     * @param Packages $packageManager
     *
     * @return JsonResponse
     */
    public function createFromTree(Request $request, Packages $packageManager)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['result' => 'error']);
        }

        $em = $this->getDoctrine()->getManager();

        if ($parent = $request->get('parent_id', null)) {
            /** @var Page|null $parent */
            $parent = $em->find(Page::class, $parent);
        }

        $page = new Page();
        $page
            ->setParent($parent)
            ->setTitle('Untitled')
            ->setLocale('lv')
            ->setIsHomePage(false)
            ->setPriority($this->getMaxParentChildPriority($parent) + 1)
        ;

        $em->persist($page);
        $em->flush();

        $path = $this->admin->generateObjectUrl('edit', $page->getId());

        return $this->json([
            'result' => 'success',
            'page_id' => $page->getId(),
            'title'   => $page->getTitle(),
            'path'    => $path,
            'node'    => $this->renderView('@Admin/CMS/page_tree_node.html.twig', ['page' => $page]),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateFromTree(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['result' => 'error']);
        }

        $em = $this->getDoctrine()->getManager();

        if ($page = $request->get('page_id', null)) {
            /** @var Page|null $page */
            $page = $em->find(Page::class, $page);
        }

        if (!$page) {
            return $this->json(['result' => 'error']);
        }

        if ($parent = $request->get('parent_id', null)) {
            /** @var Page|null $parent */
            $parent = $em->find(Page::class, $parent);
        }

        $page
            ->setParent($parent)
            ->setPriority($this->getMaxParentChildPriority($parent) + 1)
        ;

        $em->flush();

        return $this->json(['result' => 'success']);
    }

    /**
     * @param Page|null $page
     *
     * @return int
     */
    private function getMaxParentChildPriority(Page $page = null)
    {
        $priority = 0;

        if (null === $page) {
            return $priority;
        }

        foreach ($page->getChildren() as $child) {
            $priority = max($priority, $child->getPriority());
        }

        return $priority;
    }
}