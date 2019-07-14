<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/8/19 11:28 AM
 */

namespace Alamirault\ElasticsearchBundle\Model;


use Alamirault\ElasticsearchBundle\Service\ElasticsearchClient;
use Elastica\ResultSet\BuilderInterface;

class Index extends \Elastica\Index
{
    public function createSearch($query = '', $options = null, BuilderInterface $builder = null)
    {
        $search = new Search($this->getClient(), $builder);
        $search->addIndex($this);
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    public function getClient(): ElasticsearchClient
    {
        return $this->_client;
    }
}