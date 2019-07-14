<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 10:12 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Model;

use Alamirault\ElasticsearchBundle\Exception\IndexAlreadyRegisteredException;
use Alamirault\ElasticsearchBundle\Exception\UnregisteredIndexException;
use Alamirault\ElasticsearchBundle\Model\IndexDefinition;
use Alamirault\ElasticsearchBundle\Model\IndexRegistry;
use Elastica\Client;
use PHPUnit\Framework\TestCase;

class IndexRegistryTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function testConstructor()
    {
        $indexes = [
            ["name" => "name_example", "mapping" => "path/to/mapping.yml"],
            ["name" => "other_example", "mapping" => "other/path/to/mapping.yml"],
        ];
        $indexRegistry = new IndexRegistry($this->client, $indexes);

        $index = $indexRegistry->retrieveIndex("name_example");

        $this->assertInstanceOf(IndexDefinition::class, $index);
        $this->assertEquals("name_example", $index->getName());
        $this->assertEquals("path/to/mapping.yml", $index->getPathToMapping());
    }

    public function testConstructorWithSameKey()
    {
        $this->expectException(IndexAlreadyRegisteredException::class);
        $this->expectExceptionMessage("Index 'name_example' is already registred in this registry.");

        $indexes = [
            ["name" => "name_example", "mapping" => "path/to/mapping.yml"],
            ["name" => "name_example", "mapping" => "path/to/mapping.yml"],
        ];
        $indexRegistry = new IndexRegistry($this->client, $indexes);
    }

    public function testRegisterIndex()
    {
        $indexRegistry = new IndexRegistry($this->client, []);
        $indexDefinition = new IndexDefinition("index_name", "/path/to/mapping.yml");
        $indexRegistry->register($indexDefinition);
        $retrieve = $indexRegistry->retrieveIndex("index_name");

        $this->assertInstanceOf(IndexDefinition::class, $retrieve);
    }

    public function testRegisterIndexTwice()
    {
        $this->expectException(IndexAlreadyRegisteredException::class);
        $this->expectExceptionMessage("Index 'index_name' is already registred in this registry.");

        $indexRegistry = new IndexRegistry($this->client, []);
        $indexDefinition = new IndexDefinition("index_name", "/path/to/mapping.yml");
        $indexDefinitionSecond = new IndexDefinition("index_name", "/path/to/mapping.yml");

        $indexRegistry->register($indexDefinition);
        $indexRegistry->register($indexDefinitionSecond);
    }

    public function testRetrieveUnregisteredIndex()
    {
        $this->expectException(UnregisteredIndexException::class);
        $this->expectExceptionMessage("Index 'index_name' is not registered in this registry.");

        $indexRegistry = new IndexRegistry($this->client, []);
        $indexRegistry->retrieveIndex("index_name");
    }

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
    }
}