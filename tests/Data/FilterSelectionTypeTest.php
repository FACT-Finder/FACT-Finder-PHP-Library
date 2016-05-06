<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class FilterSelectionType extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $selectionTypeClass;

    public function setUp()
    {
        parent::setUp();

        $this->selectionTypeClass = FF::getClassName('Data\FilterSelectionType');
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testTypeSafety()
    {
        $selectionTypeClass = $this->selectionTypeClass;
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::SingleHideUnselected());
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::SingleShowUnselected());
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::MultiSelectOr());
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::MultiSelectAnd());
    }

    public function testEquality()
    {
        $selectionTypeClass = $this->selectionTypeClass;
        $this->assertTrue($selectionTypeClass::SingleHideUnselected() == $selectionTypeClass::SingleHideUnselected());
        $this->assertTrue($selectionTypeClass::SingleShowUnselected() == $selectionTypeClass::SingleShowUnselected());
        $this->assertTrue($selectionTypeClass::MultiSelectOr() == $selectionTypeClass::MultiSelectOr());
        $this->assertTrue($selectionTypeClass::MultiSelectAnd() == $selectionTypeClass::MultiSelectAnd());
    }
}
