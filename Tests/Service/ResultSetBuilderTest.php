<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 11:28 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Service;

use Alamirault\ElasticsearchBundle\Model\Result;
use Alamirault\ElasticsearchBundle\Service\ResultSetBuilder;
use Alamirault\ElasticsearchBundle\Tests\Service\Fixture\User;
use Alamirault\ElasticsearchBundle\Tests\Service\Fixture\UserDenormalizer;
use Elastica\Query;
use Elastica\Response;
use PHPUnit\Framework\TestCase;

class ResultSetBuilderTest extends TestCase
{
    private $resultSetBuilder;

    public function testBuildResultsetEmptyResponse()
    {
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $response->method("getData")->willReturn([]);

        $resultStet = $this->resultSetBuilder->buildResultSet($response, new Query());
        $this->assertCount(0, $resultStet->getResults());
    }

    public function testBuildResultset()
    {
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $response->method("getData")->willReturn([
            "hits" => [
                "hits" => [
                    [
                        "_source" => [
                            "name" => "John",
                        ],
                    ],
                ],
            ],
        ]);

        $resultStet = $this->resultSetBuilder->buildResultSet($response, new Query());
        $this->assertCount(1, $resultStet->getResults());
        $oneResult = $resultStet->getResults()[0];

        $this->assertInstanceOf(Result::class, $oneResult);
        $user = $oneResult->getModel();
        $this->assertInstanceOf(User::class, $user);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $denormalizer = new UserDenormalizer();

        $this->resultSetBuilder = new ResultSetBuilder($denormalizer);
    }
}