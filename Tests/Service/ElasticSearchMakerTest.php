<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 11:50 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Service;


use Alamirault\ElasticsearchBundle\Form\Extension\ElasticConditionTypeExtension;
use Alamirault\ElasticsearchBundle\Service\ElasticsearchMaker;
use Alamirault\ElasticsearchBundle\Service\IndexMaker;
use Alamirault\ElasticsearchBundle\Service\ItemsPerPage;
use Alamirault\ElasticsearchBundle\Tests\Service\Fixture\UserFormType;
use Elastica\Query;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ElasticSearchMakerTest extends TestCase
{
    /**
     * @var CsrfTokenManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenStorage;

    /**
     * @var IndexMaker|PHPUnit_Framework_MockObject_MockObject
     */
    private $indexMaker;
    /**
     * @var ItemsPerPage|PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsPerPage;

    protected function setUp()
    {
        parent::setUp();
        $this->tokenStorage = $this->getMockBuilder(CsrfTokenManagerInterface::class)->disableOriginalConstructor()->getMock();
        $this->indexMaker = $this->getMockBuilder(IndexMaker::class)->disableOriginalConstructor()->getMock();
        $this->itemsPerPage = $this->getMockBuilder(ItemsPerPage::class)->disableOriginalConstructor()->getMock();
        $this->itemsPerPage->method("getValue")->willReturn(15);
    }

    public function testPaginateQuery()
    {

        $elasticsearchMaker = new ElasticsearchMaker($this->tokenStorage, $this->indexMaker, $this->itemsPerPage);

        $query = $elasticsearchMaker->paginate(new Query(), 3, 15);
        $this->assertSame(
            [
                "from" => 30,
                "size" => 15,
            ],
            $query->getParams()
        );
    }


    public function testSortEmptyQuery()
    {

        $elasticsearchMaker = new ElasticsearchMaker($this->tokenStorage, $this->indexMaker, $this->itemsPerPage);
        $request = new Request();

        $this->assertSame(
            [
                "sort" => [
                    [
                        "createdAt" => [
                            'order' => 'desc',
                        ],
                    ],
                ],
            ],
            $elasticsearchMaker->sort(new Query(), $request)->getParams()
        );

        $this->assertSame(
            [
                "sort" => [
                    [
                        "other" => [
                            'order' => 'direction',
                        ],
                    ],
                ],
            ],
            $elasticsearchMaker->sort(new Query(), $request, "other", 'direction')->getParams()
        );
    }

    public function testSort()
    {
        $elasticsearchMaker = new ElasticsearchMaker($this->tokenStorage, $this->indexMaker, $this->itemsPerPage);
        $request = new Request([
            "sort" => "key",
            "direction" => "value",
        ]);

        $this->assertSame(
            [
                "sort" => [
                    [
                        "key" => [
                            'order' => 'value',
                        ],
                    ],
                ],
            ],
            $elasticsearchMaker->sort(new Query(), $request)->getParams()
        );
    }

    public function testGetQueryFromFilterConditionsNotSubmitted()
    {
        $elasticsearchMaker = new ElasticsearchMaker($this->tokenStorage, $this->indexMaker, $this->itemsPerPage);
        $notSubmittedForm = Forms::createFormFactory()->createBuilder()->getForm();

        $query = $elasticsearchMaker->getQueryFromFilterConditions(new Request(), $notSubmittedForm);
        $queryArray = $query->toArray();

        $this->assertArrayHasKey("query", $queryArray);
        $this->assertArrayHasKey("match_all", $queryArray["query"]);
    }

    public function testGetQueryFromFilterConditionsNotSubmittedWithDefaultData()
    {
        $elasticsearchMaker = new ElasticsearchMaker($this->tokenStorage, $this->indexMaker, $this->itemsPerPage);
        $notSubmittedForm = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new ElasticConditionTypeExtension())
            ->getFormFactory()
            ->createBuilder(UserFormType::class)
            ->getForm();

        $query = $elasticsearchMaker->getQueryFromFilterConditions(new Request(), $notSubmittedForm);
        $queryArray = $query->toArray();

        $expected = [
            "query" => [
                "bool" => [
                    "must" => [
                        [
                            "match" => [
                                "name" => "Jean",
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $queryArray);
    }

    public function testGetQueryFromFilterConditionsValidForm()
    {
        $elasticsearchMaker = new ElasticsearchMaker($this->tokenStorage, $this->indexMaker, $this->itemsPerPage);
        $form = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new ElasticConditionTypeExtension())
            ->getFormFactory()
            ->createBuilder(UserFormType::class)
            ->getForm();

        $request = new Request([], [
            "user_form" => [
                "name" => "John",
            ],
        ]);

        $form->submit($request->request->get($form->getName()));

        $query = $elasticsearchMaker->getQueryFromFilterConditions($request, $form);
        $queryArray = $query->toArray();

        $expected = [
            "query" => [
                "bool" => [
                    "must" => [
                        [
                            "match" => [
                                "name" => "John",
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $queryArray);
    }

    public function testGetQueryFromFilterConditionsValidFormMultiple()
    {
        $elasticsearchMaker = new ElasticsearchMaker($this->tokenStorage, $this->indexMaker, $this->itemsPerPage);
        $form = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new ElasticConditionTypeExtension())
            ->getFormFactory()
            ->createBuilder(UserFormType::class)
            ->getForm();

        $request = new Request([], [
            "user_form" => [
                "name" => "John",
                "email" => "jdo",
            ],
        ]);

        $form->submit($request->request->get($form->getName()));

        $query = $elasticsearchMaker->getQueryFromFilterConditions($request, $form);
        $queryArray = $query->toArray();

        $expected = [
            "query" => [
                "bool" => [
                    "must" => [
                        [
                            "match" => [
                                "name" => "John",
                            ],
                        ],
                        [
                            "wildcard" => [
                                "email" => [
                                    "value" => "jdo*",
                                    "boost" => 1.0,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $queryArray);
    }
}