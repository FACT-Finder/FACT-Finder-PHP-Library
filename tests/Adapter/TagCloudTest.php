<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class TagCloudTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\TagCloud
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $this->dataProvider = FF::getInstance(
            'Core\Server\FileSystemDataProvider',
            self::$dic['loggerClass'],
            self::$dic['configuration']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\TagCloud',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['parametersConverter']
        );
    }

    public function testGetTagCloud()
    {
        $tagCloud = $this->adapter->getTagCloud();

        $this->assertEquals(5, count($tagCloud), 'wrong number of tag queries delivered');
        $this->assertInstanceOf('FACTFinder\Data\TagQuery', $tagCloud[0], 'tag cloud element is no tag query');
        $this->assertEquals(0.561, $tagCloud[0]->getWeight(), 'wrong weight delivered for first tag query', 0.0001);
        $this->assertEquals(1266, $tagCloud[0]->getSearchCount(), 'wrong search count delivered for first tag query');
        $this->assertEquals("28+zoll+damen", $tagCloud[0]->getLabel(), 'wrong query delivered for first tag query');
        $this->assertFalse($tagCloud[0]->isSelected(), 'first tag query should not be selected');
    }

    public function testWordCount()
    {
        $this->adapter->setWordCount(3);
        $tagCloud = $this->adapter->getTagCloud();

        $this->assertEquals(3, count($tagCloud), 'wrong number of tag queries delivered');
        $this->assertInstanceOf('FACTFinder\Data\TagQuery', $tagCloud[0], 'tag cloud element is no tag query');
        $this->assertEquals(0.561, $tagCloud[0]->getWeight(), 'wrong weight delivered for first tag query', 0.0001);
        $this->assertEquals(1266, $tagCloud[0]->getSearchCount(), 'wrong search count delivered for first tag query');
        $this->assertEquals("28+zoll+damen", $tagCloud[0]->getLabel(), 'wrong query delivered for first tag query');
        $this->assertFalse($tagCloud[0]->isSelected(), 'first tag query should not be selected');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWordCount()
    {
        $this->adapter->setWordCount(-1);
    }
}
