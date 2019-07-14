<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/27/19 3:41 PM
 */

namespace Alamirault\ElasticsearchBundle\Model;

use Elastica\Result as ElasticaResult;

class Result extends ElasticaResult
{
    protected $model;

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model): void
    {
        $this->model = $model;
    }
}