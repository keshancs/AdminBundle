<?php

namespace AdminBundle\Twig;

use AdminBundle\Admin\Pool;
use AdminBundle\Admin\SettingManager;
use AdminBundle\Entity\Menu;
use AdminBundle\Entity\MenuItem;
use AdminBundle\Entity\Page;
use AdminBundle\Routing\RouteLoader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AdminExtension extends AbstractExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SettingManager
     */
    private $settingManager;

    /**
     * @var ObjectRepository
     */
    private $pageRepo;

    /**
     * @param RequestStack           $requestStack
     * @param Pool                   $pool
     * @param ParameterBagInterface  $parameterBag
     * @param Environment            $environment
     * @param PropertyAccessor       $propertyAccessor
     * @param EntityManagerInterface $em
     * @param SettingManager         $settingManager
     */
    public function __construct(
        RequestStack           $requestStack,
        Pool                   $pool,
        ParameterBagInterface  $parameterBag,
        Environment            $environment,
        PropertyAccessor       $propertyAccessor,
        EntityManagerInterface $em,
        SettingManager         $settingManager
    ) {
        $this->requestStack     = $requestStack;
        $this->pool             = $pool;
        $this->parameterBag     = $parameterBag;
        $this->environment      = $environment;
        $this->propertyAccessor = $propertyAccessor;
        $this->em               = $em;
        $this->settingManager   = $settingManager;
        $this->pageRepo         = $em->getRepository(Page::class);
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('parameter', [$this, 'getParameter']),
            new TwigFunction('get_admin_setting', [$this, 'getAdminSetting']),
            new TwigFunction('admin_pool', [$this, 'getAdminPool']),
            new TwigFunction('admin_route', [$this, 'getAdminRoute']),
            new TwigFunction('admin_path', [$this, 'getAdminPath']),
            new TwigFunction('cms_page_url', [$this, 'generateCmsPageUrl']),
            new TwigFunction('render_menu_element', [$this, 'renderMenuElement'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('render_locale_selector_element', [$this, 'renderLocaleSelectorElement'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('render_page_tree', [$this, 'renderPageTree'], [
                'is_safe' => ['html'],
            ])
        ];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return $this->parameterBag->get($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAdminSetting(string $name)
    {
        return $this->settingManager->get($name);
    }

    /**
     * @return Pool
     */
    public function getAdminPool()
    {
        return $this->pool;
    }

    /**
     * @param string      $route
     * @param string|null $adminCode
     *
     * @return string|null
     */
    public function getAdminRoute(string $route, $adminCode = null)
    {
//        if (null !== $adminCode) {
//            $route = $this->pool->getAdminByAdminCode($adminCode)->getRouteName($route);
//        }
//
//        return $this->router->getRouteName($route);

        return null;
    }

    /**
     * @param string      $path
     * @param string|null $adminCode
     * @param array       $parameters
     *
     * @return string|null
     */
    public function getAdminPath(string $path, $adminCode = null, array $parameters = [])
    {
//        if ($adminRoute = $this->getAdminRoute($path, $adminCode)) {
//            return $this->router->generate($adminRoute, $parameters);
//        }

        return null;
    }

    /**
     * @param array $parameters
     *
     * @return string|null
     */
    public function generateCmsPageUrl(array $parameters)
    {
        $request = $this->requestStack->getCurrentRequest();
        /** @var Page|null $page */
        $page    = $request->get('_page', null);

        if (!isset($parameters['id']) && !isset($parameters['locale'])) {
            throw new \RuntimeException('Parameters `id` or `locale` are required to generate a CMS page URL.');
        }

        if (isset($parameters['id'])) {
            $page = $this->pageRepo->find($parameters['id']);

            unset($parameters['id']);
        }

        if (isset($parameters['locale'])) {
            if (null === $page) {
                throw new \RuntimeException(
                    'In order to generate a URL for page using only `locale` parameter an active page should be present'
                );
            } else if ($page->getLocale() !== $parameters['locale']) {
                if ($translation = $page->getTranslation($parameters['locale'])) {
                    $page = $translation;
                }
            }

            unset($parameters['locale']);
        }

        $url = $request->getSchemeAndHttpHost() . $page->getPath();

        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }

    /**
     * @param string|int $menuId
     * @param array      $options
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderMenuElement($menuId, array $options = [])
    {
        $count = 0;
        $items = $pages = [];
        /** @var Menu $menu */
        $menu  = $this->em->getRepository(Menu::class)->findOneBy(['id' => $menuId]);

        if ($menu) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('mi')
                ->from(MenuItem::class, 'mi', 'mi.id')
                ->where($qb->expr()->in('mi.id', ':menu_item_ids'))
                ->setParameter('menu_item_ids', $menu->getItemIds())
            ;

            $countQuery = (clone $qb)->resetDQLPart('join');

            try {
                $count = $countQuery->select($qb->expr()->count('mi'))->getQuery()->getSingleScalarResult();
            } catch (UnexpectedResultException $e) {
            }

            $items = $qb->getQuery()->getResult();

            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('p')
                ->from(Page::class, 'p', 'p.id')
                ->where($qb->expr()->in('p.id', ':page_ids'))
                ->setParameter('page_ids', array_keys($menu->getPageIds()))
            ;

            $pageIds = $qb->getQuery()->getResult();

            foreach ($menu->getPageIds() as $pageId => $itemId) {
                $pages[$itemId] = $pageIds[$pageId];
            }
        }

        if ($count) {
            return $this->environment->render($options['template'] ?? '@Admin/CMS/menu.html.twig', [
                'hierarchy' => $menu->getHierarchy(),
                'items'     => $items,
                'pages'     => $pages,
            ]);
        }

        return null;
    }

    /**
     * @param array $options
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderLocaleSelectorElement(array $options = [])
    {
        return $this->environment->render($options['template'] ?? '@Admin/CMS/locale_selector.html.twig');
    }

    /**
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderPageTree()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository(Page::class)->createQueryBuilder('p');
        $qb
            ->where($qb->expr()->isNull('p.parent'))
            ->andWhere($qb->expr()->eq('p.locale', ':locale'))
            ->setParameter('locale', 'lv')
            ->orderBy('p.priority', 'ASC')
        ;

        return $this->environment->render('@Admin/CMS/page_tree.html.twig', [
            'pages' => $qb->getQuery()->getResult(),
        ]);
    }
}
