<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 10:21 AM
 */

namespace Alamirault\ElasticsearchBundle\Exception;

class IndexAlreadyRegisteredException extends \Exception
{
    public function __construct(string $indexName)
    {
        parent::__construct(sprintf("Index '%s' is already registred in this registry.", $indexName));
    }

}