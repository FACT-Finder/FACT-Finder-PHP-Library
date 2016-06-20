<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class BreadCrumbTypeTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $typeClass;

    public function setUp()
    {
        parent::setUp();

        $this->typeClass = FF::getClassName('Data\BreadCrumbType');
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testTypeSafety()
    {
        $typeClass = $this->typeClass;
        $this->assertInstanceOf($typeClass, $typeClass::Search());
        $this->assertInstanceOf($typeClass, $typeClass::Filter());
        $this->assertInstanceOf($typeClass, $typeClass::Advisor());
    }

    public function testEquality()
    {
        $typeClass = $this->typeClass;
        $this->assertTrue($typeClass::Search() == $typeClass::Search());
        $this->assertTrue($typeClass::Filter() == $typeClass::Filter());
        $this->assertTrue($typeClass::Advisor() == $typeClass::Advisor());
        $this->assertFalse($typeClass::Search() == $typeClass::Filter());
        $this->assertFalse($typeClass::Search() == $typeClass::Advisor());
    }
}
