<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/1/19 10:57 AM
 */

namespace Alamirault\ElasticsearchBundle\Twig\Extension;

use Alamirault\ElasticsearchBundle\Model\SortableItems;
use Alamirault\ElasticsearchBundle\Service\ItemsPerPage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ElasticsearchExtension extends AbstractExtension
{
    /**
     * @var ItemsPerPage
     */
    private $itemsPerPage;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * ElasticsearchExtension constructor.
     * @param ItemsPerPage $itemsPerPage
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     */
    public function __construct(ItemsPerPage $itemsPerPage, RequestStack $requestStack, RouterInterface $router)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('elasticsearch_pagination', [$this, 'elasticsearchPagination'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('elasticsearch_sortable', [$this, 'elasticsearchSortable'], ['is_safe' => ['html']]),
        ];
    }

    public function elasticsearchPagination(Environment $env, SortableItems $sortableItems): string
    {
        $data = $this->getPaginationData($sortableItems);

        return $env->render(
            "@AlamiraultElasticsearch/pagination.html.twig", $data
        );
    }

    public function elasticsearchSortable(SortableItems $sortableItems, string $linkName, string $sortField): string
    {
        $route = $this->requestStack->getCurrentRequest()->get('_route');

        $link = $this->router->generate($route, [
            'sort' => $sortField,
            'direction' => $sortableItems->getSortDirection() === 'asc' ? 'desc' : 'asc',
        ]);

        return sprintf('<a href="%s" class="%s">%s</a>', $link, strtolower($sortableItems->getSortDirection()), $linkName);
    }

    public function getPaginationData(SortableItems $sortableItems)
    {
        $pageCount = $this->getPageCount($sortableItems);
        $current = $sortableItems->getCurrentPage();

        if ($pageCount < $current) {
            $current = $pageCount;
        }

        $pageRange = 5;
        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }

        $delta = ceil($pageRange / 2);

        if ($current - $delta > $pageCount - $pageRange) {
            $pages = range($pageCount - $pageRange + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = range($offset + 1, $offset + $pageRange);
        }

        $proximity = floor($pageRange / 2);

        $startPage = $current - $proximity;
        $endPage = $current + $proximity;

        if ($startPage < 1) {
            $endPage = min($endPage + (1 - $startPage), $pageCount);
            $startPage = 1;
        }

        if ($endPage > $pageCount) {
            $startPage = max($startPage - ($endPage - $pageCount), 1);
            $endPage = $pageCount;
        }

        $viewData = [
            'last' => $pageCount,
            'current' => $current,
            'numItemsPerPage' => $this->itemsPerPage->getValue(),
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $sortableItems->getItems()->getTotalHits(),
            'pageRange' => $pageRange,
            'startPage' => $startPage,
            'endPage' => $endPage,
        ];

        if ($current > 1) {
            $viewData['previous'] = $current - 1;
        }

        if ($current < $pageCount) {
            $viewData['next'] = $current + 1;
        }

        $viewData['pagesInRange'] = $pages;
        $viewData['firstPageInRange'] = min($pages);
        $viewData['lastPageInRange'] = max($pages);

        $request = $this->requestStack->getCurrentRequest();

        $viewData["route"] = $request->get('_route');
        $viewData["query"] = $request->query->all();
        $viewData["pageParameterName"] = 'page';
        return $viewData;
    }

    public function getPageCount(SortableItems $sortableItems)
    {
        $totalHits = $sortableItems->getItems()->getTotalHits();
        $nbItemsPerPage = $this->itemsPerPage->getValue();

        return intval(ceil($totalHits / $nbItemsPerPage));
    }
}