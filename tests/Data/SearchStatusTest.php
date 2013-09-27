<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class SearchStatusTest extends \FACTFinder\Test\BaseTestCase
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

        $this->statusClass = FF::getClassName('Data\SearchStatus');
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testTypeSafety()
    {
        $statusClass = $this->statusClass;
        $this->assertInstanceOf($statusClass, $statusClass::NoQuery());
        $this->assertInstanceOf($statusClass, $statusClass::NoResult());
        $this->assertInstanceOf($statusClass, $statusClass::EmptyResult());
        $this->assertInstanceOf($statusClass, $statusClass::RecordsFound());
    }

    public function testEquality()
    {
        $statusClass = $this->statusClass;
        $this->assertTrue($statusClass::RecordsFound() == $statusClass::RecordsFound());
        $this->assertTrue($statusClass::EmptyResult() == $statusClass::EmptyResult());
        $this->assertFalse($statusClass::NoQuery() == $statusClass::NoResult());
        $this->assertFalse($statusClass::NoQuery() == $statusClass::EmptyResult());
    }
}
