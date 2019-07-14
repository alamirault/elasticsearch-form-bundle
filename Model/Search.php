<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/8/19 11:29 AM
 */

namespace Alamirault\ElasticsearchBundle\Model;


use Elastica\Client;
use Elastica\Request;
use Elastica\ResultSet\BuilderInterface;
use Elastica\ResultSet\DefaultBuilder;

class Search extends \Elastica\Search
{
    private $builder;

    public function __construct(Client $client, BuilderInterface $builder = null)
    {
        parent::__construct($client, $builder);

        $this->builder = $builder ?: new DefaultBuilder();
    }

    public function search($query = '', $options = null)
    {
        $this->setOptionsAndQuery($options, $query);

        $query = $this->getQuery();
        $path = $this->getPath();

        $params = $this->getOptions();

        // Send scroll_id via raw HTTP body to handle cases of very large (> 4kb) ids.
        if ('_search/scroll' == $path) {
            $data = [self::OPTION_SCROLL_ID => $params[self::OPTION_SCROLL_ID]];
            unset($params[self::OPTION_SCROLL_ID]);
        } else {
            $data = $query->toArray();
        }

        $response = $this->getClient()->request(
            $path,
            Request::POST,
            $data,
            $params
        );

        return $this->builder->buildResultSet($response, $query);
    }
}