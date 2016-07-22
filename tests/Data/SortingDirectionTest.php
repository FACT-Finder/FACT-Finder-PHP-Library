<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class SortingDirectionTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $directionClass;

    public function setUp()
    {
        parent::setUp();

        $this->directionClass = FF::getClassName('Data\SortingDirection');
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testTypeSafety()
    {
        $directionClass = $this->directionClass;
        $this->assertInstanceOf($directionClass, $directionClass::Ascending());
        $this->assertInstanceOf($directionClass, $directionClass::Descending());
    }

    public function testEquality()
    {
        $directionClass = $this->directionClass;
        $this->assertTrue($directionClass::Ascending() == $directionClass::Ascending());
        $this->assertTrue($directionClass::Descending() == $directionClass::Descending());
        $this->assertFalse($directionClass::Ascending() == $directionClass::Descending());
    }
}
