<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/28/19 11:38 AM
 */

namespace Alamirault\ElasticsearchBundle\Model;


class IndexDefinition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $pathToMapping;

    /**
     * IndexDefinition constructor.
     * @param string $name
     * @param string $pathToMapping
     */
    public function __construct(string $name, string $pathToMapping)
    {
        $this->name = $name;
        $this->pathToMapping = $pathToMapping;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPathToMapping(): string
    {
        return $this->pathToMapping;
    }
}