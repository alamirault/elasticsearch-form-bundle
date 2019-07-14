<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 11:26 AM
 */

namespace Alamirault\ElasticsearchBundle\Model;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

interface ElasticsearchDenormalizerInterface extends DenormalizerInterface
{
    public function getClass(): string;
}