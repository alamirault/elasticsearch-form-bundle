<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/27/19 3:20 PM
 */

namespace Alamirault\ElasticsearchBundle\Service;

use Alamirault\ElasticsearchBundle\Form\Extension\ElasticConditionTypeExtension;
use Alamirault\ElasticsearchBundle\Model\ElasticsearchDenormalizerInterface;
use Alamirault\ElasticsearchBundle\Model\Index;
use Alamirault\ElasticsearchBundle\Model\IndexRegistry;
use Alamirault\ElasticsearchBundle\Model\Result;
use Alamirault\ElasticsearchBundle\Model\SortableItems;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\ResultSet;
use Elastica\Search;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ElasticsearchMaker
{
    /**
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;
    /**
     * @var IndexMaker
     */
    private $indexMaker;
    /**
     * @var ItemsPerPage
     */
    private $itemsPerPage;

    /**
     * ElasticsearchMaker constructor.
     * @param CsrfTokenManagerInterface $tokenManager
     * @param IndexMaker $indexMaker
     * @param ItemsPerPage $itemsPerPage
     */
    public function __construct(CsrfTokenManagerInterface $tokenManager, IndexMaker $indexMaker, ItemsPerPage $itemsPerPage)
    {
        $this->tokenManager = $tokenManager;
        $this->indexMaker = $indexMaker;
        $this->itemsPerPage = $itemsPerPage;
    }


    /**
     * @param FormInterface $form Form uses fo search
     * @param Request $request
     * @param IndexRegistry $indexRegistry Registry of registered indexes
     * @param string $indexName Name of index
     * @param ElasticsearchDenormalizerInterface $denormalizer Used for transform array result to an object
     * @param array $options Sortfield, Sortdirection
     * @return SortableItems
     */
    public function manageForm(FormInterface $form, Request $request, IndexRegistry $indexRegistry, string $indexName,
                               ElasticsearchDenormalizerInterface $denormalizer, array $options = []): SortableItems
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $sortField = $request->query->get('sort', $options["sortField"]);
        $sortDirection = $request->query->get('direction', $options["sortDir"]);
        $currentPage = $request->query->getInt('page', 1);

        $form->handleRequest($request);
        $query = $this->getQueryFromFilterConditions($request, $form);

        $pageNumber = $request->query->getInt('page', 1);
        $nbItemsPerPage = $this->itemsPerPage->getValue();

        if (!$this->canPaginate($indexRegistry, $indexName, $pageNumber, $nbItemsPerPage)) {
            return new SortableItems(null, $sortField, $sortDirection, $currentPage);
        }
        $this->paginate($query, $pageNumber, $nbItemsPerPage);

        $this->sort($query, $request, $options["sortField"]);

        $boLogs = $this->makeSearch($indexRegistry, $indexName, $query, $denormalizer);

        return new SortableItems($boLogs, $sortField, $sortDirection, $currentPage);
    }

    /**
     * @param IndexRegistry $indexRegistry
     * @param string $indexName
     * @param ElasticsearchDenormalizerInterface $denormalizer
     * @param string $value
     * @param string $key
     * @return Result
     */
    public function getDocument(IndexRegistry $indexRegistry, string $indexName, ElasticsearchDenormalizerInterface $denormalizer, string $value, string $key = 'uuid'): Result
    {
        $query = new Query\Match($key, $value);
        $resultSet = $this->makeSearch($indexRegistry, $indexName, $query, $denormalizer);

        if (!$resultSet->count()) {
            throw new NotFoundHttpException();
        }

        if ($resultSet->count() > 1) {
            throw new NotFoundHttpException(sprintf("There are more than one document in index %s with '%s'='%s'", $indexName, $key, $value));
        }

        return $resultSet->getResults()[0];
    }

    /**
     * @param IndexRegistry $indexRegistry
     * @param string $indexName
     * @param $query
     * @param ElasticsearchDenormalizerInterface|null $denormalizer
     * @param array $options
     * @return ResultSet
     */
    public function makeSearch(IndexRegistry $indexRegistry, string $indexName, $query, ?ElasticsearchDenormalizerInterface $denormalizer = null, $options = []): ResultSet
    {
        $index = $this->indexMaker->getIndex($indexRegistry, $indexName);
        $search = $this->getSearch($index, $denormalizer, $query, $options);
        return $search->search('', null, \Elastica\Request::POST);
    }

    public function getSearch(Index $index, ?DenormalizerInterface $denormalizer, $query, $options): Search
    {
        $builder = null;
        if ($denormalizer) {
            $builder = new ResultSetBuilder($denormalizer);
        }

        return $index->createSearch(
            $query,
            $options,
            $builder
        );
    }

    public function paginate(Query $query, int $page, int $size): Query
    {
        $query->setFrom($size * ($page - 1));
        $query->setSize($size);

        return $query;
    }

    public function sort(Query $query, Request $request, string $defaultSortField = "createdAt", string $defaultSortDirection = "desc"): Query
    {
        $sortField = $request->query->get('sort', $defaultSortField);
        $sortDirection = $request->query->get('direction', $defaultSortDirection);

        $query->addSort([
            $sortField => [
                "order" => $sortDirection,
            ],
        ]);

        return $query;
    }


    /**
     * @param Request $request
     * @param FormInterface $form
     * @return Query Empty query when form is not valid
     */
    public function getQueryFromFilterConditions(Request $request, FormInterface $form): Query
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if (!is_null($request->getSession())) {
                FormSessionUtils::saveFormSession($request);
            }
        }

        return $this->getFilterConditions($form);
    }


    private function getFilterConditions(FormInterface $form): Query
    {
        $query = new Query();
        $mustMatchs = new BoolQuery();

        /** @var $child FormInterface */
        foreach ($form->all() as $child) {
            $callable = $child->getConfig()->getAttribute(ElasticConditionTypeExtension::EXTENSION_NAME);

            if ($callable instanceof \Closure) {
                $values = $this->prepareFilterValues($child);
                $condition = $callable($values);
                if (!is_null($condition)) {
                    if (!($condition instanceof AbstractQuery)) {
                        throw new \Exception(sprintf("The callable method of %s must return %s object, %s given", ElasticConditionTypeExtension::EXTENSION_NAME, AbstractQuery::class, gettype($condition) === "object" ? get_class($condition) : gettype($condition)));
                    }
                    $mustMatchs->addMust($condition);
                }
            }
        }

        if ($mustMatchs->count()) {
            $query->setQuery($mustMatchs);
        }

        return $query;
    }

    private function prepareFilterValues(FormInterface $form)
    {
        return $form->getNormData();
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "sortField" => "creation_date",
            "sortDir" => "desc",
        ]);

        $resolver->setAllowedValues("sortDir", ["desc", "asc"]);
    }

    private function canPaginate(IndexRegistry $indexRegistry, string $indexName, int $pageNumber, int $nbItemsPerPage): bool
    {
        $maxResultWindow = $this->indexMaker->getIndex($indexRegistry, $indexName)->getClient()->getMaxResultWindow();

        return $pageNumber * $nbItemsPerPage <= $maxResultWindow;
    }
}