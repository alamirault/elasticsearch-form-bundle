<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/27/19 3:36 PM
 */

namespace Alamirault\ElasticsearchBundle\Service;

use Alamirault\ElasticsearchBundle\Model\ElasticsearchDenormalizerInterface;
use Alamirault\ElasticsearchBundle\Model\Result;
use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\ResultSet\BuilderInterface;
use Symfony\Component\Serializer\Serializer;

class ResultSetBuilder implements BuilderInterface
{
    private $serializer;
    /**
     * @var ElasticsearchDenormalizerInterface
     */
    private $denormalizer;

    /**
     * ResultSetBuilder constructor.
     * @param ElasticsearchDenormalizerInterface $denormalizer
     */
    public function __construct(ElasticsearchDenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
        $normalizers = [$this->denormalizer];
        $this->serializer = new Serializer($normalizers);
    }


    /**
     * Builds a ResultSet for a given Response.l
     *
     * @param Response $response
     * @param Query $query
     *
     * @return ResultSet
     */
    public function buildResultSet(Response $response, Query $query)
    {
        $results = $this->buildResults($response);
        $resultSet = new ResultSet($response, $query, $results);

        return $resultSet;
    }

    /**
     * Builds individual result objects.
     *
     * @param Response $response
     *
     * @return Result[]
     */
    private function buildResults(Response $response): array
    {
        $data = $response->getData();
        $results = [];

        if (!isset($data['hits']['hits'])) {
            return $results;
        }

        foreach ($data['hits']['hits'] as $hit) {
            $result = new Result($hit);
            $object = $this->serializer->denormalize($hit, $this->denormalizer->getClass());
            $result->setModel($object);

            $results[] = $result;
        }

        return $results;
    }
}