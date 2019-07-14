<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 11:37 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Service\Fixture;


use Alamirault\ElasticsearchBundle\Model\ElasticsearchDenormalizerInterface;

class UserDenormalizer implements ElasticsearchDenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $user = $data["_source"];

        return new User($user["name"]);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === User::class;
    }

    public function getClass(): string
    {
        return User::class;
    }
}