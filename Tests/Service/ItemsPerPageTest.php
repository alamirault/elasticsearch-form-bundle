<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 11:03 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Service;

use Alamirault\ElasticsearchBundle\Service\ItemsPerPage;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ItemsPerPageTest extends TestCase
{
    /**
     * @var RequestStack|PHPUnit_Framework_MockObject_MockObject
     */
    private $requestStack;

    public function testGetValueWithoutAll()
    {
        $request = new Request();

        $this->requestStack->method("getMasterRequest")->willReturn($request);

        $itemsPerPage = new ItemsPerPage($this->requestStack);
        $this->assertEquals(20, $itemsPerPage->getValue());
    }

    public function testGetValueWithCookieOnly()
    {
        $request = new Request([], [], [], [
            'itemsPerPage' => 137,
        ]);

        $this->requestStack->method("getMasterRequest")->willReturn($request);

        $itemsPerPage = new ItemsPerPage($this->requestStack);
        $this->assertEquals(137, $itemsPerPage->getValue());
    }

    public function testGetValueWithQueryOnly()
    {
        $request = new Request([
            "nbItemPerPage" => 151,
        ]);

        $this->requestStack->method("getMasterRequest")->willReturn($request);

        $itemsPerPage = new ItemsPerPage($this->requestStack);
        $this->assertEquals(151, $itemsPerPage->getValue());
    }

    public function testGetValueWithAll()
    {
        $request = new Request([
            "nbItemPerPage" => 151,
        ], [], [], [
            'itemsPerPage' => 137,
        ]);

        $this->requestStack->method("getMasterRequest")->willReturn($request);

        $itemsPerPage = new ItemsPerPage($this->requestStack);
        $this->assertEquals(151, $itemsPerPage->getValue());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestStack = $this->getMockBuilder(RequestStack::class)->disableOriginalConstructor()->getMock();
    }
}