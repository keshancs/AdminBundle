<?php

namespace AdminBundle\Controller;

use AdminBundle\Block\BlockType;
use AdminBundle\Block\TextBlockType;
use AdminBundle\Entity\Block;
use AdminBundle\Entity\Page;
use Doctrine\ORM\Mapping\MappingException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends CRUDController
{
    /**
     * @param Request $request
     *
     * @return Response
     * @throws MappingException
     */
    public function compose(Request $request)
    {
        $id     = $request->get($this->admin->getIdentifier());
        $object = $this->admin->getObject($id);

        return $this->render('@Admin/CRUD/page_compose.html.twig', [
            'object' => $object,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws MappingException
     */
    public function createBlock(Request $request)
    {
        $id      = $request->get($this->admin->getIdentifier());
        /** @var Page $object */
        $object  = $this->admin->getObject($id);
        $type    = $request->get('type', null);
        $blocks  = $this->get('admin.pool')->getBlocks();
        $builder = $this->createFormBuilder();

        if ($type) {
            $tempBlock = new $blocks[$type]();
            $tempBlock->buildForm($builder, []);
        } else {
            $keys = array_keys($blocks);

            $builder
                ->add('type', ChoiceType::class, [
                    'attr'     => ['class' => 'form-control'],
                    'choices'  => array_combine($keys, $keys),
                    'row_attr' => ['class' => 'form-group'],
                ])
            ;
        }

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$type) {
                $type = $data['type'];

                return $this->redirectToRoute(
                    $request->get('_route'),
                    array_merge($request->get('_route_params'), ['type' => $type])
                );
            }

            $block = new Block();
            $block
                ->setPage($object)
                ->setType($type)
                ->setData($data)
            ;

            $this->addFlash('admin_flash_success', $this->admin->trans('admin.flash.edit_success'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($block);
            $em->flush();
        }

        return $this->render('@Admin/CRUD/page_create_block.html.twig', [
            'object' => $object,
            'form'   => $form->createView(),
        ]);
    }

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