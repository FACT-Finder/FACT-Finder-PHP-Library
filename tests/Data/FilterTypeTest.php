<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class FilterTypeTest extends \FACTFinder\Test\BaseTestCase
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

        $this->typeClass = FF::getClassName('Data\FilterType');
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testTypeSafety()
    {
        $typeClass = $this->typeClass;
        $this->assertInstanceOf($typeClass, $typeClass::Text());
        $this->assertInstanceOf($typeClass, $typeClass::Number());
    }

    public function testEquality()
    {
        $typeClass = $this->typeClass;
        $this->assertTrue($typeClass::Text() == $typeClass::Text());
        $this->assertTrue($typeClass::Number() == $typeClass::Number());
    }
}
