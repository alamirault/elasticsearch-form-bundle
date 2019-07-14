<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 10:36 AM
 */

namespace Alamirault\ElasticsearchBundle\Exception;


class UnregisteredIndexException extends \Exception
{
    public function __construct(string $indexName)
    {
        parent::__construct(sprintf("Index '%s' is not registered in this registry.", $indexName));
    }

}