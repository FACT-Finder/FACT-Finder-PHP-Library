<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class TrackingEventTypeTest extends \FACTFinder\Test\BaseTestCase
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

        $this->typeClass = FF::getClassName('Data\TrackingEventType');
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testTypeSafety()
    {
        $typeClass = $this->typeClass;
        $this->assertInstanceOf($typeClass, $typeClass::Display());
        $this->assertInstanceOf($typeClass, $typeClass::Feedback());
        $this->assertInstanceOf($typeClass, $typeClass::Inspect());
        $this->assertInstanceOf($typeClass, $typeClass::AvailabilityCheck());
        $this->assertInstanceOf($typeClass, $typeClass::Cart());
        $this->assertInstanceOf($typeClass, $typeClass::Buy());
        $this->assertInstanceOf($typeClass, $typeClass::CacheHit());
        $this->assertInstanceOf($typeClass, $typeClass::SessionStart());
    }

    public function testEquality()
    {
        $typeClass = $this->typeClass;
        $this->assertTrue($typeClass::Display() == $typeClass::Display());
        $this->assertTrue($typeClass::Cart() == $typeClass::Cart());
        $this->assertFalse($typeClass::Display() == $typeClass::Cart());
        $this->assertFalse($typeClass::Buy() == $typeClass::Feedback());
    }
}
