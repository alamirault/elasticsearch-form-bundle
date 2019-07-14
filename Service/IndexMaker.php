<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/28/19 12:23 PM
 */

namespace Alamirault\ElasticsearchBundle\Service;


use Alamirault\ElasticsearchBundle\Model\Index;
use Alamirault\ElasticsearchBundle\Model\IndexRegistry;
use Symfony\Component\Yaml\Yaml;

class IndexMaker
{
    const READ = 'READ';
    const WRITE = 'WRITE';

    public function getIndex(IndexRegistry $indexRegistry, string $indexName, string $mode = self::READ): Index
    {
        $index = new Index($indexRegistry->getClient(), $this->getIndexName($indexName, $mode));

        if (!$index->exists()) {
            $indexDefinition = $indexRegistry->retrieveIndex($indexName);

            $mapping = Yaml::parse(file_get_contents($indexDefinition->getPathToMapping()));
            $index->create($mapping);
        }
        return $index;
    }

    public function getIndexName(string $indexName, string $mode): string
    {
        switch ($mode) {
            case self::WRITE:
                return sprintf('%s_%s', $indexName, date('Y-m-d'));
            case self::READ:
                return sprintf('%s_*', $indexName);
            default:
                return $indexName;
        }
    }
}