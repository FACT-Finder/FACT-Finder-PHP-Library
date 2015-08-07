<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class ArticleNumberSearchStatusTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $statusClass;

    public function setUp()
    {
        parent::setUp();

        $this->statusClass = FF::getClassName('Data\ArticleNumberSearchStatus');
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testTypeSafety()
    {
        $statusClass = $this->statusClass;
        $this->assertInstanceOf($statusClass, $statusClass::IsArticleNumberResultFound());
        $this->assertInstanceOf($statusClass, $statusClass::IsNoArticleNumberResultFound());
        $this->assertInstanceOf($statusClass, $statusClass::IsNoArticleNumberSearch());
    }

    public function testEquality()
    {
        $statusClass = $this->statusClass;
        $this->assertTrue($statusClass::IsArticleNumberResultFound() == $statusClass::IsArticleNumberResultFound());
        $this->assertTrue($statusClass::IsNoArticleNumberResultFound() == $statusClass::IsNoArticleNumberResultFound());
        $this->assertTrue($statusClass::IsNoArticleNumberSearch() == $statusClass::IsNoArticleNumberSearch());
        $this->assertFalse($statusClass::IsArticleNumberResultFound() == $statusClass::IsNoArticleNumberResultFound());
        $this->assertFalse($statusClass::IsNoArticleNumberResultFound() == $statusClass::IsNoArticleNumberSearch());
        $this->assertFalse($statusClass::IsArticleNumberResultFound() == $statusClass::IsNoArticleNumberSearch());
    }
}
