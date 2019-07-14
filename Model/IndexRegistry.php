<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/28/19 11:38 AM
 */

namespace Alamirault\ElasticsearchBundle\Model;


use Alamirault\ElasticsearchBundle\Exception\IndexAlreadyRegisteredException;
use Alamirault\ElasticsearchBundle\Exception\UnregisteredIndexException;
use Elastica\Client;

class IndexRegistry
{
    /**
     * @var IndexDefinition[]
     */
    private $indexes = [];
    /**
     * @var Client
     */
    private $client;

    /**
     * IndexRegistry constructor.
     * @param Client $client
     * @param array $indexes
     * @throws IndexAlreadyRegisteredException
     */
    public function __construct(Client $client, array $indexes)
    {
        $this->client = $client;
        foreach ($indexes as $index) {
            $indexName = $index["name"];
            if (!$this->indexIsAlreadyRegistered($indexName)) {
                $this->register(new IndexDefinition($indexName, $index["mapping"]));
            } else {
                throw new IndexAlreadyRegisteredException($indexName);
            }
        }
    }


    public function register(IndexDefinition $indexDefinition)
    {
        if (!$this->indexIsAlreadyRegistered($indexDefinition->getName())) {
            $this->indexes[] = $indexDefinition;
        } else {
            throw new IndexAlreadyRegisteredException($indexDefinition->getName());
        }
    }

    public function retrieveIndex(string $indexName)
    {
        foreach ($this->indexes as $indexDefinition) {
            if ($indexDefinition->getName() === $indexName) {
                return $indexDefinition;
            }
        }

        throw new UnregisteredIndexException($indexName);
    }

    private function indexIsAlreadyRegistered(string $indexName): bool
    {
        foreach ($this->indexes as $indexDefinition) {
            if ($indexDefinition->getName() === $indexName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}