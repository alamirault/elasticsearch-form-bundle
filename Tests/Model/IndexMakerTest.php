<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 10:39 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Model;

use Alamirault\ElasticsearchBundle\Service\IndexMaker;
use PHPUnit\Framework\TestCase;

class IndexMakerTest extends TestCase
{
    public function testGetIndexName()
    {
        $indexMaker = new IndexMaker();

        $this->assertEquals("name_*", $indexMaker->getIndexName("name", IndexMaker::READ));
        $this->assertEquals("name_" . date('Y-m-d'), $indexMaker->getIndexName("name", IndexMaker::WRITE));
        $this->assertEquals("name", $indexMaker->getIndexName("name", "BADMODE"));
    }
}